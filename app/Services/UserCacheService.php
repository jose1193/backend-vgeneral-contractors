<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\CacheService;
use App\Interfaces\UsersRepositoryInterface;
use Illuminate\Support\Collection;

class UserCacheService
{
    private const CACHE_TIME = 720; // minutes

    public function __construct(
        private readonly CacheService $cacheService,
        private readonly UsersRepositoryInterface $userRepository
    ) {}

    public function forgetUserListCache(string $cacheKeyPrefix, int $userId): void
    {
        $userCacheKey = $this->generateCacheKey($cacheKeyPrefix, (string) $userId);
        $this->cacheService->forget($userCacheKey);
    }

    public function forgetSocialProviderCache(string $cacheKeyPrefix, string $email): void
    {
        $userCacheKeyProvider = $this->generateCacheKey($cacheKeyPrefix, (string) $email);
        $this->cacheService->forget($userCacheKeyProvider);
    }

   
    public function forgetDataCacheByUuid(string $cacheKeyPrefix, string $uuid): void
    {
        $cacheKey = $this->generateCacheKey($cacheKeyPrefix, $uuid);
        $this->cacheService->forget($cacheKey);
    }

    public function updateSuperAdminCaches(string $cacheKeyPrefix): void
    {
        $superAdmins = $this->userRepository->getAllSuperAdmins();
        foreach ($superAdmins as $admin) {
            $adminCacheKey = $this->generateCacheKey($cacheKeyPrefix, (string) $admin->id);
            $this->cacheService->forget($adminCacheKey);
        }
    }

    public function updateRolesCaches(string $cacheKeyPrefix): void
    {
        $this->cacheService->forget($cacheKeyPrefix);
    }

    
    public function getCachedDataList(string $cacheKey, callable $dataCallback): Collection
    {
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, $dataCallback);
    }
    
    public function getCachedUserList(string $cacheKeyPrefix, int $userId, callable $dataCallback): Collection
    {
        $cacheKey = $this->generateCacheKey($cacheKeyPrefix, (string) $userId);
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, $dataCallback);
    }

    public function getCachedItem(string $cacheKeyPrefix, string $itemId, callable $dataCallback): ?object
    {
        $cacheKey = $this->generateCacheKey($cacheKeyPrefix, $itemId);
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, $dataCallback);
    }


    private function generateCacheKey(string $prefix, string $suffix): string
    {
        return $prefix . $suffix;
    }
}