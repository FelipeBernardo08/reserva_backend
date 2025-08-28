<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{

    public function read(string $cacheName): array
    {
        return Cache::get($cacheName) ?? [];
    }

    public function create(string $cacheName, array $data, int $time): void
    {
        Cache::set($cacheName, $data, $time);
    }

    public function update(string $cacheName, string $index, array $data, int $time): void
    {
        $response = Cache::get($cacheName) ?? [];
        $response[$index] = $data;
        Cache::set($cacheName, $response, $time);
    }

    public function remove(string $cacheName, string $index): void
    {
        $response = Cache::get($cacheName) ?? [];
        unset($response[$index]);
        Cache::set($cacheName, $response);
    }

    public function removeAll(string $cacheName): void
    {
        Cache::forget($cacheName);
    }

    public function clear(): void
    {
        Cache::clear();
    }
}
