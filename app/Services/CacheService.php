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

    public function delete(string $cacheName): void
    {
        Cache::forget($cacheName);
    }
}
