<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\UsersRepositoryInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Interfaces\CacheServiceInterface;
use App\DTOs\UserDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMailWithCredentials;

class UserService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'users_list_';

    public function __construct(
        private readonly UsersRepositoryInterface $repository,
        private readonly TransactionService $transactionService,
        private readonly CacheService $cacheService
    ) {}

    public function all(): Collection
    {
        $cacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) Auth::id());
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, fn() => $this->repository->index());
    }

    public function storeUser(UserDTO $userDto): object
    {
        return $this->transactionService->handleTransaction(function () use ($userDto) {
        $userDetails = $this->prepareUserDetails($userDto);
        $plainTextPassword = $userDetails['plain_text_password'];
        unset($userDetails['plain_text_password']); // Remove it before storing

        $user = $this->repository->store($userDetails);
        if ($userDto->userRole !== null) {
            $this->syncRoles($user, $userDto->userRole);
        }
        $this->sendWelcomeEmail($user, $plainTextPassword);
        $this->updateDataCache();
        return $user;
        }, 'storing user');
    }

    public function updateUser(UserDTO $userDto, UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($userDto, $uuid) {
            $existingUser = $this->getExistingUser($uuid);
            $this->validateUserPermission($existingUser);

            $updateDetails = $this->prepareUpdateDetails($userDto);
            $user = $this->repository->update($updateDetails, $uuid->toString());
            if ($userDto->userRole !== null) {
                $this->syncRoles($user, $userDto->userRole);
            }

            $this->updateCache($uuid, $user);
            $this->updateDataCache();
            return $user;
        }, 'updating user');
    }


     private function prepareUpdateDetails(UserDTO $dto): array
    {
        $updateDetails = [
            'user_id_ref_by' => Auth::id(),
        ];

        $fields = [
            'name' => 'name',
            'last_name' => 'lastName',
            'username' => 'username',
            'email' => 'email',
            'phone' => 'phone',
            'address' => 'address',
            'zip_code' => 'zipCode',
            'city' => 'city',
            'state' => 'state',
            'country' => 'country',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'gender' => 'gender',
            'provider' => 'provider',
            'provider_id' => 'providerId',
            'provider_avatar' => 'providerAvatar',
            'register_date' => 'registerDate'
        ];

        foreach ($fields as $dbField => $dtoField) {
            if (property_exists($dto, $dtoField) && $dto->$dtoField !== null) {
                $updateDetails[$dbField] = $dto->$dtoField;
            }
        }

        // Handle password separately
        if (property_exists($dto, 'password') && $dto->password !== null) {
            $updateDetails['password'] = Hash::make($dto->password);
        }

        return $updateDetails;
    }

    public function showUser(UuidInterface $uuid): ?object
    {
        $cacheKey = $this->generateCacheKey('user_', $uuid->toString());
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, fn() => $this->repository->getByUuid($uuid->toString()));
    }

    public function deleteUser(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingUser = $this->getExistingUser($uuid);
            $this->validateUserPermission($existingUser);
            
            $this->repository->delete($uuid->toString());
            $this->invalidateUserCache($uuid);
            $this->updateDataCache();
            return true;
        }, 'deleting user');
    }

    public function restoreUser(UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $user = $this->repository->restore($uuid->toString());
            $this->updateCache($uuid, $user);
            $this->updateDataCache();
            return $user;
        }, 'restoring user');
    }

    public function getUsersByRole(string $role): Collection
    {
        $cacheKey = $this->generateCacheKey('users_role_', $role);
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, fn() => $this->repository->getByRole($role));
    }

        private function generateRobustPassword(int $length = 10): string
    {
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $specialChars = '!@#$%^&*()_-+=<>?';

    $allChars = $uppercase . $lowercase . $numbers . $specialChars;
    $password = '';

    // Ensure at least one character from each set
    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];

    // Fill the rest of the password
    for ($i = strlen($password); $i < $length; $i++) {
        $password .= $allChars[random_int(0, strlen($allChars) - 1)];
    }

    // Shuffle the password to mix up the guaranteed characters
    return str_shuffle($password);
    }

    private function prepareUserDetails(UserDTO $dto): array
    {
        $plainTextPassword = $dto->password ?? $this->generateRobustPassword();
        return [
        ...$dto->toArray(),
        'uuid' => Uuid::uuid4()->toString(),
        'password' => Hash::make($plainTextPassword),
        'email_verified_at' => now(),
        'plain_text_password' => $plainTextPassword, // This will be used for emailing, then discarded
        ];
    }

    

    private function getExistingUser(UuidInterface $uuid): object
    {
        $user = $this->repository->getByUuid($uuid->toString());
        if (!$user) {
            throw new Exception("User not found");
        }
        return $user;
    }

    private function validateUserPermission(): void
    {
        $userId = Auth::id();
        $isSuperAdmin = $this->repository->isSuperAdmin($userId);

        if (!$isSuperAdmin) {
            throw new Exception("Unauthorized access");
        }
    }

    private function syncRoles(object $user, $roleId): void
    {
        if (is_array($roleId)) {
            $user->roles()->sync($roleId);
        } else {
            $user->roles()->sync([$roleId]);
        }
    }

    private function sendWelcomeEmail(object $user, string $password): void
    {
        $passwordMessage = $password === $user->password 
        ? 'the password you registered'
        : $password; // This will be the plain text password

        Mail::to($user->email)->send(new WelcomeMailWithCredentials($user, $passwordMessage));
    }

    private function updateCache(UuidInterface $uuid, object $user): void
    {
        $cacheKey = $this->generateCacheKey('user_', $uuid->toString());
        $this->cacheService->refreshCache($cacheKey, self::CACHE_TIME, fn() => $user);
    }

    private function updateDataCache(): void
    {
        $cacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) Auth::id());
        $this->cacheService->updateDataCache($cacheKey, self::CACHE_TIME, fn() => $this->repository->index());
    }

    private function invalidateUserCache(UuidInterface $uuid): void
    {
        $this->cacheService->invalidateCache($this->generateCacheKey('user_', $uuid->toString()));
    }

    private function generateCacheKey(string $prefix, string $suffix): string
    {
        return $prefix . $suffix;
    }
}