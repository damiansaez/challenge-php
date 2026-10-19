<?php

declare(strict_types=1);

namespace App\Dto;

final class ProductData
{
    public function __construct(
        public readonly string $nombre,
        public readonly ?string $descripcion,
        public readonly string $precio,
    ) {
    }
}
