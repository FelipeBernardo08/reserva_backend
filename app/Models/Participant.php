<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function createParticipant(string $name): array
    {
        return self::create([
            'name' => $name
        ])->toArray();
    }

    public function getAllParticipants(): array
    {
        return self::get()->toArray();
    }

    public function updateParticipant(int $id, string $name): bool
    {
        return self::where('id', $id)
            ->update([
                'name' => $name
            ]);
    }
}
