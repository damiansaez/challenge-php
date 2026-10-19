<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class PriceConverter
{
    public function __construct(private readonly string $rate)
    {
        if (preg_match('/^\d+(\.\d+)?$/', $rate) !== 1 || bccomp($rate, '0', 10) <= 0) {
            throw new RuntimeException('PRECIO_USD debe ser un número positivo con punto decimal');
        }
    }

    public static function fromEnvironment(): self
    {
        $rate = getenv('PRECIO_USD');

        if ($rate === false) {
            throw new RuntimeException('Falta la variable de entorno PRECIO_USD');
        }

        return new self($rate);
    }

    public function toUsd(string $pesos): string
    {
        return bcdiv($pesos, $this->rate, 2);
    }
}