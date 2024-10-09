<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\UsersRepositoryInterface;
use App\DTOs\UserDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

use App\Mail\UserCredentialsNotification;
use App\Jobs\SendWelcomeEmailWithCredentials;
class UserService
{
    private const CACHE_KEY_USER = 'user_';
    private const CACHE_KEY_LIST = 'users_total_list_';
    private const CACHE_KEY_ROLE = 'users_role_';

    private const USER_FIELDS = [
        'uuid', 'name', 'last_name', 'username', 'email', 'password', 'phone',
        'address', 'zip_code', 'city', 'state', 'country', 'latitude', 'longitude',
        'gender', 'user_role', 'provider', 'provider_id', 'provider_avatar', 'register_date'
    ];

    public function __construct(
        private readonly UsersRepositoryInterface $repository,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService,
        private readonly LoggerInterface $logger
    ) {}

    public function all(): Collection
    {
        return $this->userCacheService->getCachedUserList(
            self::CACHE_KEY_LIST,
            Auth::id(),
            fn() => $this->repository->index()
        );
    }

    public function storeData(UserDTO $userDto): object
    {
        return $this->transactionService->handleTransaction(function () use ($userDto) {
            $userDetails = $this->prepareUserDetails($userDto);
            $plainTextPassword = $userDetails['plain_text_password'];
            unset($userDetails['plain_text_password']);

            // Almacenar el usuario
            $user = $this->repository->store($userDetails);

            // Asignar roles si están presentes en el DTO
            if ($userDto->userRole !== null) {
                $this->syncRoles($user, $userDto->userRole);
            }

            // Actualizar caches
            $this->updateCaches();
            $this->logger->info('User stored successfully', ['user_id' => $user->id]);

            // Enviar notificación con la contraseña generada o proporcionada
            $this->sendUserCreatedNotification($user, $plainTextPassword);

            return $user;
        }, 'storing user');
    }



    public function updateData(UserDTO $userDto, UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($userDto, $uuid) {
            $existingUser = $this->repository->getByUuid($uuid->toString());

            if ($userDto->email !== null && $userDto->email !== $existingUser->email) {
                $this->checkEmailUniqueness($userDto->email, $uuid);
            }

            $updateDetails = $this->prepareUpdateDetails($userDto);

            
            if ($userDto->generatePassword === true) {
                $newPassword = $this->generateRobustPassword();
                $updateDetails['password'] = Hash::make($newPassword);
            }

            $user = $this->repository->update($updateDetails, $uuid->toString());

            if ($userDto->userRole !== null) {
                $this->syncRoles($user, $userDto->userRole);
            }

            $this->updateCaches($uuid);

            // Send email with new password if it was generated
            if ($userDto->generatePassword === true) {
                $this->sendPasswordUpdateNotification($user, $newPassword);
            }

            $this->logger->info('User updated successfully', ['user_id' => $user->id]);
            return $user;
        }, 'updating user');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_USER,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingUser = $this->repository->getByUuid($uuid->toString());
            
            $this->repository->delete($uuid->toString());
            $this->updateCaches($uuid);
            
            $this->logger->info('User deleted successfully', ['user_uuid' => $uuid->toString()]);
            return true;
        }, 'deleting user');
    }

    public function restoreData(UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $user = $this->repository->restore($uuid->toString());
            $this->updateCaches($uuid);
            $this->logger->info('User restored successfully', ['user_id' => $user->id]);
            return $user;
        }, 'restoring user');
    }

    private function prepareUserDetails(UserDTO $dto): array
    {
    // Genera la contraseña si no se proporcionó
        $plainTextPassword = $dto->password ?? $this->generateRobustPassword();
    
        return [
        ...$dto->toArray(),
        'uuid' => Uuid::uuid4()->toString(),
        'password' => Hash::make($plainTextPassword), 
        'email_verified_at' => now(),
        'plain_text_password' => $plainTextPassword, 
        ];
    }

    private function checkEmailUniqueness(string $email, UuidInterface $currentUserUuid): void
    {
        $existingUserWithEmail = $this->repository->findByEmail($email);

        if ($existingUserWithEmail && $existingUserWithEmail->uuid !== $currentUserUuid->toString()) {
            throw new Exception('The email address is already in use by another account.');
        }
    }

    private function prepareUpdateDetails(UserDTO $dto): array
    {
        $updateDetails = [];

        foreach (self::USER_FIELDS as $field) {
            $dtoField = lcfirst(str_replace('_', '', ucwords($field, '_')));
            if (property_exists($dto, $dtoField) && $dto->$dtoField !== null) {
                $updateDetails[$field] = $dto->$dtoField instanceof UuidInterface
                    ? $dto->$dtoField->toString()
                    : $dto->$dtoField;
            }
        }

        return $updateDetails;
    }

    public function getUsersByRole(string $role): Collection
    {
        $cacheKey = self::CACHE_KEY_ROLE . strtolower(str_replace(' ', '_', $role));
        return $this->userCacheService->getCachedDataList(
            $cacheKey,
            fn() => $this->repository->getByRole($role)
        );
    }

    
    public function getTechnicalServices(): Collection
    {
        return $this->getUsersByRole('Technical Services');
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
    
    private function syncRoles($user, $roleId)
    {
        // Verificar que roleId es un array o un valor único
        if (is_array($roleId)) {
            // Si es un array, sincronizar los roles
            $user->roles()->sync($roleId);
        } else {
            // Si es un solo ID, sincronizar con un solo rol
            $user->roles()->sync([$roleId]);
        }
    }

    private function updateCaches(?UuidInterface $uuid = null): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, Auth::id());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
        $roles = $this->repository->getAllRoles();
        
        if ($roles) {
        foreach ($roles as $role) {
            $cacheKey = self::CACHE_KEY_ROLE . strtolower(str_replace(' ', '_', $role->name));
            $this->userCacheService->updateRolesCaches($cacheKey);
        }
        } else {
        $this->userCacheService->updateRolesCaches(self::CACHE_KEY_ROLE);
        }

        if ($uuid) {
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_USER, $uuid->toString());
        }
    }

    private function sendUserCreatedNotification(object $user, string $password): void
    {
        $roles = $user->roles()->pluck('name')->toArray();
        Mail::to($user->email)->send(new UserCredentialsNotification($user, $password, $roles, true));
        $this->logger->info('User created notification sent', ['user_id' => $user->id]);
    }

    private function sendPasswordUpdateNotification(object $user, string $newPassword): void
    {
        $roles = $user->roles()->pluck('name')->toArray();
        Mail::to($user->email)->send(new UserCredentialsNotification($user, $newPassword, $roles, false));
        $this->logger->info('Password update notification sent', ['user_id' => $user->id]);
    }
}