<?php

namespace App\Enums;

enum ResponseStatus: string
{
    case NON_COMPILATA = 'NON_COMPILATA';
    case BOZZA = 'BOZZA';
    case INVIATA_ACQUISTO = 'INVIATA_ACQUISTO';
    case INVIATA_RIFIUTO = 'INVIATA_RIFIUTO';
    case RIAPERTA = 'RIAPERTA';
    case BLOCCATA = 'BLOCCATA';

    public function label(): string
    {
        return match ($this) {
            self::NON_COMPILATA => 'Non compilata',
            self::BOZZA => 'Bozza',
            self::INVIATA_ACQUISTO => 'Acquisto inviato',
            self::INVIATA_RIFIUTO => 'Rifiuto inviato',
            self::RIAPERTA => 'Riaperta',
            self::BLOCCATA => 'Bloccata',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::NON_COMPILATA => 'Manca',
            self::BOZZA => 'Bozza',
            self::INVIATA_ACQUISTO => 'Acquisto',
            self::INVIATA_RIFIUTO => 'Rifiuto',
            self::RIAPERTA => 'Riaperta',
            self::BLOCCATA => 'Bloccata',
        };
    }

    /** Icona testuale: lo stato non è mai comunicato dal solo colore. */
    public function icon(): string
    {
        return match ($this) {
            self::NON_COMPILATA => '—',
            self::BOZZA => '✎',
            self::INVIATA_ACQUISTO => '✓',
            self::INVIATA_RIFIUTO => '✗',
            self::RIAPERTA => '↻',
            self::BLOCCATA => '⊘',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NON_COMPILATA => 'bg-slate-100 text-slate-600 ring-slate-300',
            self::BOZZA => 'bg-amber-50 text-amber-800 ring-amber-300',
            self::INVIATA_ACQUISTO => 'bg-emerald-50 text-emerald-800 ring-emerald-300',
            self::INVIATA_RIFIUTO => 'bg-slate-200 text-slate-700 ring-slate-400',
            self::RIAPERTA => 'bg-sky-50 text-sky-800 ring-sky-300',
            self::BLOCCATA => 'bg-rose-50 text-rose-800 ring-rose-300',
        };
    }

    /** La risposta è stata inviata in via definitiva dal CR. */
    public function isSubmitted(): bool
    {
        return in_array($this, [self::INVIATA_ACQUISTO, self::INVIATA_RIFIUTO], true);
    }
}
