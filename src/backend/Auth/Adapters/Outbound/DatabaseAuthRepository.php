<?php

namespace App\Auth\Adapters\Outbound;

use App\Auth\Core\Ports\Out\AuthRepositoryInterface;
use App\Helpers\JwtHelper;
use PDO;

class DatabaseAuthRepository implements AuthRepositoryInterface
{
    private $conn;

    // Constructor para recibir la conexión PDO
    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function loginAuth(string $email, string $password): array
    {
        try {
            $sql = "SELECT email, userType, password, nom, cognom FROM db_users WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'message' => 'No hem trobat aquest usuari'];
            }

            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                // Crear el payload para el JWT
                $payload = [
                    'user_id' => '1',
                    'email' => $user['email'],
                    'nom' => $user['nom'] . ' ' . $user['cognom'],
                    'user_type' => $user['userType'],
                    'kid' => 'key_api',
                    'iat' => time(),
                    'exp' => time() + 604800,
                ];

                $jwt = JwtHelper::generateJwt($payload, $_ENV['TOKEN']); // Obtener la clave desde .env
                $expiration = time() + (10 * 24 * 60 * 60); // 10 días

                $arr_cookie_options = array(
                    'expires' => $expiration,
                    'path' => '/',
                    'domain' => 'elliot.cat',
                    'secure' => true,     // or false
                    'httponly' => true,    // or false
                    'samesite' => 'Strict' // None || Lax  || Strict
                );

                setcookie('token', $jwt, $arr_cookie_options);

                return [
                    'success' => true,
                    'message' => 'Dades d\'usuari correctes',
                    'user_type' => $user['userType'],
                ];
            }

            return ['success' => false, 'message' => 'Contrasenya incorrecta'];
        } catch (\PDOException $e) {
            error_log('Error al obtener las contraseñas: ' . $e->getMessage());
            return [];
        }
    }
}
