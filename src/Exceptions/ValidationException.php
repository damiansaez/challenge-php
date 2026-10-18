<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ValidationException extends HttpException
{
    /**
     * @param array<string, string> $errors campo => mensaje
     */
    public function __construct(array $errors, string $message = 'Datos inválidos')
    {
        parent::__construct($message, 422, $errors);
    }
}