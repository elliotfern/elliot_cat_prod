<?php

declare(strict_types=1);

namespace App\Application\Agenda\Service;

use PDO;

final class BirthdayService
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function getBetween(string $from, string $to): array
    {
        $yearFrom = (int)substr($from, 0, 4);

        $sql = <<<SQL
            SELECT
                t.id,
                t.titol,
                t.descripcio,
                t.tipus,
                t.lloc,
                CONCAT(t.ymd, ' 00:00:00') AS data_inici,
                CONCAT(t.ymd, ' 23:59:59') AS data_fi,
                t.tot_el_dia,
                t.estat,
                t.creat_el,
                t.actualitzat_el,
                t.origen,
                t.contacte_id
            FROM (
                SELECT
                    (-c.id) AS id,
                    CONCAT('🎂 ', c.nom, ' ', c.cognoms) AS titol,
                    NULL AS descripcio,
                    'aniversari' AS tipus,
                    NULL AS lloc,
                    CASE
                        WHEN MONTH(c.data_naixement) = 2 AND DAY(c.data_naixement) = 29
                            THEN DATE(LAST_DAY(CONCAT(:yearFrom, '-02-01')))
                        ELSE DATE(CONCAT(
                            :yearFrom2, '-',
                            LPAD(MONTH(c.data_naixement), 2, '0'), '-',
                            LPAD(DAY(c.data_naixement), 2, '0')
                        ))
                    END AS ymd,
                    1 AS tot_el_dia,
                    'confirmat' AS estat,
                    NOW() AS creat_el,
                    NOW() AS actualitzat_el,
                    'contacte' AS origen,
                    c.id AS contacte_id
                FROM db_contactes c
                WHERE c.data_naixement IS NOT NULL
            ) t
            WHERE t.ymd BETWEEN :fromDate AND :toDate
            ORDER BY t.ymd ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':yearFrom'  => $yearFrom,
            ':yearFrom2' => $yearFrom,
            ':fromDate'  => $from,
            ':toDate'    => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
