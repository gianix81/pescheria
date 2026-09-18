<?php

namespace App\Exceptions;

class InsufficientStockException extends DomainException
{
    public function __construct(public readonly int $remaining, public readonly int $requested)
    {
        $message = $remaining === 0
            ? 'Disponibilità esaurita: non è più possibile acquistare questa opportunità.'
            : "Disponibilità insufficiente: restano {$remaining} colli, ne hai richiesti {$requested}.";

        parent::__construct($message);
    }
}
