<?php

namespace App\Enums;

enum ReviewOutcome: string
{
    case APPROVATA = 'APPROVATA';
    case RESPINTA = 'RESPINTA';

    public function label(): string
    {
        return $this === self::APPROVATA ? 'Approvata' : 'Respinta';
    }
}
