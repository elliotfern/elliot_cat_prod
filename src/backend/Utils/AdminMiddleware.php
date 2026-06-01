<?php

namespace App\Utils;

class AdminMiddleware
{
    public static function handle(): void
    {
        if (!isAuthenticatedAdmin()) {
            Response::error(
                MissatgesAPI::error('admin'),
                [],
                403
            );
            exit;
        }
    }
}
