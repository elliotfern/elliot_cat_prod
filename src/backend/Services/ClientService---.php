<?php

namespace App\Services;

use App\Config\Database;
use App\Utils\Uuid;
use App\Utils\Tables;
use App\Modules\Clients\Schema\ClientSchema;
use App\Utils\Schema\SchemaProcessor;
use Ramsey\Uuid\Uuid as ramsey;
use App\Config\Audit;
use PDO;

class ClientService22
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

    public function create(array $data): array
    {
        $conn = $this->db->getPdo();

        // 1. VALIDACIÓN (schema)
        $schema = ClientSchema::create();

        $clientData = SchemaProcessor::process(
            $data,
            $schema
        );

        // 2. UUID
        $id = Uuid::toBinary(ramsey::uuid7()->getBytes());

        // 3. NORMALIZACIÓN UUID RELACIONALES
        $ciutat_id = isset($clientData['ciutat_id'])
            ? Uuid::toBinary($clientData['ciutat_id'])
            : null;

        $provincia_id = isset($clientData['provincia_id'])
            ? Uuid::toBinary($clientData['provincia_id'])
            : null;

        $pais_id = isset($clientData['pais_id'])
            ? Uuid::toBinary($clientData['pais_id'])
            : null;

        $estat_id = isset($clientData['estat_id'])
            ? Uuid::toBinary($clientData['estat_id'])
            : null;

        try {

            $conn->beginTransaction();

            $table = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS);

            $sql = "
            INSERT INTO {$table}
            (
                id,
                clientNom,
                clientCognoms,
                clientEmail,
                clientWeb,
                clientNIF,
                clientEmpresa,
                clientAdreca,
                clientCP,
                ciutat_id,
                provincia_id,
                pais_id,
                clientTelefon,
                estat_id,
                clientRegistre
            )
            VALUES
            (
                :id,
                :clientNom,
                :clientCognoms,
                :clientEmail,
                :clientWeb,
                :clientNIF,
                :clientEmpresa,
                :clientAdreca,
                :clientCP,
                :ciutat_id,
                :provincia_id,
                :pais_id,
                :clientTelefon,
                :estat_id,
                :clientRegistre
            )
        ";

            $stmt = $conn->prepare($sql);

            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->bindValue(':clientNom', $clientData['clientNom'], PDO::PARAM_STR);
            $stmt->bindValue(':clientCognoms', $clientData['clientCognoms'] ?? null, $clientData['clientCognoms'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':clientEmail', $clientData['clientEmail'], PDO::PARAM_STR);
            $stmt->bindValue(':clientWeb', $clientData['clientWeb'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':clientNIF', $clientData['clientNIF'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':clientEmpresa', $clientData['clientEmpresa'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':clientAdreca', $clientData['clientAdreca'], PDO::PARAM_STR);
            $stmt->bindValue(':clientCP', $clientData['clientCP'] ?? null, PDO::PARAM_STR);

            $stmt->bindValue(':ciutat_id', $ciutat_id, PDO::PARAM_STR);
            $stmt->bindValue(':provincia_id', $provincia_id, PDO::PARAM_STR);
            $stmt->bindValue(':pais_id', $pais_id, PDO::PARAM_STR);
            $stmt->bindValue(':estat_id', $estat_id, PDO::PARAM_STR);

            $stmt->bindValue(':clientTelefon', $clientData['clientTelefon'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':clientRegistre', $clientData['clientRegistre'], PDO::PARAM_STR);

            $stmt->execute();

            // 4. AUDITORIA (aquí queda muy limpio ya dentro del service)
            Audit::registrarCanvi(
                $conn,
                null, // o userUuid si lo inyectas luego
                "INSERT",
                "Creació client: {$clientData['clientNom']}",
                'db_comptabilitat_clients',
                $id
            );

            $conn->commit();

            return [
                'id' => $id
            ];
        } catch (\Throwable $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            throw $e;
        }
    }

    public function update(array $data): array
    {
        $conn = $this->db->getPdo();

        // 1. VALIDACIÓN
        $schema = ClientSchema::update();
        $clientData = SchemaProcessor::process(
            $data,
            $schema
        );

        // 2. ID obligatorio
        if (empty($clientData['id'])) {
            throw new \Exception("ID requerit per actualitzar client");
        }

        $id = Uuid::toBinary($clientData['id']);

        // 3. NORMALIZACIÓN UUID
        $ciutat_id = isset($clientData['ciutat_id'])
            ? Uuid::toBinary($clientData['ciutat_id'])
            : null;

        $provincia_id = isset($clientData['provincia_id'])
            ? Uuid::toBinary($clientData['provincia_id'])
            : null;

        $pais_id = isset($clientData['pais_id'])
            ? Uuid::toBinary($clientData['pais_id'])
            : null;

        $estat_id = isset($clientData['estat_id'])
            ? Uuid::toBinary($clientData['estat_id'])
            : null;

        try {

            $conn->beginTransaction();

            $table = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS);

            $sql = "
            UPDATE {$table}
            SET
                clientNom = :clientNom,
                clientCognoms = :clientCognoms,
                clientEmail = :clientEmail,
                clientWeb = :clientWeb,
                clientNIF = :clientNIF,
                clientEmpresa = :clientEmpresa,
                clientAdreca = :clientAdreca,
                clientCP = :clientCP,
                ciutat_id = :ciutat_id,
                provincia_id = :provincia_id,
                pais_id = :pais_id,
                clientTelefon = :clientTelefon,
                estat_id = :estat_id,
                clientRegistre = :clientRegistre
            WHERE id = :id
        ";

            $stmt = $conn->prepare($sql);

            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->bindValue(':clientNom', $clientData['clientNom'], PDO::PARAM_STR);
            $stmt->bindValue(':clientCognoms', $clientData['clientCognoms'] ?? null, $clientData['clientCognoms'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':clientEmail', $clientData['clientEmail'], PDO::PARAM_STR);
            $stmt->bindValue(':clientWeb', $clientData['clientWeb'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':clientNIF', $clientData['clientNIF'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':clientEmpresa', $clientData['clientEmpresa'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':clientAdreca', $clientData['clientAdreca'], PDO::PARAM_STR);
            $stmt->bindValue(':clientCP', $clientData['clientCP'] ?? null, PDO::PARAM_STR);

            $stmt->bindValue(':ciutat_id', $ciutat_id, PDO::PARAM_STR);
            $stmt->bindValue(':provincia_id', $provincia_id, PDO::PARAM_STR);
            $stmt->bindValue(':pais_id', $pais_id, PDO::PARAM_STR);
            $stmt->bindValue(':estat_id', $estat_id, PDO::PARAM_STR);

            $stmt->bindValue(':clientTelefon', $clientData['clientTelefon'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':clientRegistre', $clientData['clientRegistre'], PDO::PARAM_STR);

            $stmt->execute();

            // 4. AUDITORIA
            Audit::registrarCanvi(
                $conn,
                null,
                "UPDATE",
                "Actualització client: {$clientData['clientNom']}",
                'db_comptabilitat_clients',
                $id
            );

            $conn->commit();

            return [
                'id' => $clientData['id']
            ];
        } catch (\Throwable $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            throw $e;
        }
    }

    public function delete(string $id): array
    {
        $conn = $this->db->getPdo();

        if (empty($id)) {
            throw new \Exception("ID requerit");
        }

        $binaryId = Uuid::toBinary($id);

        try {

            $conn->beginTransaction();

            $table = $this->db->qi(Tables::DB_COMPTABILITAT_CLIENTS);

            // 1. comprobamos que existe (evita deletes silenciosos)
            $checkSql = "
            SELECT id, clientNom, clientEmail
            FROM {$table}
            WHERE id = :id
            LIMIT 1
        ";

            $stmt = $conn->prepare($checkSql);
            $stmt->bindValue(':id', $binaryId, PDO::PARAM_STR);
            $stmt->execute();

            $client = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$client) {
                throw new \Exception("Client no trobat");
            }

            // 2. soft delete (ajusta esto a tu sistema de estados)
            $sql = "
            UPDATE {$table}
            SET estat_id = :estat_id
            WHERE id = :id
        ";

            $stmt = $conn->prepare($sql);

            // 👉 aquí puedes definir tu estado "ELIMINADO"
            $deletedState = Uuid::toBinary('ESTAT_DELETED_UUID');

            $stmt->bindValue(':id', $binaryId, PDO::PARAM_STR);
            $stmt->bindValue(':estat_id', $deletedState, PDO::PARAM_STR);

            $stmt->execute();

            // 3. AUDITORÍA
            \App\Config\Audit::registrarCanvi(
                $conn,
                null,
                "DELETE",
                "Soft delete client: {$client['clientNom']} ({$client['clientEmail']})",
                'db_comptabilitat_clients',
                $binaryId
            );

            $conn->commit();

            return [
                'id' => $id,
                'deleted' => true
            ];
        } catch (\Throwable $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            throw $e;
        }
    }
}
