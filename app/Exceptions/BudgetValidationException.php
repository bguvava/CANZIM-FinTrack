<?php

namespace App\Exceptions;

use Exception;

class BudgetValidationException extends Exception
{
    /**
     * @var array<string, mixed>
     */
    protected array $details;

    /**
     * Create a new budget validation exception.
     *
     * @param  array<string, mixed>  $details
     */
    public function __construct(string $message, array $details = [])
    {
        parent::__construct($message);
        $this->details = $details;
    }

    /**
     * Get the validation error details.
     *
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
