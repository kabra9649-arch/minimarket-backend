<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

$input      = json_decode(file_get_contents('php://input'), true);
$msg        = trim($input['mensaje'] ?? '');
$ctx        = $input['contexto'] ?? 'admin';
$cliente_id = (int)($input['cliente_id'] ?? 0);

if (!$msg) { echo json_encode(['respuesta' => 'Por favor escribe tu mensaje.']); exit; }

$db = getDB();

// ════════════════════════════════════════════════════════
// CONTEXTO CLIENTE
// ════════════════════════════════════════════════════════
if ($ctx === 'cliente' && $cliente_id > 0) {

    $ultimoPedido = $db->query("
        SELECT p.*, pd.direccion, pd.telefono, pd.repartidor, pd.costo_envio
        FROM pedidos p
        LEFT JOIN pedidos_domicilio pd ON pd.pedido_id = p.id
        WHERE p.cliente_id = $cliente_id
        ORDER BY p.fecha DESC LIMIT 1
    ")->fetch_assoc();

    $todosPedidos = $db->query("
        SELECT num_pedido, estado, tipo, total, fecha
        FROM pedidos WHERE cliente_id = $cliente_id
        ORDER BY fecha DESC LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    $detallePedido = '';
    if ($ultimoPedido) {
        $pid = $ultimoPedido['id'];
        $detalles = $db->query("
            SELECT pr.nombre, dp.cantidad, dp.precio_unitario, dp.subtotal
            FROM detalle_pedidos dp JOIN productos pr ON dp.producto_id=pr.id
            WHERE dp.pedido_id=$pid
        ")->fetch_all(MYSQLI_ASSOC);
        $itemsStr = implode(', ', array_map(fn($d) => "{$d['nombre']} x{$d['cantidad']}", $detalles));
        $estadoEmoji = match($ultimoPedido['estado']) {
            'pendiente'  => '⏳ Pendiente — tu pedido está siendo procesado.',
            'preparando' => '👨‍🍳 En preparación — tu pedido se está preparando.',
            'listo'      => '✅ Listo — tu pedido está listo para entrega.',
            'en_camino'  => '🚴 En camino — tu pedido está siendo entregado.',
            'entregado'  => '✅ Entregado — tu pedido fue entregado con éxito.',
            'cancelado'  => '❌ Cancelado.',
            default      => ucfirst($ultimoPedido['estado'])
        };
        $detallePedido = "
ÚLTIMO PEDIDO:
- Número: {$ultimoPedido['num_pedido']}
- Estado: $estadoEmoji
- Tipo: ".ucfirst($ultimoPedido['tipo'])."
- Total: RD$ ".number_format($ultimoPedido['total'],2)."
- Fecha: ".date('d/m/Y H:i', strtotime($ultimoPedido['fecha']))."
- Productos: $itemsStr
".($ultimoPedido['direccion'] ? "- Dirección: {$ultimoPedido['direccion']}" : "")."
".($ultimoPedido['repartidor'] ? "- Repartidor: {$ultimoPedido['repartidor']}" : "");
    } else {
        $detallePedido = "El cliente no tiene pedidos registrados aún.";
    }

    $historialStr = '';
    foreach ($todosPedidos as $p) {
        $historialStr .= "- {$p['num_pedido']}: ".ucfirst($p['estado'])." — RD$ ".number_format($p['total'],2)." ({$p['tipo']}) — ".date('d/m/Y', strtotime($p['fecha']))."\n";
    }

    $prods = $db->query("SELECT COUNT(*) as total FROM productos WHERE activo=1 AND stock_actual>0")->fetch_assoc();
    $cats  = $db->query("SELECT GROUP_CONCAT(nombre SEPARATOR ', ') as lista FROM categorias")->fetch_assoc();

    $systemPrompt = "Eres NEXSYS AI, el asistente virtual del colmado NEXSYS. Eres amable, directo y respondes en español. Tienes acceso a los datos reales del pedido de este cliente.

$detallePedido

HISTORIAL (últimos 5):
$historialStr

CATÁLOGO: {$prods['total']} productos en categorías: {$cats['lista']}.
Costo de envío: RD\$ 150. Métodos de pago: efectivo, tarjeta, transferencia.

Cuando el cliente pregunte por su pedido dile el estado exacto con emoji y todos los detalles. Sé amable. Máximo 3-4 oraciones.";

} else {
// ════════════════════════════════════════════════════════
// CONTEXTO ADMIN — DATOS COMPLETOS DEL SISTEMA
// ════════════════════════════════════════════════════════

    // ── VENTAS ──────────────────────────────────────────
    $vHoy    = $db->query("SELECT COUNT(*) c, IFNULL(SUM(total),0) t, IFNULL(AVG(total),0) avg FROM ventas WHERE DATE(fecha)=CURDATE() AND estado='completada'")->fetch_assoc();
    $vAyer   = $db->query("SELECT COUNT(*) c, IFNULL(SUM(total),0) t FROM ventas WHERE DATE(fecha)=DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND estado='completada'")->fetch_assoc();
    $vSemana = $db->query("SELECT COUNT(*) c, IFNULL(SUM(total),0) t FROM ventas WHERE fecha>=DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND estado='completada'")->fetch_assoc();
    $vMes    = $db->query("SELECT COUNT(*) c, IFNULL(SUM(total),0) t FROM ventas WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE()) AND estado='completada'")->fetch_assoc();
    $vAnio   = $db->query("SELECT COUNT(*) c, IFNULL(SUM(total),0) t FROM ventas WHERE YEAR(fecha)=YEAR(CURDATE()) AND estado='completada'")->fetch_assoc();

    // Mejor día de la semana
    $mejorDia = $db->query("SELECT DAYNAME(fecha) dia, SUM(total) t FROM ventas WHERE estado='completada' AND fecha>=DATE_SUB(CURDATE(),INTERVAL 30 DAY) GROUP BY DAYNAME(fecha) ORDER BY t DESC LIMIT 1")->fetch_assoc();

    // Método de pago más usado
    $metodoPago = $db->query("SELECT metodo_pago, COUNT(*) c FROM ventas WHERE estado='completada' GROUP BY metodo_pago ORDER BY c DESC LIMIT 1")->fetch_assoc();

    // Última venta
    $ultimaVenta = $db->query("SELECT num_factura, total, metodo_pago, fecha FROM ventas WHERE estado='completada' ORDER BY fecha DESC LIMIT 1")->fetch_assoc();

    // ── PRODUCTOS ────────────────────────────────────────
    $totalProds   = $db->query("SELECT COUNT(*) t FROM productos WHERE activo=1")->fetch_assoc();
    $prodsConStock= $db->query("SELECT COUNT(*) t FROM productos WHERE activo=1 AND stock_actual>0")->fetch_assoc();
    $stockBajo    = $db->query("SELECT COUNT(*) t FROM productos WHERE activo=1 AND stock_actual<=stock_minimo AND stock_actual>0")->fetch_assoc();
    $sinStock     = $db->query("SELECT COUNT(*) t FROM productos WHERE activo=1 AND stock_actual=0")->fetch_assoc();
    $porVencer    = $db->query("SELECT COUNT(*) t FROM productos WHERE activo=1 AND fecha_vencimiento<=DATE_ADD(CURDATE(),INTERVAL 7 DAY) AND fecha_vencimiento>=CURDATE()")->fetch_assoc();
    $valorInv     = $db->query("SELECT IFNULL(SUM(stock_actual*precio_venta),0) t FROM productos WHERE activo=1")->fetch_assoc();
    $valorCosto   = $db->query("SELECT IFNULL(SUM(stock_actual*precio_compra),0) t FROM productos WHERE activo=1")->fetch_assoc();

    // Productos más vendidos
    $topVendidos = $db->query("SELECT p.nombre, SUM(dv.cantidad) vendidos, SUM(dv.subtotal) ingresos FROM detalle_ventas dv JOIN productos p ON dv.producto_id=p.id GROUP BY p.id ORDER BY vendidos DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
    $topStr = implode(', ', array_map(fn($p) => "{$p['nombre']} ({$p['vendidos']} uds — RD\$ ".number_format($p['ingresos'],0).")", $topVendidos));

    // Productos con stock bajo (lista)
    $stockBajoLista = $db->query("SELECT nombre, stock_actual, stock_minimo FROM productos WHERE activo=1 AND stock_actual<=stock_minimo ORDER BY stock_actual ASC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
    $stockBajoStr = implode(', ', array_map(fn($p) => "{$p['nombre']} (stock: {$p['stock_actual']}, mín: {$p['stock_minimo']})", $stockBajoLista));

    // ── CLIENTES ─────────────────────────────────────────
    $totalClientes  = $db->query("SELECT COUNT(*) t FROM clientes WHERE activo=1")->fetch_assoc();
    $clienteNuevoMes= $db->query("SELECT COUNT(*) t FROM clientes WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch_assoc();
    $topCliente     = $db->query("SELECT c.nombre, COUNT(v.id) compras, SUM(v.total) gastado FROM ventas v JOIN clientes c ON v.cliente_id=c.id WHERE v.estado='completada' GROUP BY c.id ORDER BY gastado DESC LIMIT 1")->fetch_assoc();

    // ── PEDIDOS ──────────────────────────────────────────
    $pedHoy      = $db->query("SELECT COUNT(*) t FROM pedidos WHERE DATE(fecha)=CURDATE()")->fetch_assoc();
    $pedPend     = $db->query("SELECT COUNT(*) t FROM pedidos WHERE estado='pendiente'")->fetch_assoc();
    $pedDomPend  = $db->query("SELECT COUNT(*) t FROM pedidos WHERE tipo='domicilio' AND estado NOT IN ('entregado','cancelado')")->fetch_assoc();
    $pedMes      = $db->query("SELECT COUNT(*) t, IFNULL(SUM(total),0) total FROM pedidos WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE())")->fetch_assoc();
    $pedEntregados= $db->query("SELECT COUNT(*) t FROM pedidos WHERE estado='entregado'")->fetch_assoc();

    // ── INVENTARIO / COMPRAS ─────────────────────────────
    $comprasMes  = $db->query("SELECT COUNT(*) t, IFNULL(SUM(total),0) total FROM compras WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE())")->fetch_assoc();
    $cats        = $db->query("SELECT nombre, (SELECT COUNT(*) FROM productos WHERE categoria_id=categorias.id AND activo=1) prods FROM categorias ORDER BY prods DESC")->fetch_all(MYSQLI_ASSOC);
    $catsStr     = implode(', ', array_map(fn($c) => "{$c['nombre']} ({$c['prods']} productos)", $cats));

    // ── EMPLEADOS ────────────────────────────────────────
    $empleados   = $db->query("SELECT COUNT(*) t FROM empleados WHERE activo=1")->fetch_assoc();
    $usuarios    = $db->query("SELECT COUNT(*) t FROM usuarios WHERE activo=1")->fetch_assoc();

    // ── RESUMEN FINANCIERO ───────────────────────────────
    $gananciaHoy  = $db->query("SELECT IFNULL(SUM(v.total - (SELECT IFNULL(SUM(dp.cantidad*p.precio_compra) FROM detalle_ventas dp JOIN productos p ON dp.producto_id=p.id WHERE dp.venta_id=v.id),0)),0) g FROM ventas v WHERE DATE(v.fecha)=CURDATE() AND v.estado='completada'")->fetch_assoc();

    $systemPrompt = "Eres NEXSYS AI, el asistente inteligente del sistema de gestión NEXSYS. Tienes acceso COMPLETO a todos los datos reales del negocio en tiempo real. Responde en español, de forma clara, directa y profesional. Si te preguntan datos específicos, dálos con números exactos. Puedes responder cualquier pregunta sobre el negocio o general.

════════════════════════════════════
DATOS DEL SISTEMA — ".date('d/m/Y H:i')."
════════════════════════════════════

📊 VENTAS:
• Hoy: {$vHoy['c']} ventas — RD\$ ".number_format($vHoy['t'],2)." (ticket promedio: RD\$ ".number_format($vHoy['avg'],2).")
• Ayer: {$vAyer['c']} ventas — RD\$ ".number_format($vAyer['t'],2)."
• Esta semana: {$vSemana['c']} ventas — RD\$ ".number_format($vSemana['t'],2)."
• Este mes: {$vMes['c']} ventas — RD\$ ".number_format($vMes['t'],2)."
• Este año: {$vAnio['c']} ventas — RD\$ ".number_format($vAnio['t'],2)."
• Método de pago más usado: ".($metodoPago['metodo_pago'] ?? 'N/A')."
• Mejor día (últimos 30 días): ".($mejorDia['dia'] ?? 'N/A')." (RD\$ ".number_format($mejorDia['t'] ?? 0,2).")
• Última venta: ".($ultimaVenta ? "{$ultimaVenta['num_factura']} — RD\$ ".number_format($ultimaVenta['total'],2)." — ".date('d/m/Y H:i',strtotime($ultimaVenta['fecha'])) : "ninguna")."

📦 INVENTARIO:
• Total productos activos: {$totalProds['t']}
• Productos con stock disponible: {$prodsConStock['t']}
• Sin stock: {$sinStock['t']}
• Con stock bajo: {$stockBajo['t']}
• Por vencer (7 días): {$porVencer['t']}
• Valor inventario (precio venta): RD\$ ".number_format($valorInv['t'],2)."
• Valor inventario (precio costo): RD\$ ".number_format($valorCosto['t'],2)."
• Categorías: $catsStr
• Top 5 más vendidos: $topStr
".($stockBajoStr ? "• Productos stock bajo: $stockBajoStr" : "")."

👥 CLIENTES:
• Total clientes activos: {$totalClientes['t']}
• Nuevos este mes: {$clienteNuevoMes['t']}
• Mejor cliente: ".($topCliente ? "{$topCliente['nombre']} ({$topCliente['compras']} compras — RD\$ ".number_format($topCliente['gastado'],2).")" : "N/A")."

🛵 PEDIDOS:
• Pedidos hoy: {$pedHoy['t']}
• Pendientes: {$pedPend['t']}
• Domicilios activos (sin entregar): {$pedDomPend['t']}
• Este mes: {$pedMes['t']} pedidos — RD\$ ".number_format($pedMes['total'],2)."
• Total entregados: {$pedEntregados['t']}

🛒 COMPRAS:
• Compras este mes: {$comprasMes['t']} — RD\$ ".number_format($comprasMes['total'],2)."

👨‍💼 PERSONAL:
• Empleados activos: {$empleados['t']}
• Usuarios del sistema: {$usuarios['t']}

════════════════════════════════════
Cuando respondas preguntas de datos, usa los números exactos de arriba. Si preguntan algo que no está en los datos, responde con tu conocimiento general. Sé conciso pero completo.";
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
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer '.$groqKey],
        CURLOPT_POSTFIELDS     => json_encode([
            'model'       => 'llama-3.1-8b-instant',
            'messages'    => [['role'=>'system','content'=>$systemPrompt],['role'=>'user','content'=>$msg]],
            'max_tokens'  => 500,
            'temperature' => 0.5
        ])
    ]);
    $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code === 200) { $data = json_decode($res, true); $respuesta = $data['choices'][0]['message']['content'] ?? null; }
}

if (!$respuesta && $anthropicKey) {
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json','x-api-key: '.$anthropicKey,'anthropic-version: 2023-06-01'],
        CURLOPT_POSTFIELDS     => json_encode([
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 500,
            'system'     => $systemPrompt,
            'messages'   => [['role'=>'user','content'=>$msg]]
        ])
    ]);
    $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code === 200) { $data = json_decode($res, true); $respuesta = $data['content'][0]['text'] ?? null; }
}

