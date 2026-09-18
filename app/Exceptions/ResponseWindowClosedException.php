<?php

namespace App\Exceptions;

class ResponseWindowClosedException extends DomainException
{
    public function __construct(string $message = 'I termini per rispondere sono scaduti.')
    {
        parent::__construct($message);
    }
}
