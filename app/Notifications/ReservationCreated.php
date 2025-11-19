<?php

namespace App\Notifications;

use FlipperBox\Scheduling\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ReservationCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load('user', 'vehicle');
    }

    /**
     * Aquí definimos los canales: 'database' guarda en la tabla, 'broadcast' envía a Reverb.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Lo que se guarda en la tabla de base de datos (JSON).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Nueva reserva de {$this->reservation->user->name} para el vehículo {$this->reservation->vehicle->patente}.",
            'url' => route('admin.scheduling.reservations.index', ['date' => $this->reservation->reservation_date->format('Y-m-d')]),
            'reservation_id' => $this->reservation->id,
        ];
    }

    /**
     * Lo que se envía por WebSocket (Reverb).
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        // Reutilizamos el array de base de datos para enviar lo mismo
        return new BroadcastMessage($this->toArray($notifiable));
    }
}