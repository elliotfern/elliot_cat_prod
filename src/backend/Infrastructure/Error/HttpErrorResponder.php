<?php

namespace App\Infrastructure\Error;

use Throwable;
use App\Utils\Response;

use App\Domain\Shared\Exception\NotFoundException;
use App\Domain\Shared\Exception\ValidationException;
use App\Domain\Shared\Exception\UnauthorizedException;
use App\Domain\Shared\Exception\ForbiddenException;

class HttpErrorResponder
{
    public static function send(Throwable $e): void
    {
        match (true) {

            $e instanceof ValidationException =>
            Response::error(
                message: $e->getMessage(),
                errors: [],
                httpCode: 400
            ),

            $e instanceof UnauthorizedException =>
            Response::error(
                message: $e->getMessage(),
                errors: [],
                httpCode: 401
            ),

            $e instanceof ForbiddenException =>
            Response::error(
                message: $e->getMessage(),
                errors: [],
                httpCode: 403
            ),

            $e instanceof NotFoundException =>
            Response::error(
                message: $e->getMessage(),
                errors: [],
                httpCode: 404
            ),

            default =>
            Response::error(
                message: 'Error intern del servidor',
                errors: [],
                httpCode: 500
            )
        };
    }
}
