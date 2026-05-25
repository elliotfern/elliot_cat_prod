<?php

namespace App\Infrastructure\Error;

use Throwable;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler(
            function (Throwable $e): void {

                error_log((string) $e);

                HttpErrorResponder::send($e);
            }
        );
    }
}
