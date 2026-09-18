<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case BUYER = 'BUYER';
    case TECNICO = 'TECNICO';
    case CAPO_REPARTO = 'CAPO_REPARTO';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Super Admin',
            self::BUYER => 'Buyer',
            self::TECNICO => 'Tecnico',
            self::CAPO_REPARTO => 'Capo Reparto',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Accesso completo: gestisce utenti, anagrafiche e tutte le opportunità.',
            self::BUYER => 'Crea e pubblica le opportunità, esporta i risultati.',
            self::TECNICO => 'Verifica le opportunità e monitora le compilazioni.',
            self::CAPO_REPARTO => 'Risponde per il proprio punto vendita.',
        };
    }

    /** Rotta di atterraggio dopo il login. */
    public function homeRoute(): string
    {
        return match ($this) {
            self::ADMIN => 'tecnico.anagrafiche.utenti',
            self::BUYER => 'buyer.dashboard',
            self::TECNICO => 'tecnico.dashboard',
            self::CAPO_REPARTO => 'cr.dashboard',
        };
    }

    /** Il ruolo richiede l'associazione a un punto vendita? */
    public function requiresStore(): bool
    {
        return $this === self::CAPO_REPARTO;
    }

    /** Ruoli assegnabili dall'interfaccia di gestione utenti. */
    public static function assegnabili(): array
    {
        return self::cases();
    }
}