// ── FALLBACK ─────────────────────────────────────────────
if (!$respuesta) {
    if ($ctx === 'cliente' && isset($ultimoPedido) && $ultimoPedido) {
        $estado = $ultimoPedido['estado'];
        $emojis = ['pendiente'=>'⏳','preparando'=>'👨‍🍳','listo'=>'✅','en_camino'=>'🚴','entregado'=>'✅','cancelado'=>'❌'];
        $respuesta = ($emojis[$estado]??'')." Tu pedido {$ultimoPedido['num_pedido']} está ".ucfirst($estado).". Total: RD$ ".number_format($ultimoPedido['total'],2).".";
    } else {
        // Fallback con datos reales
        $fallbacks = [
            'vend'    => "Hoy: {$vHoy['c']} ventas por RD\$ ".number_format($vHoy['t'],2).". Esta semana: RD\$ ".number_format($vSemana['t'],2).". Este mes: RD\$ ".number_format($vMes['t'],2).".",
            'stock'   => "Hay {$stockBajo['t']} productos con stock bajo. Sin stock: {$sinStock['t']}. Por vencer: {$porVencer['t']}.",
            'cliente' => "Tienes {$totalClientes['t']} clientes activos. {$clienteNuevoMes['t']} nuevos este mes.",
            'pedido'  => "Pedidos hoy: {$pedHoy['t']}. Pendientes: {$pedPend['t']}. Domicilios activos: {$pedDomPend['t']}.",
            'inventar'=> "Valor del inventario: RD\$ ".number_format($valorInv['t'],2)." (venta) / RD\$ ".number_format($valorCosto['t'],2)." (costo).",
            'hola'    => '¡Hola! Soy NEXSYS AI. Puedo decirte ventas, stock, clientes, pedidos y más. ¿Qué necesitas?',
        ];
        $respuesta = '¡Hola! Soy NEXSYS AI. ¿En qué te puedo ayudar?';
        foreach ($fallbacks as $key => $resp) {
            if (stripos($msg, $key) !== false) { $respuesta = $resp; break; }
        }
    }
}

echo json_encode(['respuesta' => $respuesta]);
