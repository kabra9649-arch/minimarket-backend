<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']); exit;
}

$input   = json_decode(file_get_contents('php://input'), true) ?? [];
$tipo    = $input['tipo']    ?? $_POST['tipo']    ?? 'stock_bajo';
$mensaje = $input['mensaje'] ?? $_POST['mensaje'] ?? '';

if (!$mensaje) {
    echo json_encode(['ok' => false, 'msg' => 'El mensaje es obligatorio']); exit;
}

// Validar enum
$tiposValidos = ['stock_bajo', 'vencimiento', 'sin_alerta'];
if (!in_array($tipo, $tiposValidos)) $tipo = 'stock_bajo';

$db = getDB();
$stmt = $db->prepare("INSERT INTO alertas_whatsapp (tipo, mensaje) VALUES (?, ?)");
if (!$stmt) {
    echo json_encode(['ok' => false, 'msg' => 'Error prepare: ' . $db->error]); exit;
}
$stmt->bind_param('ss', $tipo, $mensaje);
if ($stmt->execute()) {
    echo json_encode(['ok' => true, 'msg' => 'Alerta guardada', 'id' => $db->insert_id]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Error: ' . $db->error]);
}
$stmt->close();
