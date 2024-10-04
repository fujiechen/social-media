<?php

namespace App\Exceptions;

use Throwable;

class IllegalArgumentException extends \Exception
{
    private string $field;

    public function __construct(string $field = "", $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }
}
