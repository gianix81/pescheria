<?php

namespace App\Enums;

enum MediaType: string
{
    case IMAGE = 'IMAGE';
    case VIDEO = 'VIDEO';

    public function label(): string
    {
        return $this === self::IMAGE ? 'Immagine' : 'Video';
    }
}
