<?php

namespace App\Enums;

enum Role: string
{
    case BUYER = 'BUYER';
    case TECNICO = 'TECNICO';
    case CAPO_REPARTO = 'CAPO_REPARTO';

    public function label(): string
    {
        return match ($this) {
            self::BUYER => 'Buyer',
            self::TECNICO => 'Tecnico',
            self::CAPO_REPARTO => 'Capo Reparto',
        };
    }

    /** Rotta di atterraggio dopo il login. */
    public function homeRoute(): string
    {
        return match ($this) {
            self::BUYER => 'buyer.dashboard',
            self::TECNICO => 'tecnico.dashboard',
            self::CAPO_REPARTO => 'cr.dashboard',
        };
    }
}
