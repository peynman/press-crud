<?php

namespace Larapress\CRUD\Exceptions;

use Exception;

class RequestException extends Exception
{
    public function __construct($message, $code = 400)
    {
        parent::__construct($message, $code);
    }
}
