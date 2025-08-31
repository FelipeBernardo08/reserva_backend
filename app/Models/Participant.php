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

    public function reservationParticipant()
    {
        return $this->hasMany(ReservationParticipant::class, 'participant_id');
    }

    public function createParticipant(string $name): array
    {
        return self::create([
            'name' => $name
        ])->toArray();
    }

    public function readAllParticipants(): array
    {
        return self::with([
            'reservationParticipant' => function ($query) {
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

    public function readParticipantDetails(int $id): array
    {
        return self::where('id', $id)
            ->with([
                'reservationParticipant' => function ($query) {
                    $query->where('status', true)->select(['id', 'room_id', 'date_init', 'date_end']);
                },
                'reservationParticipant.reservation' => function ($query) {
                    $query->select(['id', 'room_id', 'date_init', 'date_end']);
                },
                'reservationParticipant.reservation.room' => function ($query) {
                    $query->select(['id', 'title', 'description']);
                }
            ])
            ->get(['id', 'name'])
            ->toArray();
    }
}
