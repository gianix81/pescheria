<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case IN_APP = 'IN_APP';
    case EMAIL = 'EMAIL';
    case WHATSAPP = 'WHATSAPP';

    public function label(): string
    {
        return match ($this) {
            self::IN_APP => 'In app',
            self::EMAIL => 'Email',
            self::WHATSAPP => 'WhatsApp',
        };
    }
}
