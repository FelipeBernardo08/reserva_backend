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

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'room_id');
    }

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

    public function readAllRooms(): array
    {
        return self::with([
            'reservations' => function ($query) {
                $query->select(['id', 'room_id']);
            }
        ])
            ->get()
            ->toArray();
    }

    public function readRoomDetails(int $id): array
    {
        return self::where('id', $id)
            ->with([
                'reservations' => function ($query) {
                    $query->select(['id', 'room_id', 'date_init', 'date_end', 'status']);
                },
                'reservations.reservationParticipants' => function ($query) {
                    $query->select(['id', 'reservation_id', 'participant_id']);
                },
                'reservations.reservationParticipants.participant' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])
            ->get()
            ->toArray();
    }
}
