<?php

namespace App\Mail;

use FlipperBox\Scheduling\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation
    ) {
        $this->reservation->load('user', 'vehicle');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Reserva - FlipperBox',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.confirmed',
            with: [
                'url' => route('cliente.dashboard'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
