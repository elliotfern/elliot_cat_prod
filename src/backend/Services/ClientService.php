<?php

namespace App\Services;

use App\Config\Database;
use App\Utils\Uuid;
use App\Utils\Tables;

class ClientService
{
    public function __construct(
        private Database $db
    ) {}

    public function getById(string $id): ?array
    {
        $clientsTable = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS);
        $citiesTable = $this->db->qi(Tables::DB_CIUTATS);
        $countriesTable = $this->db->qi(Tables::DB_PAISOS);
        $provincesTable = $this->db->qi(Tables::DB_PROVINCIES);
        $statusTable = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT);

        $sql = "
        SELECT
            c.id,
            c.clientNom,
            c.clientCognoms,
            c.clientEmail,
            c.clientWeb,
            c.clientNIF,
            c.clientEmpresa,
            c.clientAdreca,
            c.clientCP,
            c.ciutat_id,
            c.provincia_id,
            c.pais_id,
            c.clientTelefon,
            c.clientRegistre,
            COALESCE(ci.ciutat_ca, ci.ciutat) AS ciutat_final,
            co.pais_ca,
            cou.provincia_ca,
            c.estat_id,
            s.estat
        FROM {$clientsTable} AS c
        LEFT JOIN {$citiesTable} AS ci ON c.ciutat_id = ci.id
        LEFT JOIN {$countriesTable} AS co ON c.pais_id = co.id
        LEFT JOIN {$provincesTable} AS cou ON c.provincia_id = cou.id
        LEFT JOIN {$statusTable} AS s ON c.estat_id = s.id
        WHERE c.id = :id
        LIMIT 1
    ";

        return $this->db->getOne($sql, [
            'id' => Uuid::toBinary($id)
        ]);
    }

    public function getAll(): array
    {
        $clientsTable = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS);
        $citiesTable = $this->db->qi(Tables::DB_CIUTATS);
        $countriesTable = $this->db->qi(Tables::DB_PAISOS);
        $provincesTable = $this->db->qi(Tables::DB_PROVINCIES);
        $statusTable = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT);

        $sql = "
        SELECT
            c.id,
            c.clientNom,
            c.clientCognoms,
            c.clientEmail,
            c.clientWeb,
            c.clientNIF,
            c.clientEmpresa,
            c.clientAdreca,
            c.clientCP,
            c.ciutat_id,
            c.provincia_id,
            c.pais_id,
            c.clientTelefon,
            c.clientRegistre,
            ci.ciutat_ca,
            co.pais_ca,
            cou.provincia_ca,
            c.estat_id,
            s.estat,
            s.ordre
        FROM {$clientsTable} AS c
        LEFT JOIN {$citiesTable} AS ci ON c.ciutat_id = ci.id
        LEFT JOIN {$countriesTable} AS co ON c.pais_id = co.id
        LEFT JOIN {$provincesTable} AS cou ON c.provincia_id = cou.id
        LEFT JOIN {$statusTable} AS s ON c.estat_id = s.id
        ORDER BY c.clientCognoms ASC
    ";

        $result = $this->db->getData($sql);
        return $result;
    }
}
