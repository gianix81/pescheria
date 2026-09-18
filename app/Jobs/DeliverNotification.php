<?php

namespace App\Jobs;

use App\Enums\DeliveryStatus;
use App\Enums\NotificationChannel;
use App\Mail\NotificationMail;
use App\Models\NotificationDelivery;
use App\Services\Notifications\WhatsAppGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * Consegna di una notifica su un canale opzionale.
 * Idempotente: se la riga è già SENT il job esce senza rinviare.
 */
class DeliverNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public function __construct(public int $deliveryId) {}

    public function handle(WhatsAppGateway $whatsapp): void
    {
        $delivery = NotificationDelivery::with('notification.user', 'notification.opportunity')->find($this->deliveryId);

        if (! $delivery || $delivery->status === DeliveryStatus::SENT) {
            return;
        }

        $delivery->increment('attempts');

        try {
            match ($delivery->channel) {
                NotificationChannel::EMAIL => $this->sendEmail($delivery),
                NotificationChannel::WHATSAPP => $whatsapp->send($delivery->notification),
                NotificationChannel::IN_APP => true,
            };

            $delivery->forceFill([
                'status' => DeliveryStatus::SENT,
                'sent_at' => now(),
                'error' => null,
            ])->save();
        } catch (\Throwable $e) {
            $delivery->forceFill([
                'status' => DeliveryStatus::FAILED,
                'error' => substr($e->getMessage(), 0, 500),
            ])->save();

            throw $e;
        }
    }

    private function sendEmail(NotificationDelivery $delivery): void
    {
        $user = $delivery->notification->user;

        if (blank($user?->email)) {
            throw new \RuntimeException('Destinatario senza email.');
        }

        Mail::to($user->email)->send(new NotificationMail($delivery->notification));
    }
}
