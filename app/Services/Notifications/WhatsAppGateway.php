<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Canale WhatsApp opzionale (WhatsApp Business API / Cloud API).
 *
 * Regola di prodotto: il messaggio NON contiene pulsanti che registrano ordini.
 * Porta solo un deep link alla scheda, che richiede autenticazione.
 */
class WhatsAppGateway
{
    public function send(Notification $notification): bool
    {
        $config = config('pescheria.notifiche.whatsapp');
        $phone = $notification->user->phone;

        if (blank($phone)) {
            throw new \RuntimeException('Utente senza numero di telefono: impossibile inviare su WhatsApp.');
        }

        if (blank($config['api_url']) || blank($config['token'])) {
            throw new \RuntimeException('Canale WhatsApp non configurato (WHATSAPP_API_URL / WHATSAPP_API_TOKEN).');
        }

        $text = trim($notification->title."\n".($notification->body ?? '')."\n\nApri la scheda: ".$notification->url);

        $response = Http::withToken($config['token'])
            ->timeout(15)
            ->post(rtrim($config['api_url'], '/').'/'.$config['phone_id'].'/messages', [
                'messaging_product' => 'whatsapp',
                'to' => preg_replace('/\D+/', '', $phone),
                'type' => 'text',
                'text' => ['preview_url' => true, 'body' => $text],
            ]);

        if ($response->failed()) {
            Log::warning('Invio WhatsApp fallito', ['notification_id' => $notification->id, 'status' => $response->status()]);

            throw new \RuntimeException('WhatsApp ha risposto con stato '.$response->status().'.');
        }

        return true;
    }
}
