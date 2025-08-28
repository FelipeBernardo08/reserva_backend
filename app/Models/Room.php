<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description'
    ];

    public function createRoom(string $title, string $description): array
    {
        return self::create([
            'title' => $title,
            'description' => $description
        ])->toArray();
    }

    public function getRoomByTitle(string $title): array
    {
        return self::where('title', $title)
            ->get()
            ->toArray();
    }

    public function getAllRooms(): array
    {
        return self::get()->toArray();
    }
}
