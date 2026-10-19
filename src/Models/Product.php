<?php

declare(strict_types=1);

namespace App\Models;

final class Product
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly ?string $descripcion,
        public readonly string $precio,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['nombre'],
            $row['descripcion'] !== null ? (string) $row['descripcion'] : null,
            (string) $row['precio'],
            (string) $row['created_at'],
            (string) $row['updated_at'],
        );
    }
}