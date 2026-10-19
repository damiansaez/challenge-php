<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\Product;
use JsonSerializable;

final class ProductResponse implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly ?string $descripcion,
        public readonly string $precio,
        public readonly string $precioUsd,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }

    public static function fromProduct(Product $product, string $precioUsd): self
    {
        return new self(
            $product->id,
            $product->nombre,
            $product->descripcion,
            $product->precio,
            $precioUsd,
            $product->createdAt,
            $product->updatedAt,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'precio_usd' => $this->precioUsd,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
