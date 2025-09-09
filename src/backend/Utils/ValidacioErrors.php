<?php

namespace App\Utils;

class ValidacioErrors
{
    public static function requerit(string $camp): string
    {
        return "El camp <strong>$camp</strong> és obligatori.";
    }

    public static function invalid(string $camp): string
    {
        return "El camp <strong>$camp</strong> no és vàlid.";
    }

    public static function dataNoValida(string $camp): string
    {
        return "El camp <strong>$camp</strong> no és vàlid. Format esperat: dia/mes/any.";
    }

    public static function massaCurt(string $camp, int $min): string
    {
        return "El camp <strong>$camp</strong> ha de tenir almenys $min caràcters.";
    }

    public static function massaLlarg(string $camp, int $max): string
    {
        return "El camp <strong>$camp</strong> no pot superar els $max caràcters.";
    }

    // Pots afegir més funcions segons necessitis
}
