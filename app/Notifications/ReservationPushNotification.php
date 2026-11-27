<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ReservationPushNotification extends Notification
{
    public function __construct(
        public ?Reservation $reservation = null,
        public string $type = '',
    ) {
        $this->reservation?->loadMissing(['house.street', 'user']);
    }

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $date = $this->reservation ? date('d/m/Y', strtotime($this->reservation->reservation_date)) : '';
        $address = trim(($this->reservation?->house?->street?->name ?? '') . ' #' . ($this->reservation?->house?->house_number ?? ''));

        [$title, $body, $url] = match ($this->type) {
            'created' => [
                "Nueva solicitud de reserva - {$date}",
                "Solicitud de {$address} pendiente de revisión.",
                '/admin',
            ],
            'approved' => [
                "Tu reserva del {$date} fue aprobada",
                'Tu solicitud de reservación ha sido confirmada.',
                '/dashboard',
            ],
            'rejected' => [
                "Tu reserva del {$date} fue rechazada",
                $this->reservation?->rejection_reason
                    ? "Motivo: {$this->reservation->rejection_reason}"
                    : 'Tu solicitud de reservación no fue aprobada.',
                '/dashboard',
            ],
            'waitlist_released' => [
                "¡Fecha disponible! - {$date}",
                'La fecha en tu lista de espera se ha liberado para reservar.',
                '/dashboard',
            ],
            'payment_reminder' => [
                'Recordatorio de pago pendiente',
                'El plazo para cubrir la cuota de tu reservación está próximo a vencer.',
                '/dashboard',
            ],
            default => [
                "Actualización de reserva - {$date}",
                'Tienes una actualización en tu reservación.',
                '/dashboard',
            ],
        };

        return (new WebPushMessage)
            ->title($title)
            ->icon('/images/icon-192.png')
            ->body($body)
            ->data(['url' => $url]);
    }
}
