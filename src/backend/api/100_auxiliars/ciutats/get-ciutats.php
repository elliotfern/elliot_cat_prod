<?php

use App\Application\Ciutat\Presenter\CiutatResponse;
use App\Config\Database;
use App\Infrastructure\Persistence\Ciutat\MysqlCiutatRepository;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Validator;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = $db->getPdo();

// GET : llistat ciutats
// URL: api/ciutats/get/llistatCiutats
if ($slug === "llistatCiutats") {

    $ciutatRepository = new MysqlCiutatRepository($pdo);
    $ciutats = $ciutatRepository->getAll();

    $data = array_map(
        fn($ciutat) => CiutatResponse::toArray($ciutat),
        $ciutats
    );

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $data,
        httpCode: 200
    );

    // GET : Ciutat ID informació
    // URL: api/ciutats/get/ciutatId?id=33
} else if ($slug === "ciutatId") {

    $id = $_GET['id'] ?? null;

    $errors = [];

    Validator::requiredUuid($errors, 'id', $id);
    Validator::throwIfErrors($errors);

    $ciutatRepository = new MysqlCiutatRepository($pdo);
    $ciutat = $ciutatRepository->findById($id);

    Response::success(
        message: MissatgesAPI::success('get'),
        data: CiutatResponse::toArray($ciutat),
        httpCode: 200
    );
} else {
    //
}
