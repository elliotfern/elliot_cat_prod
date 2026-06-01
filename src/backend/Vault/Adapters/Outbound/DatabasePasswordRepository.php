<?php

namespace App\Vault\Adapters\Outbound;

use App\Vault\Core\Ports\Out\PasswordRepositoryInterface;
use PDO;

class DatabasePasswordRepository implements PasswordRepositoryInterface
{
    private $conn;

    // Constructor para recibir la conexión PDO
    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getPasswords(): array
    {
        try {
            $sql = "SELECT v.id, v.servei, v.usuari, t.tipus, v.web, v.dateModified
            FROM db_vault AS v
            LEFT JOIN db_vault_type AS t ON v.tipus = t.id
            ORDER BY v.servei ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            error_log('Error al obtener las contraseñas: ' . $e->getMessage());
            return [];
        }
    }

    public function getPasswordDesencrypt(int $serviceId): array
    {
        try {
            $sql = "SELECT v.password, v.iv
            FROM db_vault AS v
            WHERE id = :vaultId";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':vaultId', $serviceId, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch();

            $encryptionToken = $_ENV['ENCRYPTATION_TOKEN'];

            if (!$data) {
                echo json_encode(['error' => 'Password not found']);
                exit;  // Detener la ejecución del script
            }

            $iv2 = base64_decode($data['iv']);
            if (strlen($iv2) !== openssl_cipher_iv_length('aes-256-cbc')) {
                return ['error' => 'Invalid IV length'];
            }

            if ($data) {
                $decryptedPassword = openssl_decrypt($data['password'], 'aes-256-cbc', $encryptionToken, 0, $iv2);
                return ['password' => $decryptedPassword];
            }

            return null;
        } catch (\PDOException $e) {
            error_log('Error al obtener las contraseñas: ' . $e->getMessage());
            return [];
        }
    }

    public function getClau2FDesencrypt(int $serviceId): array
    {
        try {
            $sql = "SELECT v.clau2f, v.iv2f
            FROM db_vault AS v
            WHERE id = :vaultId";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':vaultId', $serviceId, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch();

            $encryptionToken = $_ENV['ENCRYPTATION_TOKEN'];

            if (!$data) {
                echo json_encode(['error' => 'Password not found']);
                exit;  // Detener la ejecución del script
            }

            $iv2f = base64_decode($data['iv2f']);
            if (strlen($iv2f) !== openssl_cipher_iv_length('aes-256-cbc')) {
                return ['error' => 'Invalid IV length'];
            }

            if ($data) {
                $decryptedPassword = openssl_decrypt($data['clau2f'], 'aes-256-cbc', $encryptionToken, 0, $iv2f);



                // Función para obtener el código TOTP
                function getTotpCode($secret, $timeStep = 30, $digits = 6)
                {
                    $time = floor(time() / $timeStep);
                    $time = pack('J', $time); // Convertir a binario de 8 bytes

                    $key = base32Decode($secret);
                    $hash = hash_hmac('sha1', $time, $key, true);

                    $offset = ord($hash[19]) & 0xf;
                    $truncatedHash = substr($hash, $offset, 4);

                    $code = unpack('N', $truncatedHash)[1] & 0x7fffffff;
                    $code = $code % pow(10, $digits);

                    return str_pad($code, $digits, '0', STR_PAD_LEFT);
                }

                // Función para decodificar una clave base32
                function base32Decode($secret)
                {
                    if (empty($secret)) {
                        return '';
                    }

                    $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
                    $base32charsFlipped = array_flip(str_split($base32chars));

                    $buffer = 0;
                    $bufferSize = 0;
                    $result = '';

                    $secret = strtoupper(rtrim($secret, '='));

                    for ($i = 0; $i < strlen($secret); $i++) {
                        $char = $secret[$i];

                        if (!isset($base32charsFlipped[$char])) {
                            continue;
                        }

                        $buffer = ($buffer << 5) | $base32charsFlipped[$char];
                        $bufferSize += 5;

                        if ($bufferSize >= 8) {
                            $bufferSize -= 8;
                            $result .= chr(($buffer >> $bufferSize) & 0xFF);
                        }
                    }

                    return $result;
                }

                // Ejemplo de uso
                $secret = $decryptedPassword; // Reemplaza esto con el secreto real
                $totpCode = getTotpCode($secret);

                return ['code' => $totpCode];
            }

            return null;
        } catch (\PDOException $e) {
            error_log('Error al obtener las contraseñas: ' . $e->getMessage());
            return [];
        }
    }

    public function savePassword(int $userId, string $serviceName, string $password, string $type, string $url): void
    {
        try {
            $sql = "INSERT INTO passwords (user_id, service_name, password, type, url) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId, $serviceName, $password, $type, $url]);
        } catch (\PDOException $e) {
            error_log('Error al guardar la contraseña: ' . $e->getMessage());
        }
    }
}
