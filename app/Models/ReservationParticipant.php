<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'participant_id'
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    public function createReservationParticipants(int $reservationId, array $participantIds): bool
    {
        $data = [];

        foreach ($participantIds as $participantId) {
            $data[] = [
                'participant_id' => $participantId,
                'reservation_id' => $reservationId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        return self::insert($data);
    }

    public function deleteReservationParticipants(int $reservationId, array $participantIds): bool
    {
        return self::where('reservation_id', $reservationId)
            ->whereIn('participant_id', $participantIds)
            ->delete();
    }
}
