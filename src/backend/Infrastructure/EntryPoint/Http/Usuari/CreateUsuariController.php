<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuari;

use App\Application\Usuari\UseCase\CreateUsuariUseCase;
use App\Utils\MissatgesAPI;
use App\Utils\Response;

final class CreateUsuariController
{
    public function __construct(
        private CreateUsuariUseCase $createUsuariUseCase
    ) {}

    public function execute(
        string $email,
        string $password,
        string $nom,
        string $cognom,
        mixed $acceptaPrivacitat
    ): void {
        try {
            if ($acceptaPrivacitat !== true && $acceptaPrivacitat !== 1 && $acceptaPrivacitat !== '1') {
                Response::error(
                    message: MissatgesAPI::error('privacitat'),
                    httpCode: 400
                );

                return;
            }

            $usuari = $this->createUsuariUseCase->execute(
                email: $email,
                password: $password,
                nom: $nom,
                cognom: $cognom,
                acceptaPrivacitat: true
            );

            Response::success(
                message: MissatgesAPI::success('createUser'),
                data: [
                    'user' => [
                        'id' => $usuari->id()->toString(),
                        'email' => $usuari->email()->value(),
                        'nom' => $usuari->nom(),
                        'cognom' => $usuari->cognom(),
                        'role' => $usuari->role()->value,
                    ],
                ],
                httpCode: 201
            );
        } catch (\InvalidArgumentException $e) {
            $message = MissatgesAPI::error($e->getMessage());

            $errors = [];

            if ($e->getMessage() === 'email_duplicat') {
                $errors['email'] = [
                    'label' => 'Correu electrònic',
                    'messages' => [$message],
                ];
            }

            Response::error(
                message: $message,
                errors: $errors,
                httpCode: 400
            );
        } catch (\Throwable $e) {
            error_log($e->getMessage());

            Response::error(
                message: MissatgesAPI::error(
                    'No s\'ha pogut crear el compte.'
                ),
                httpCode: 500
            );
        }
    }
}
