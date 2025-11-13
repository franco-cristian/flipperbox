<?php

namespace App\Events;

use FlipperBox\Scheduling\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewReservationMade implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * La reserva que fue creada.
     */
    public readonly Reservation $reservation;

    /**
     * Create a new event instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load('user', 'vehicle');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Canal privado. Solo los usuarios autorizados podrán escucharlo.
        return [
            new PrivateChannel('admin-notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        // Evento que escucharemos en el frontend.
        return 'reservation.made';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // "Payload" de datos que recibirá el frontend.
        return [
            'message' => "Nueva reserva de {$this->reservation->user->name} para el vehículo {$this->reservation->vehicle->patente}.",
            'url' => route('admin.scheduling.reservations.index', ['date' => $this->reservation->reservation_date->format('Y-m-d')]),
            'date' => now()->toFormattedDateString(),
        ];
    }
}