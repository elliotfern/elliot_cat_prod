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
        $clientsTable2 = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT);

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
            c.estat_id,
            e.num,
            e.estat
        FROM {$clientsTable} AS c
        INNER JOIN {$clientsTable2} AS e ON c.estat_id = e.id
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
        $clientsTable2 = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT);

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
            c.estat_id,
            e.num,
            e.estat
        FROM {$clientsTable} AS c
        INNER JOIN {$clientsTable2} AS e ON c.estat_id = e.id
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
