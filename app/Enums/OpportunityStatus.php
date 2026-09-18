<?php

namespace App\Enums;

enum OpportunityStatus: string
{
    case BOZZA = 'BOZZA';
    case IN_VERIFICA = 'IN_VERIFICA';
    case DA_CORREGGERE = 'DA_CORREGGERE';
    case PROGRAMMATA = 'PROGRAMMATA';
    case APERTA = 'APERTA';
    case SCADUTA = 'SCADUTA';
    case CHIUSA = 'CHIUSA';
    case ANNULLATA = 'ANNULLATA';
    case ARCHIVIATA = 'ARCHIVIATA';

    public function label(): string
    {
        return match ($this) {
            self::BOZZA => 'Bozza',
            self::IN_VERIFICA => 'In verifica',
            self::DA_CORREGGERE => 'Da correggere',
            self::PROGRAMMATA => 'Programmata',
            self::APERTA => 'Aperta',
            self::SCADUTA => 'Scaduta',
            self::CHIUSA => 'Chiusa',
            self::ANNULLATA => 'Annullata',
            self::ARCHIVIATA => 'Archiviata',
        };
    }

    /** Classi Tailwind del badge: colore + testo, mai solo colore. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::BOZZA => 'bg-slate-100 text-slate-700 ring-slate-300',
            self::IN_VERIFICA => 'bg-amber-50 text-amber-800 ring-amber-300',
            self::DA_CORREGGERE => 'bg-rose-50 text-rose-800 ring-rose-300',
            self::PROGRAMMATA => 'bg-sky-50 text-sky-800 ring-sky-300',
            self::APERTA => 'bg-emerald-50 text-emerald-800 ring-emerald-300',
            self::SCADUTA => 'bg-slate-200 text-slate-700 ring-slate-400',
            self::CHIUSA => 'bg-slate-200 text-slate-700 ring-slate-400',
            self::ANNULLATA => 'bg-rose-100 text-rose-900 ring-rose-400',
            self::ARCHIVIATA => 'bg-slate-100 text-slate-500 ring-slate-300',
        };
    }

    /** Transizioni ammesse dalla macchina a stati (sezione 6 del capitolato). */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::BOZZA => [self::IN_VERIFICA, self::ANNULLATA],
            self::IN_VERIFICA => [self::DA_CORREGGERE, self::PROGRAMMATA, self::APERTA, self::ANNULLATA],
            self::DA_CORREGGERE => [self::IN_VERIFICA, self::ANNULLATA],
            self::PROGRAMMATA => [self::APERTA, self::ANNULLATA, self::CHIUSA],
            self::APERTA => [self::SCADUTA, self::CHIUSA, self::ANNULLATA],
            self::SCADUTA => [self::CHIUSA, self::ARCHIVIATA, self::ANNULLATA],
            self::CHIUSA => [self::ARCHIVIATA],
            self::ANNULLATA => [self::ARCHIVIATA],
            self::ARCHIVIATA => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /** Stati "di lavorazione": l'opportunità non è ancora stata pubblicata. */
    public function isEditableByBuyer(): bool
    {
        return in_array($this, [self::BOZZA, self::DA_CORREGGERE], true);
    }

    /**
     * Stati in cui il Buyer può modificare i dati (capitolato §3: «creare e
     * modificare opportunità in qualsiasi momento»).
     *
     * Restano fuori IN_VERIFICA, perché §6 la vuole bloccata fino all'esito del
     * Tecnico, e gli stati terminali, dove l'opportunità è ormai storia.
     */
    public function isModificabile(): bool
    {
        return in_array($this, [
            self::BOZZA, self::DA_CORREGGERE, self::PROGRAMMATA, self::APERTA, self::SCADUTA,
        ], true);
    }

    /** L'opportunità è già stata vista dai punti vendita? */
    public function isPubblicata(): bool
    {
        return in_array($this, [self::PROGRAMMATA, self::APERTA, self::SCADUTA], true);
    }

    /** Stati visibili ai Capi Reparto. */
    public static function visibleToStores(): array
    {
        return [self::APERTA, self::SCADUTA, self::CHIUSA, self::ANNULLATA];
    }

    public static function terminal(): array
    {
        return [self::CHIUSA, self::ANNULLATA, self::ARCHIVIATA];
    }
}
