<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuari;

use App\Application\Usuari\UseCase\MeUsuariUseCase;
use App\Utils\Response;

final class MeUsuariController
{
    public function __construct(
        private MeUsuariUseCase $useCase
    ) {}

    public function execute(): void
    {
        try {
            $result = $this->useCase->execute($_COOKIE['token'] ?? '');

            Response::success(
                message: 'Usuari autenticat correctament',
                data: $result
            );
        } catch (\RuntimeException $e) {

            Response::error(
                message: $e->getMessage(),
                httpCode: 401
            );
        }
    }
}
