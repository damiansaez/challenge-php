<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;
use JsonException;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        private readonly string $body,
    ) {
    }

    public static function fromGlobals(): self
    {
        return new self(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
            (string) file_get_contents('php://input'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        try {
            $data = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new HttpException('El cuerpo debe ser un JSON válido', 400, [], $e);
        }

        if (!is_array($data)) {
            throw new HttpException('El cuerpo debe ser un objeto JSON', 400);
        }

        return $data;
    }
}