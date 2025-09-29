<?php

namespace App\Utils;

final class ValidacioErrors
{
    public static function requerit(string $camp): string
    {
        return "El camp <strong>$camp</strong> és obligatori.";
    }

    public static function invalid(string $camp): string
    {
        return "El camp <strong>$camp</strong> no és vàlid.";
    }

    /**
     * Alias més específic per indicar “format” incorrecte.
     * Útil per a camps com preu, email, etc.
     */
    public static function formatNoValid(string $camp): string
    {
        return "El camp <strong>$camp</strong> no té un format vàlid.";
    }

    /**
     * Data en format dd/mm/aaaa (mantinc el teu missatge original).
     */
    public static function dataNoValida(string $camp): string
    {
        return "El camp <strong>$camp</strong> no és vàlid. Format esperat: dia/mes/any.";
    }

    /**
     * Data en format ISO yyyy-mm-dd (el que fem servir als endpoints).
     */
    public static function dataIsoNoValida(string $camp): string
    {
        return "El camp <strong>$camp</strong> no és vàlid. Format esperat: YYYY-MM-DD.";
    }

    public static function massaCurt(string $camp, int $min): string
    {
        return "El camp <strong>$camp</strong> ha de tenir almenys $min caràcters.";
    }

    public static function massaLlarg(string $camp, int $max): string
    {
        return "El camp <strong>$camp</strong> no pot superar els $max caràcters.";
    }

    /**
     * Fora d’un rang numèric o de longitud.
     */
    public static function foraDeRang(string $camp, int|float $min, int|float $max): string
    {
        return "El camp <strong>$camp</strong> ha d’estar entre $min i $max.";
    }

    /**
     * Per quan el valor no es troba a un conjunt permès (enums, catàlegs, etc.).
     */
    public static function valorNoPermes(string $camp): string
    {
        return "El valor del camp <strong>$camp</strong> no està permès.";
    }
}
