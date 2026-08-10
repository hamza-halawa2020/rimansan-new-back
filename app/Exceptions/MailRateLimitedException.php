<?php

namespace App\Exceptions;

use Exception;

class MailRateLimitedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Email service is temporarily rate limited. Please try again later.', 429);
    }
}
