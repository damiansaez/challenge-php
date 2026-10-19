<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\ProductData;
use App\Dto\ProductResponse;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Product;
use App\Repositories\ProductRepository;

final class ProductService
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly PriceConverter $converter,
    ) {
    }

    /**
     * @return list<ProductResponse>
     */
    public function getAll(): array
    {
        return array_map($this->toResponse(...), $this->repository->findAll());
    }

    public function getById(int $id): ProductResponse
    {
        return $this->toResponse($this->findOrFail($id));
    }

    public function create(array $input): ProductResponse
    {
        $id = $this->repository->create($this->validate($input));

        return $this->getById($id);
    }

    public function update(int $id, array $input): ProductResponse
    {
        $this->findOrFail($id);

        $this->repository->update($id, $this->validate($input));

        return $this->getById($id);
    }

    public function delete(int $id): void
    {
        if (!$this->repository->delete($id)) {
            throw new NotFoundException('Producto no encontrado');
        }
    }

    private function findOrFail(int $id): Product
    {
        return $this->repository->findById($id) ?? throw new NotFoundException('Producto no encontrado');
    }

    private function validate(array $data): ProductData
    {
        $errors = [];

        $nombre = $data['nombre'] ?? null;
        if ($nombre !== null && !is_string($nombre)) {
            $errors['nombre'] = 'El nombre debe ser texto';
        } elseif (!is_string($nombre) || trim($nombre) === '') {
            $errors['nombre'] = 'El nombre es obligatorio';
        } elseif (mb_strlen(trim($nombre)) > 255) {
            $errors['nombre'] = 'El nombre no puede superar los 255 caracteres';
        }

        $descripcion = $data['descripcion'] ?? null;
        if ($descripcion !== null && !is_string($descripcion)) {
            $errors['descripcion'] = 'La descripción debe ser texto';
        } elseif (is_string($descripcion) && mb_strlen($descripcion) > 500) {
            $errors['descripcion'] = 'La descripción no puede superar los 500 caracteres';
        }

        $precio = $data['precio'] ?? null;
        $errorPrecio = $this->validatePrecio($precio);
        if ($errorPrecio !== null) {
            $errors['precio'] = $errorPrecio;
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new ProductData(
            trim($nombre),
            $descripcion,
            trim((string) $precio),
        );
    }

    private function validatePrecio(mixed $precio): ?string
    {
        if ($precio === null || $precio === '') {
            return 'El precio es obligatorio';
        }

        if (!is_int($precio) && !is_float($precio) && !is_string($precio)) {
            return 'El precio debe ser un número';
        }

        $texto = trim((string) $precio);

        if (preg_match('/^-?\d+(\.\d+)?$/', $texto) !== 1) {
            return 'El precio debe ser un número con punto decimal (por ejemplo, 49999.99)';
        }

        if (bccomp($texto, '0', 10) <= 0) {
            return 'El precio debe ser mayor que 0';
        }

        if (preg_match('/\.\d{3,}$/', $texto) === 1) {
            return 'El precio puede tener como máximo 2 decimales';
        }

        // Máximo de la columna DECIMAL(10,2)
        if (bccomp($texto, '99999999.99', 2) > 0) {
            return 'El precio no puede superar 99.999.999,99';
        }

        return null;
    }

    private function toResponse(Product $product): ProductResponse
    {
        return ProductResponse::fromProduct($product, $this->converter->toUsd($product->precio));
    }
}
