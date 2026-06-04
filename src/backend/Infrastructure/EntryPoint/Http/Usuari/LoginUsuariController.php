<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuari;

use App\Application\Usuari\UseCase\LoginUsuariUseCase;
use App\Utils\MissatgesAPI;
use App\Utils\Response;

final class LoginUsuariController
{
    public function __construct(
        private LoginUsuariUseCase $loginUseCase
    ) {}

    public function execute(string $email, string $password): void
    {
        try {
            $result = $this->loginUseCase->execute($email, $password);

            $token = $result['token'];

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

            setcookie(
                'token',
                $token,
                [
                    'expires' => time() + 604800,
                    'path' => '/',
                    'httponly' => true,
                    'secure' => $isHttps,
                    'samesite' => 'Lax',
                ]
            );

            Response::success(
                message: MissatgesAPI::success('get'),
                data: [
                    'authenticated' => true,
                    'user' => $result['user'],
                ],
                httpCode: 200
            );
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            Response::error(
                message: MissatgesAPI::error($e->getMessage()),
                httpCode: 401
            );
        }
    }
}
