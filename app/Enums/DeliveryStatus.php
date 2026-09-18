<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'PENDING';
    case SENT = 'SENT';
    case FAILED = 'FAILED';
    case SKIPPED = 'SKIPPED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'In coda',
            self::SENT => 'Inviata',
            self::FAILED => 'Errore',
            self::SKIPPED => 'Canale disattivo',
        };
    }
}
