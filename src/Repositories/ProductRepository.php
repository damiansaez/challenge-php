<?php


declare(strict_types=1);

namespace App\Repositories;

use App\Dto\ProductData;
use App\Models\Product;
use PDO;

final class ProductRepository
{
    private const COLUMNS = 'id, nombre, descripcion, precio, created_at, updated_at';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query('SELECT ' . self::COLUMNS . ' FROM productos ORDER BY id');

        return array_map(Product::fromRow(...), $statement->fetchAll());
    }

    public function findById(int $id): ?Product
    {
        $statement = $this->pdo->prepare('SELECT ' . self::COLUMNS . ' FROM productos WHERE id = :id');
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        return $row === false ? null : Product::fromRow($row);
    }

    public function create(ProductData $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO productos (nombre, descripcion, precio) VALUES (:nombre, :descripcion, :precio)'
        );
        $statement->execute([
            'nombre' => $data->nombre,
            'descripcion' => $data->descripcion,
            'precio' => $data->precio,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, ProductData $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'nombre' => $data->nombre,
            'descripcion' => $data->descripcion,
            'precio' => $data->precio,
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM productos WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}