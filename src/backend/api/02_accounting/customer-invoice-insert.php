<?php
// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");
header("Access-Control-Allow-Methods: POST");

// insert data to db

// Dominio permitido (modifica con tu dominio)
$allowed_origin = "https://elliot.cat";

// Verificar el encabezado 'Origin'
if (isset($_SERVER['HTTP_ORIGIN'])) {
  if ($_SERVER['HTTP_ORIGIN'] !== $allowed_origin) {
    http_response_code(403); // Respuesta 403 Forbidden
    echo json_encode(["error" => "Acceso denegado. Origen no permitido."]);
    exit;
  }
}

// Verificar que el método HTTP sea PUT
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405); // Método no permitido
  echo json_encode(["error" => "Método no permitido. Se requiere POST."]);
  exit;
}

$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

// Inicializar un array para los errores
$errors = [];


if (empty($data["idUser"])) {
  $hasError = true;
} else {
  $idUser = data_input($data['idUser']);
}

if (empty($data["facConcepte"])) {
  $hasError = true;
} else {
  $facConcepte = data_input($data['facConcepte']);
}

if (empty($data["facData"])) {
  $hasError = true;
} else {
  $facData = data_input($data['facData']);
}

if (empty($data["facDueDate"])) {
  $hasError = true;
} else {
  $facDueDate = data_input($data['facDueDate']);
}

if (empty($data["facSubtotal"])) {
  $hasError = true;
} else {
  $facSubtotal = data_input($data['facSubtotal']);
}

if (empty($data["facFees"])) {
  $hasError = true;
} else {
  $facFees = data_input($data['facFees']);
}

if (empty($data["facTotal"])) {
  $hasError = true;
} else {
  $facTotal = data_input($data['facTotal']);
}

if (empty($data["facVAT"])) {
  $hasError = true;
} else {
  $facVAT = data_input($data['facVAT']);
}

if (empty($data["facIva"])) {
  $hasError = true;
} else {
  $facIva = data_input($data['facIva']);
}

if (empty($data["facEstat"])) {
  $hasError = true;
} else {
  $facEstat = data_input($data['facEstat']);
}

if (empty($data["facPaymentType"])) {
  $hasError = true;
} else {
  $facPaymentType = data_input($data['facPaymentType']);
}

if (!isset($hasError)) {
  $sql = "INSERT INTO db_accounting_soletrade_invoices_customers SET idUser=:idUser, facConcepte=:facConcepte, facData=:facData, facDueDate=:facDueDate, facSubtotal=:facSubtotal, facFees=:facFees, facTotal=:facTotal, facVAT=:facVAT, facIva=:facIva, facEstat=:facEstat, facPaymentType=:facPaymentType";


  global $conn;
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":idUser", $idUser, PDO::PARAM_INT);
  $stmt->bindParam(":facConcepte", $facConcepte, PDO::PARAM_STR);
  $stmt->bindParam(":facData", $facData, PDO::PARAM_STR);
  $stmt->bindParam(":facDueDate", $facDueDate, PDO::PARAM_STR);
  $stmt->bindParam(":facSubtotal", $facSubtotal, PDO::PARAM_STR);
  $stmt->bindParam(":facFees", $facFees, PDO::PARAM_STR);
  $stmt->bindParam(":facTotal", $facTotal, PDO::PARAM_STR);
  $stmt->bindParam(":facVAT", $facVAT, PDO::PARAM_STR);
  $stmt->bindParam(":facIva", $facIva, PDO::PARAM_INT);
  $stmt->bindParam(":facEstat", $facEstat, PDO::PARAM_INT);
  $stmt->bindParam(":facPaymentType", $facPaymentType, PDO::PARAM_INT);
  $stmt->execute();

  // response output      
  $response = array(
    'status' => 'success',
  );
  header("Content-Type: application/json");
  echo json_encode($response);
} else {
  // response output - data error
  $response = array(
    'status' => 'error',
  );
  echo json_encode($response);
}
