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

    public function reservations()
    {
        return $this->hasMany(ReservationParticipant::class, 'participant_id');
    }

    public function createParticipant(string $name): array
    {
        return self::create([
            'name' => $name
        ])->toArray();
    }

    public function getAllParticipants(): array
    {
        return self::with([
            'reservations' => function ($query) {
                $query->select(['id', 'participant_id']);
            }
        ])
            ->get()
            ->toArray();
    }

    public function updateParticipant(int $id, string $name): bool
    {
        return self::where('id', $id)
            ->update([
                'name' => $name
            ]);
    }

    public function getParticipantDetails(int $id): array
    {
        return self::where('id', $id)
            ->with([
                'reservations' => function ($query) {
                    $query->where('status', true)->select(['id', 'room_id', 'date_init', 'date_end']);
                },
                'reservations.room' => function ($query) {
                    $query->select(['id', 'title', 'description']);
                }
            ])
            ->get(['id', 'name'])
            ->toArray();
    }
}
