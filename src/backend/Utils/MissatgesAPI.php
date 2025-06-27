<?php

namespace App\Utils;

class MissatgesAPI
{
    public static function success($key): string
    {
        $messages = [
            'get' => "Obtenció de dades correctament.",
            'create' => "Registre creat correctament.",
            'update' => "Registre actualitzat correctament.",
            'delete' => "Registre eliminat amb èxit.",
            'default' => "Operació realitzada correctament.",
            'loginOk' => "Has iniciat sessió correctament. Redirigint...",
        ];
        return $messages[$key] ?? $messages['default'];
    }

    public static function error($key): string
    {
        $messages = [
            'not_found' => "No s'ha trobat el registre sol·licitat.",
            'validacio' => "Hi ha errors de validació en les dades.",
            'save' => "No s'ha pogut desar el registre.",
            'delete' => "Error en intentar eliminar el registre.",
            'default' => "S'ha produït un error en el procés.",
            'errorBD' => "S'ha produït un error a la base de dades.",
            'errorEndPoint' => "Aquesta operació no és vàlida.",
            'duplicat' => "Ja existeix un valor a la base de dades amb el mateix nom.",
        ];
        return $messages[$key] ?? $messages['default'];
    }
}
