<?php

namespace App\Infrastructure\Persistence\Client;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Repository\ClientRepositoryInterface;
use App\Domain\Client\ValueObject\ClientId;
use App\Config\Database;
use App\Utils\Tables;


final class MysqlClientRepository implements ClientRepositoryInterface
{
    public function __construct(
        private Database $db
    ) {}

    public function findById(ClientId $id): ?Client
    {
        $clientsTable = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS);
        //$citiesTable = $this->db->qi(Tables::DB_CIUTATS);
        //$countriesTable = $this->db->qi(Tables::DB_PAISOS);
        //$provincesTable = $this->db->qi(Tables::DB_PROVINCIES);
        //$statusTable = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT);
        /* COALESCE(ci.ciutat_ca, ci.ciutat) AS ciutat_final,
            co.pais_ca,
            cou.provincia_ca,
            s.estat
            LEFT JOIN {$citiesTable} AS ci ON c.ciutat_id = ci.id
        LEFT JOIN {$countriesTable} AS co ON c.pais_id = co.id
        LEFT JOIN {$provincesTable} AS cou ON c.provincia_id = cou.id
        LEFT JOIN {$statusTable} AS s ON c.estat_id = s.id
            */
        $sql = "SELECT
            c.id,
            c.nom,
            c.cognoms,
            c.email,
            c.web,
            c.nif,
            c.empresa,
            c.adreca,
            c.cp,
            c.ciutat_id,
            c.provincia_id,
            c.pais_id,
            c.telefon,
            c.registre,
            c.estat_id           
        FROM {$clientsTable} AS c
        WHERE c.id = :id
        LIMIT 1";

        $row = $this->db->getOne($sql, [
            'id' => $id->value()
        ]);

        if (!$row) {
            return null;
        }

        $client = ClientMapper::fromArray($row);
        return $client;
    }

    public function save(Client $client): void
    {
        // más adelante
    }

    public function findAll(): array
    {
        $clientsTable = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS);

        $sql = "
        SELECT
            c.id,
            c.nom,
            c.cognoms,
            c.email,
            c.web,
            c.nif,
            c.empresa,
            c.adreca,
            c.cp,
            c.ciutat_id,
            c.provincia_id,
            c.pais_id,
            c.telefon,
            c.registre,
            c.estat_id
        FROM {$clientsTable} AS c
        ORDER BY c.cognoms ASC
    ";

        $rows = $this->db->getData($sql);

        $clients = [];

        foreach ($rows as $row) {
            $clients[] = ClientMapper::fromArray($row);
        }

        return $clients;
    }
}
