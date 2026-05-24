<?php

namespace App\Infrastructure\Persistence\Pais;

use PDO;
use App\Domain\Pais\Entity\Pais;
use App\Domain\Pais\Repository\PaisRepositoryInterface;
use App\Utils\Uuid;

class MysqlPaisRepository implements PaisRepositoryInterface
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function findById(string $id): ?Pais
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM db_geo_paisos
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->bindValue(
            ':id',
            uuid::toBinary($id),
            PDO::PARAM_LOB
        );

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return MysqlPaisMapper::map($row);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT *
            FROM db_geo_paisos
            ORDER BY pais_ca
        ");

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = MysqlPaisMapper::map($row);
        }

        return $items;
    }

    public function save(Pais $pais): void
    {
        // insert/update
    }
}
