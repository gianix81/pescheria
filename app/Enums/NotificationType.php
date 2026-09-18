<?php

namespace App\Enums;

enum NotificationType: string
{
    case OPPORTUNITA_IN_VERIFICA = 'OPPORTUNITA_IN_VERIFICA';
    case OPPORTUNITA_APPROVATA = 'OPPORTUNITA_APPROVATA';
    case OPPORTUNITA_RESPINTA = 'OPPORTUNITA_RESPINTA';
    case OPPORTUNITA_APERTA = 'OPPORTUNITA_APERTA';
    case OPPORTUNITA_MODIFICATA = 'OPPORTUNITA_MODIFICATA';
    case OPPORTUNITA_CHIUSA = 'OPPORTUNITA_CHIUSA';
    case OPPORTUNITA_ANNULLATA = 'OPPORTUNITA_ANNULLATA';
    case SOLLECITO_RISPOSTA = 'SOLLECITO_RISPOSTA';
    case RISPOSTA_RIAPERTA = 'RISPOSTA_RIAPERTA';
    case RIEPILOGO_FINALE = 'RIEPILOGO_FINALE';

    public function label(): string
    {
        return match ($this) {
            self::OPPORTUNITA_IN_VERIFICA => 'Opportunità da verificare',
            self::OPPORTUNITA_APPROVATA => 'Opportunità approvata',
            self::OPPORTUNITA_RESPINTA => 'Opportunità respinta',
            self::OPPORTUNITA_APERTA => 'Nuova opportunità aperta',
            self::OPPORTUNITA_MODIFICATA => 'Opportunità modificata',
            self::OPPORTUNITA_CHIUSA => 'Opportunità chiusa',
            self::OPPORTUNITA_ANNULLATA => 'Opportunità annullata',
            self::SOLLECITO_RISPOSTA => 'Sollecito risposta',
            self::RISPOSTA_RIAPERTA => 'Risposta riaperta',
            self::RIEPILOGO_FINALE => 'Riepilogo finale',
        };
    }
}
