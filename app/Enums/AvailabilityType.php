<?php

namespace App\Enums;

enum AvailabilityType: string
{
    case APERTA = 'APERTA';
    case LIMITATA = 'LIMITATA';

    public function label(): string
    {
        return match ($this) {
            self::APERTA => 'Colli illimitati',
            self::LIMITATA => 'Colli limitati',
        };
    }

    public function isLimited(): bool
    {
        return $this === self::LIMITATA;
    }
}
