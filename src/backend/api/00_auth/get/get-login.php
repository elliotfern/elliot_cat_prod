<?php

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");
header("Access-Control-Allow-Methods: POST");

// Verificar si se ha recibido un parámetro válido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


        // Llamar al método getPasswords con el ID dinámico
        $passwords = $passwordController->loginAuth($email, $password);

        // Verificar que hemos obtenido un array de datos
        if (is_array($passwords)) {
                // Devolver los datos como un array JSON
                echo json_encode($passwords, JSON_PRETTY_PRINT);
        } else {
                // Si no se ha obtenido un array, devolver un error en formato JSON
                echo json_encode(["error" => "No se encontraron contraseñas"]);
        }
}
