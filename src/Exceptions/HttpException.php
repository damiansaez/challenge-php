<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly array $details = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }


    public function getDetails(): array
    {
        return $this->details;
    }

    public function getHeaders(): array
    {
        return [];
    }
}