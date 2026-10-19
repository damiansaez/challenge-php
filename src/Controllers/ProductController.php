<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Services\ProductService;

final class ProductController
{
    public function __construct(
        private readonly ProductService $service,
        private readonly Request $request,
    ) {
    }

    public function getAll(): void
    {
        JsonResponse::send(['data' => $this->service->getAll()]);
    }

    public function getById(string $id): void
    {
        JsonResponse::send(['data' => $this->service->getById($this->toId($id))]);
    }

    public function create(): void
    {
        $product = $this->service->create($this->request->json());

        JsonResponse::send(['data' => $product], 201, ['Location' => '/productos/' . $product->id]);
    }

    public function update(string $id): void
    {
        JsonResponse::send(['data' => $this->service->update($this->toId($id), $this->request->json())]);
    }

    public function delete(string $id): void
    {
        $this->service->delete($this->toId($id));

        JsonResponse::noContent();
    }

    private function toId(string $id): int
    {
        if (!ctype_digit($id) || (int) $id < 1) {
            throw new ValidationException(['id' => 'El id debe ser un entero positivo']);
        }

        return (int) $id;
    }
}