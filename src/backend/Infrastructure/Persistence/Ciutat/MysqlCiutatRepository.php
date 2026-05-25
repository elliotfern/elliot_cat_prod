<?php

namespace App\Infrastructure\Persistence\Ciutat;

use PDO;
use App\Domain\Ciutat\Repository\CiutatRepository;
use App\Domain\Shared\Exception\NotFoundException;
use App\Domain\Ciutat\Entity\Ciutat;
use App\Utils\Uuid;

class MysqlCiutatRepository implements CiutatRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function getAll(): array
    {
        $sql = "
            SELECT
                c.id,
                c.ciutat,
                c.ciutat_ca,
                c.ciutat_en,
                c.descripcio,
                c.created_at,
                c.updated_at,

                p.id AS pais_id,
                p.pais_ca,
                p.pais_en,
                p.created_at AS pais_created_at,
                p.updated_at AS pais_updated_at

            FROM db_geo_ciutats c

            INNER JOIN db_geo_paisos p
                ON c.pais_id = p.id

            ORDER BY c.ciutat ASC
        ";

        $stmt = $this->pdo->query($sql);

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = MysqlCiutatMapper::map($row);
        }

        return $items;
    }

    public function findById(string $id): Ciutat
    {
        $sql = "
        SELECT
            c.id,
            c.ciutat,
            c.ciutat_ca,
            c.ciutat_en,
            c.descripcio,
            c.created_at,
            c.updated_at,

            p.id AS pais_id,
            p.pais_ca,
            p.pais_en,
            p.created_at AS pais_created_at,
            p.updated_at AS pais_updated_at

        FROM db_geo_ciutats c

        INNER JOIN db_geo_paisos p
            ON c.pais_id = p.id

        WHERE c.id = :id

        LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => Uuid::toBinary($id)
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Ciutat no trobada');
        }

        return MysqlCiutatMapper::map($row);
    }
}
