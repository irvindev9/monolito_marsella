<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ?Reservation $reservation = null,
        public string $type = '',
        public ?int $seasonRequestCount = null,
    ) {
        $this->reservation?->loadMissing(['house.street', 'user']);
    }

    public function envelope(): Envelope
    {
        $date = $this->reservation ? date('d/m/Y', strtotime($this->reservation->reservation_date)) : '';

        $subject = match ($this->type) {
            'created' => "Nueva solicitud de reserva - {$date}",
            'approved' => "Tu reserva del {$date} fue aprobada",
            'rejected' => "Tu reserva del {$date} fue rechazada",
            'waitlist_released' => "¡Fecha disponible para reservar! - {$date}",
            'payment_reminder' => 'Recordatorio amable: pago pendiente de tu reservación',
            default => "Actualización de reserva - {$date}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: match ($this->type) {
                'created' => 'emails.reservations.created',
                'waitlist_released' => 'emails.reservations.waitlist-released',
                'payment_reminder' => 'emails.reservations.payment-reminder',
                default => 'emails.reservations.status',
            },
        );
    }
}
