<?php

namespace App\Exceptions;

use App\Enums\OpportunityStatus;

class InvalidTransitionException extends DomainException
{
    public function __construct(OpportunityStatus $from, OpportunityStatus $to)
    {
        parent::__construct("Transizione non consentita: da {$from->label()} a {$to->label()}.");
    }
}
