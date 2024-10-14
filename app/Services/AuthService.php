<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\AuthRepositoryInterface;
use App\DTOs\AuthDTO;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Psr\Log\LoggerInterface;

class AuthService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_USER = 'user_';
    private const CACHE_KEY_PROVIDER = 'user_email_provider_';
    private const CACHE_KEY_USER_SOCIALITE_RESOURCE = 'user_social_provider_';

    public function __construct(
        private readonly AuthRepositoryInterface $repository,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService,
        private readonly LoggerInterface $logger
    ) {}

    public function authenticateUser(AuthDTO $dto): array
    {
        $loginField = filter_var($dto->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = $this->repository->findByField($loginField, $dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw new Exception('Invalid credentials');
        }

        $token = $this->createTokenForUser($user);
        $this->cacheUser($user);

        $this->logger->info('User authenticated successfully', ['id' => $user->id]);
        return ['user' => $user, 'token' => $token];
    }

    public function logoutUser(User $user): void
    {
        $user->tokens()->delete();
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_USER, $user->uuid);
        $this->userCacheService->forgetSocialProviderCache(self::CACHE_KEY_PROVIDER, $user->email, $user->id);
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_USER_SOCIALITE_RESOURCE, $user->uuid);
        $this->logger->info('User logged out successfully', ['id' => $user->id]);
    }

    public function getAuthenticatedUser(int $userId): User
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_USER,
            $userId,
            fn() => $this->repository->findById($userId)
        );
    }

    public function updateUserPassword(AuthDTO $dto, User $user): User
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $user) {
            if (!Hash::check($dto->currentPassword, $user->password)) {
                throw new Exception('Current password does not match');
            }

            $user->password = Hash::make($dto->newPassword);
            $this->repository->update($user);

            $this->cacheUser($user);
            $this->logger->info('User password updated successfully', ['id' => $user->id]);
            return $user;
        }, 'updating user password');
    }

    public function checkEmailAvailability(string $email, ?User $currentUser): array
    {
        if ($currentUser && $currentUser->email === $email) {
            return ['available' => true, 'message' => ''];
        }

        $exists = $this->repository->emailExists($email);
        return [
            'available' => !$exists,
            'message' => $exists ? 'Email is already taken' : 'Email is available'
        ];
    }

    public function checkUsernameAvailability(string $username, ?User $currentUser): array
    {
        if ($currentUser && $currentUser->username === $username) {
            return ['available' => true, 'message' => ''];
        }

        $exists = $this->repository->usernameExists($username);
        return [
            'available' => !$exists,
            'message' => $exists ? 'Username is already taken' : 'Username is available'
        ];
    }

    private function createTokenForUser(User $user): string
    {
        // Crear el token completo
        $token = $user->createToken('auth_token')->plainTextToken;

        // Dividir el token en partes y retornar solo la segunda parte
        $tokenParts = explode('|', $token);
        return $tokenParts[1] ?? $token; // Retornar la segunda parte si existe, o el token completo si no
    }


    private function cacheUser(User $user): void
    {
        $this->userCacheService->getCachedItem(
            self::CACHE_KEY_USER,
            (string) $user->uuid,
            fn() => $user
        );
    }
}