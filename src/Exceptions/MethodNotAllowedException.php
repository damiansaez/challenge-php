<?php

declare(strict_types=1);

namespace App\Exceptions;

final class MethodNotAllowedException extends HttpException
{
    public function __construct(private readonly array $allowedMethods)
    {
        parent::__construct('Método no permitido', 405);
    }

    public function getHeaders(): array
    {
        return ['Allow' => implode(', ', $this->allowedMethods)];
    }
}