<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'date_init',
        'date_end',
        'status'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function reservationParticipants()
    {
        return $this->hasMany(ReservationParticipant::class, 'reservation_id');
    }

    public function createReservation(int $roomId, string $dateInit, string $dateEnd): array
    {
        return self::create([
            'room_id' => $roomId,
            'date_init' => $dateInit,
            'date_end' => $dateEnd,
        ])->toArray();
    }

    public function readReservationById(int $id): array
    {
        return self::where('id', $id)
            ->get()
            ->toArray();
    }

    public function getReservationActiveByRoomId(int $roomId): array
    {
        return self::where('room_id', $roomId)
            ->where('status', true)
            ->get()
            ->toArray();
    }

    public function getReservationsComplete(): array
    {
        return self::with([
            'room' => function ($query) {
                $query->select(['id', 'title', 'description']);
            },
            'reservationParticipants' => function ($query) {
                $query->select(['id', 'reservation_id', 'participant_id']);
            },
            'reservationParticipants.participant' => function ($query) {
                $query->select(['id', 'name']);
            }
        ])
            ->orderBy('status', 'desc')
            ->get(['id', 'room_id', 'date_init', 'date_end', 'status'])
            ->toArray();
    }

    public function getReservationsCompleteById(int $id): array
    {
        return self::where('id', $id)
            ->with([
                'room' => function ($query) {
                    $query->select(['id', 'title', 'description']);
                },
                'reservationParticipants' => function ($query) {
                    $query->select(['id', 'reservation_id', 'participant_id']);
                },
                'reservationParticipants.participant' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])
            ->orderBy('status', 'desc')
            ->get(['id', 'room_id', 'date_init', 'date_end', 'status'])
            ->toArray();
    }

    public function getReservationByRoomIdComplete(int $roomId): array
    {
        return self::where('room_id', $roomId)
            ->with([
                'room' => function ($query) {
                    $query->select(['id', 'title', 'description']);
                },
                'reservationParticipants' => function ($query) {
                    $query->select(['id', 'reservation_id', 'participant_id']);
                },
                'reservationParticipants.participant' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])
            ->get(['id', 'room_id', 'date_init', 'date_end', 'status'])
            ->toArray();
    }

    public function getReservationByParticipantIdComplete(int $participantId): array
    {
        return self::with([
            'room' => function ($query) {
                $query->select(['id', 'title', 'description']);
            },
            'reservationParticipants' => function ($query) {
                $query->select(['id', 'reservation_id', 'participant_id']);
            },
            'reservationParticipants.participant' => function ($query) {
                $query->select(['id', 'name']);
            }
        ])
            ->whereHas('reservationParticipants', function ($query) use ($participantId) {
                $query->where('participant_id', $participantId);
            })
            ->get(['id', 'room_id', 'date_init', 'date_end', 'status'])
            ->toArray();
    }

    public function cancelReservationById(int $id): bool
    {
        return self::where('id', $id)
            ->update([
                'status' => false
            ]);
    }
}
