<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$tipo    = $input['tipo']    ?? $_POST['tipo']    ?? 'stock_bajo';
$mensaje = $input['mensaje'] ?? $_POST['mensaje'] ?? '';
$numero  = $input['numero']  ?? $_POST['numero']  ?? '';

if (!$mensaje) {
    echo json_encode(['ok' => false, 'msg' => 'El mensaje es obligatorio']);
    exit;
}

$db = getDB();

// Verificar si existe la tabla
$tablaExiste = $db->query("SHOW TABLES LIKE 'alertas_whatsapp'")->num_rows > 0;
if (!$tablaExiste) {
    $db->query("CREATE TABLE alertas_whatsapp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo VARCHAR(50) DEFAULT 'stock_bajo',
        mensaje TEXT NOT NULL,
        numero VARCHAR(20) DEFAULT NULL,
        leido TINYINT(1) DEFAULT 0,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

$stmt = $db->prepare("INSERT INTO alertas_whatsapp (tipo, mensaje, numero) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $tipo, $mensaje, $numero);

if ($stmt->execute()) {
    echo json_encode(['ok' => true, 'msg' => 'Alerta guardada correctamente', 'id' => $db->insert_id]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Error al guardar: ' . $db->error]);
}
$stmt->close();
