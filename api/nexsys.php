<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$msg   = trim($input['mensaje'] ?? '');
$ctx   = $input['contexto'] ?? 'admin';

if (!$msg) { echo json_encode(['respuesta' => 'Por favor escribe tu mensaje.']); exit; }

$db = getDB();

// ── DATOS COMPLETOS DEL NEGOCIO ─────────────────────────
$prods        = $db->query("SELECT COUNT(*) as total FROM productos WHERE activo=1 AND stock_actual>0")->fetch_assoc();
$stock_bajo   = $db->query("SELECT COUNT(*) as total FROM productos WHERE activo=1 AND stock_actual<=stock_minimo")->fetch_assoc();
$cats         = $db->query("SELECT GROUP_CONCAT(nombre SEPARATOR ', ') as lista FROM categorias")->fetch_assoc();
$ventasHoy    = $db->query("SELECT COUNT(*) as cant, IFNULL(SUM(total),0) as total FROM ventas WHERE DATE(fecha)=CURDATE() AND estado='completada'")->fetch_assoc();
$ventasSemana = $db->query("SELECT COUNT(*) as cant, IFNULL(SUM(total),0) as total FROM ventas WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND estado='completada'")->fetch_assoc();
$ventasMes    = $db->query("SELECT COUNT(*) as cant, IFNULL(SUM(total),0) as total FROM ventas WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE()) AND estado='completada'")->fetch_assoc();
$ventasAnio   = $db->query("SELECT COUNT(*) as cant, IFNULL(SUM(total),0) as total FROM ventas WHERE YEAR(fecha)=YEAR(CURDATE()) AND estado='completada'")->fetch_assoc();
$clientes     = $db->query("SELECT COUNT(*) as total FROM clientes WHERE activo=1")->fetch_assoc();
$pedidosPend  = $db->query("SELECT COUNT(*) as total FROM pedidos WHERE estado='pendiente'")->fetch_assoc();
$topProductos = $db->query("SELECT p.nombre, SUM(dv.cantidad) as vendidos FROM detalle_ventas dv JOIN productos p ON dv.producto_id=p.id GROUP BY dv.producto_id ORDER BY vendidos DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$topNombres   = array_column($topProductos, 'nombre');

$infoCompleta = "
DATOS ACTUALES DEL NEGOCIO (fecha: ".date('d/m/Y H:i')."):
- Productos activos con stock: {$prods['total']}
- Productos con stock bajo: {$stock_bajo['total']}
- Categorías: {$cats['lista']}
- Clientes registrados: {$clientes['total']}
- Pedidos pendientes: {$pedidosPend['total']}

VENTAS:
- Hoy: {$ventasHoy['cant']} ventas — RD\$ ".number_format($ventasHoy['total'],2)."
- Esta semana (últimos 7 días): {$ventasSemana['cant']} ventas — RD\$ ".number_format($ventasSemana['total'],2)."
- Este mes: {$ventasMes['cant']} ventas — RD\$ ".number_format($ventasMes['total'],2)."
- Este año: {$ventasAnio['cant']} ventas — RD\$ ".number_format($ventasAnio['total'],2)."
- Productos más vendidos: ".implode(', ', $topNombres)."
";

if ($ctx === 'admin') {
    $systemPrompt = "Eres NEXSYS AI, el asistente inteligente del sistema de gestión NEXSYS. Tienes acceso a datos reales del negocio y puedes responder cualquier pregunta sobre ventas, inventario, clientes, pedidos y operaciones. También puedes responder preguntas generales. Usa los datos reales cuando sean relevantes. Responde en español de forma clara y profesional. $infoCompleta";
} else {
    $systemPrompt = "Eres NEXSYS AI, el asistente virtual de NEXSYS. Ayudas a los clientes con preguntas sobre productos, pedidos, delivery y el catálogo. Datos: Tenemos {$prods['total']} productos disponibles en categorías: {$cats['lista']}. Responde en español de forma amable y breve.";
}

// ── GROQ ────────────────────────────────────────────────
$groqKey      = getenv('GROQ_API_KEY');
$anthropicKey = getenv('ANTHROPIC_API_KEY');
$respuesta    = null;

if ($groqKey) {
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer '.$groqKey],
        CURLOPT_POSTFIELDS     => json_encode([
            'model'       => 'llama-3.1-8b-instant',
            'messages'    => [['role'=>'system','content'=>$systemPrompt],['role'=>'user','content'=>$msg]],
            'max_tokens'  => 350,
            'temperature' => 0.7
        ])
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code === 200) {
        $data = json_decode($res, true);
        $respuesta = $data['choices'][0]['message']['content'] ?? null;
    }
}

// ── ANTHROPIC ────────────────────────────────────────────
if (!$respuesta && $anthropicKey) {
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json','x-api-key: '.$anthropicKey,'anthropic-version: 2023-06-01'],
        CURLOPT_POSTFIELDS     => json_encode([
            'model'    => 'claude-haiku-4-5-20251001',
            'max_tokens'=> 350,
            'system'   => $systemPrompt,
            'messages' => [['role'=>'user','content'=>$msg]]
        ])
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code === 200) {
        $data = json_decode($res, true);
        $respuesta = $data['content'][0]['text'] ?? null;
    }
}

// ── FALLBACK ─────────────────────────────────────────────
if (!$respuesta) {
    $fallbacks = [
        'semana'   => "Esta semana se han registrado {$ventasSemana['cant']} ventas por un total de RD\$ ".number_format($ventasSemana['total'],2).".",
        'mes'      => "Este mes se han registrado {$ventasMes['cant']} ventas por un total de RD\$ ".number_format($ventasMes['total'],2).".",
        'hoy'      => "Hoy se han registrado {$ventasHoy['cant']} ventas por un total de RD\$ ".number_format($ventasHoy['total'],2).".",
        'vendi'    => "Esta semana se han registrado {$ventasSemana['cant']} ventas por RD\$ ".number_format($ventasSemana['total'],2).". Este mes: RD\$ ".number_format($ventasMes['total'],2).".",
        'stock'    => "Hay {$stock_bajo['total']} productos con stock bajo que necesitan reabastecimiento.",
        'producto' => "Tenemos {$prods['total']} productos activos en categorías: {$cats['lista']}.",
        'cliente'  => "Hay {$clientes['total']} clientes registrados en el sistema.",
        'pedido'   => "Hay {$pedidosPend['total']} pedidos pendientes por atender.",
        'hola'     => '¡Hola! Soy NEXSYS AI. Puedo ayudarte con ventas, inventario, clientes y más. ¿Qué necesitas?',
    ];
    foreach ($fallbacks as $key => $resp) {
        if (stripos($msg, $key) !== false) { $respuesta = $resp; break; }
    }
    $respuesta = $respuesta ?? 'No pude conectarme al servicio de IA. Por favor intenta de nuevo.';
}

echo json_encode(['respuesta' => $respuesta]);
