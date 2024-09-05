<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function getCachedData(string $key, int $minutes, callable $callback)
    {
        return Cache::remember($key, $minutes, $callback);
    }

    public function updateCache(string $cacheKey, int $cacheTime, callable $dataCallback): void
    {
        // Obtain data using the callback and store it in the cache
        $data = $dataCallback();
        $this->putCachedData($cacheKey, $data, $cacheTime);
    }

    protected function putCachedData(string $key, $data, int $minutes): void
    {
        Cache::put($key, $data, now()->addMinutes($minutes));
    }

    public function refreshCache(string $cacheKey, int $cacheTime, callable $dataCallback): void
    {
        // Invalidate the existing cache
        $this->invalidateCache($cacheKey);

        // Update the cache with new data
        $this->updateCache($cacheKey, $cacheTime, $dataCallback);
    }

    public function invalidateCache(string $key): void
    {
        Cache::forget($key);
    }

    public function updateDataCache(string $cacheKey, int $cacheTime, callable $callback): void
    {
        $userId = Auth::id();
        $cacheKey = $cacheKey . '_' . $userId;

        if (!empty($cacheKey)) {
            $this->refreshCache($cacheKey, $cacheTime, $callback);
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }
}