<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{

    public function read(string $cacheName): array
    {
        return Cache::get($cacheName) ?? [];
    }

    public function increment(string $cacheName, array $data): void
    {
        $response = Cache::get($cacheName) ?? [];
        $response[] = $data;
        Cache::set($cacheName, $response);
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
}
