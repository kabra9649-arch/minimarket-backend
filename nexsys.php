<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

$input     = json_decode(file_get_contents('php://input'), true);
$msg       = trim($input['mensaje'] ?? '');
$ctx       = $input['contexto'] ?? 'admin';
$cliente_id = (int)($input['cliente_id'] ?? 0);

if (!$msg) { echo json_encode(['respuesta' => 'Por favor escribe tu mensaje.']); exit; }

$db = getDB();

// ── CONTEXTO CLIENTE — datos del pedido del cliente ────
if ($ctx === 'cliente' && $cliente_id > 0) {

    // Último pedido del cliente
    $ultimoPedido = $db->query("
        SELECT p.*, pd.direccion, pd.telefono, pd.repartidor, pd.costo_envio
        FROM pedidos p
        LEFT JOIN pedidos_domicilio pd ON pd.pedido_id = p.id
        WHERE p.cliente_id = $cliente_id
        ORDER BY p.fecha DESC LIMIT 1
    ")->fetch_assoc();

    // Todos los pedidos del cliente
    $todosPedidos = $db->query("
        SELECT num_pedido, estado, tipo, total, fecha
        FROM pedidos
        WHERE cliente_id = $cliente_id
        ORDER BY fecha DESC LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    // Detalle del último pedido
    $detallePedido = '';
    if ($ultimoPedido) {
        $pid = $ultimoPedido['id'];
        $detalles = $db->query("
            SELECT pr.nombre, dp.cantidad, dp.precio_unitario, dp.subtotal
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.producto_id = pr.id
            WHERE dp.pedido_id = $pid
        ")->fetch_all(MYSQLI_ASSOC);

        $itemsStr = implode(', ', array_map(fn($d) => "{$d['nombre']} x{$d['cantidad']}", $detalles));

        $estadoEmoji = match($ultimoPedido['estado']) {
            'pendiente'   => '⏳ Pendiente — tu pedido está siendo procesado.',
            'preparando'  => '👨‍🍳 En preparación — tu pedido se está preparando.',
            'listo'       => '✅ Listo — tu pedido está listo para entrega.',
            'en_camino'   => '🚴 En camino — tu pedido está siendo entregado.',
            'entregado'   => '✅ Entregado — tu pedido fue entregado con éxito.',
            'cancelado'   => '❌ Cancelado.',
            default       => ucfirst($ultimoPedido['estado'])
        };

        $detallePedido = "
ÚLTIMO PEDIDO DEL CLIENTE:
- Número: {$ultimoPedido['num_pedido']}
- Estado: $estadoEmoji
- Tipo: ".ucfirst($ultimoPedido['tipo'])."
- Total: RD$ ".number_format($ultimoPedido['total'],2)."
- Fecha: ".date('d/m/Y H:i', strtotime($ultimoPedido['fecha']))."
- Productos: $itemsStr
".($ultimoPedido['direccion'] ? "- Dirección de entrega: {$ultimoPedido['direccion']}" : "")."
".($ultimoPedido['repartidor'] ? "- Repartidor: {$ultimoPedido['repartidor']}" : "")."
";
    } else {
        $detallePedido = "El cliente no tiene pedidos registrados aún.";
    }

    // Historial
    $historialStr = '';
    foreach ($todosPedidos as $p) {
        $historialStr .= "- {$p['num_pedido']}: ".ucfirst($p['estado'])." — RD$ ".number_format($p['total'],2)." ({$p['tipo']}) — ".date('d/m/Y', strtotime($p['fecha']))."\n";
    }

    // Info productos disponibles
    $prods = $db->query("SELECT COUNT(*) as total FROM productos WHERE activo=1 AND stock_actual>0")->fetch_assoc();
    $cats  = $db->query("SELECT GROUP_CONCAT(nombre SEPARATOR ', ') as lista FROM categorias")->fetch_assoc();

    $systemPrompt = "Eres NEXSYS AI, el asistente virtual del colmado NEXSYS. Eres amable, directo y respondes en español. Tienes acceso a los datos reales del pedido de este cliente y puedes decirle exactamente dónde está su pedido, el estado, los productos que ordenó y más.

$detallePedido

HISTORIAL DE PEDIDOS (últimos 5):
$historialStr

CATÁLOGO: Tenemos {$prods['total']} productos disponibles en categorías: {$cats['lista']}.

INFORMACIÓN DEL COLMADO:
- Costo de envío: RD$ 150.00
- Tipos de pedido: mostrador y domicilio
- Métodos de pago: efectivo, tarjeta, transferencia

Cuando el cliente pregunte por su pedido, dile el estado exacto con el emoji correspondiente y todos los detalles disponibles. Sé amable y profesional. Máximo 3-4 oraciones.";

} else {
    // ── CONTEXTO ADMIN ──────────────────────────────────
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
- Esta semana: {$ventasSemana['cant']} ventas — RD\$ ".number_format($ventasSemana['total'],2)."
- Este mes: {$ventasMes['cant']} ventas — RD\$ ".number_format($ventasMes['total'],2)."
- Este año: {$ventasAnio['cant']} ventas — RD\$ ".number_format($ventasAnio['total'],2)."
- Productos más vendidos: ".implode(', ', $topNombres);

    $systemPrompt = "Eres NEXSYS AI, el asistente inteligente del sistema de gestión NEXSYS. Tienes acceso a datos reales del negocio y puedes responder cualquier pregunta sobre ventas, inventario, clientes, pedidos y operaciones. Responde en español de forma clara y profesional. $infoCompleta";
}

// ── GROQ ────────────────────────────────────────────────
$groqKey      = getenv('GROQ_API_KEY');
$anthropicKey = getenv('ANTHROPIC_API_KEY');
$respuesta    = null;

if ($groqKey) {
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer '.$groqKey],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'llama-3.1-8b-instant',
            'messages' => [['role'=>'system','content'=>$systemPrompt],['role'=>'user','content'=>$msg]],
            'max_tokens' => 350, 'temperature' => 0.7
        ])
    ]);
    $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code === 200) { $data = json_decode($res, true); $respuesta = $data['choices'][0]['message']['content'] ?? null; }
}

if (!$respuesta && $anthropicKey) {
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json','x-api-key: '.$anthropicKey,'anthropic-version: 2023-06-01'],
        CURLOPT_POSTFIELDS => json_encode(['model'=>'claude-haiku-4-5-20251001','max_tokens'=>350,'system'=>$systemPrompt,'messages'=>[['role'=>'user','content'=>$msg]]])
    ]);
    $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code === 200) { $data = json_decode($res, true); $respuesta = $data['content'][0]['text'] ?? null; }
}

// ── FALLBACK ─────────────────────────────────────────────
if (!$respuesta) {
    if ($ctx === 'cliente' && isset($ultimoPedido) && $ultimoPedido) {
        $estado = $ultimoPedido['estado'];
        $emojis = ['pendiente'=>'⏳','preparando'=>'👨‍🍳','listo'=>'✅','en_camino'=>'🚴','entregado'=>'✅','cancelado'=>'❌'];
        $respuesta = ($emojis[$estado] ?? '') . " Tu último pedido ({$ultimoPedido['num_pedido']}) está en estado: " . ucfirst($estado) . ". Total: RD$ " . number_format($ultimoPedido['total'],2) . ".";
    } else {
        $respuesta = '¡Hola! Soy NEXSYS AI. ¿En qué puedo ayudarte hoy?';
    }
}

echo json_encode(['respuesta' => $respuesta]);
