<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$mensaje = trim($input['mensaje'] ?? '');
$contexto = $input['contexto'] ?? 'cliente';

if (!$mensaje) {
    echo json_encode(['error' => 'Mensaje vacío']);
    exit();
}

$db = getDB();

// ── API KEY GROQ ──
$apiKey = getenv('GROQ_API_KEY')
       ?: ($_ENV['GROQ_API_KEY'] ?? '')
       ?: ($_SERVER['GROQ_API_KEY'] ?? '');

if (!$apiKey) {
    echo json_encode(['respuesta' => 'ERROR: API Key vacía', 'ok' => false]);
    exit();
}

// ── DATOS DEL SISTEMA ──
$stockBajo = $db->query("SELECT COUNT(*) AS t FROM productos WHERE stock_actual <= stock_minimo AND activo=1")->fetch_assoc()['t'];
$ventasHoy = $db->query("SELECT COUNT(*) AS t, IFNULL(SUM(total),0) AS ing FROM ventas WHERE DATE(fecha)=CURDATE() AND estado='completada'")->fetch_assoc();
$pedidosPendientes = $db->query("SELECT COUNT(*) AS t FROM pedidos WHERE estado='pendiente'")->fetch_assoc()['t'];
$productosDisponibles = $db->query("SELECT COUNT(*) AS t FROM productos WHERE activo=1 AND stock_actual>0")->fetch_assoc()['t'];
$porVencer = $db->query("SELECT COUNT(*) AS t FROM productos WHERE fecha_vencimiento IS NOT NULL AND fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND fecha_vencimiento >= CURDATE() AND activo=1")->fetch_assoc()['t'];

$prods = $db->query("SELECT p.nombre, p.precio_venta, p.stock_actual, c.nombre AS categoria FROM productos p JOIN categorias c ON p.categoria_id=c.id WHERE p.activo=1 AND p.stock_actual>0 ORDER BY p.nombre LIMIT 30");
$listaProductos = [];
while ($r = $prods->fetch_assoc()) {
    $listaProductos[] = "{$r['nombre']} - RD\${$r['precio_venta']} ({$r['categoria']}, stock: {$r['stock_actual']})";
}

if ($contexto === 'cliente') {
    $systemPrompt = "Eres NEXSYS, el asistente virtual inteligente del sistema NEXSYS de gestión de MiniMarket. Eres amable, profesional y útil. Respondes en español de manera natural pero profesional.

INFORMACIÓN DEL NEGOCIO:
- Nombre: NEXSYS MiniMarket
- Servicio: Venta de productos con entrega a domicilio en Santo Domingo
- Horario: Lunes a Sábado 8am-8pm, Domingo 9am-5pm
- Costo de envío: RD\$150

PRODUCTOS DISPONIBLES HOY:
" . implode("\n", $listaProductos) . "

INSTRUCCIONES:
- Ayuda a los clientes a encontrar productos, precios y disponibilidad
- Si preguntan por un producto que no está en la lista, diles que no está disponible hoy
- Puedes recomendar productos similares
- Para pedidos, diles que usen el carrito de compras
- Sé conciso, máximo 3 oraciones por respuesta
- Nunca inventes precios ni información";
} else {
    $systemPrompt = "Eres NEXSYS AI, el asistente de gestión inteligente del sistema NEXSYS. Ayudas al administrador y empleados con análisis, reportes y recomendaciones.

DATOS EN TIEMPO REAL:
- Ventas hoy: {$ventasHoy['t']} ventas | Ingresos: RD\${$ventasHoy['ing']}
- Pedidos pendientes: {$pedidosPendientes}
- Productos con stock bajo: {$stockBajo}
- Productos por vencer (7 días): {$porVencer}
- Productos disponibles: {$productosDisponibles}

Responde de forma concisa usando los datos reales del sistema.";
}

// ── LLAMAR A GROQ API ──
$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'llama3-8b-8192',
        'max_tokens' => 300,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $mensaje]
        ]
    ]),
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

error_log("NEXSYS GROQ - HTTP: $httpCode - Response: $response");

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $respuesta = $data['choices'][0]['message']['content'] ?? 'Lo siento, no pude procesar tu consulta.';
    echo json_encode(['respuesta' => $respuesta, 'ok' => true]);
} else {
    $fallbacks = [
        'cliente' => 'Hola, soy NEXSYS. Para asistencia inmediata, puedes enviar un mensaje a través del formulario de contacto.',
        'admin' => 'NEXSYS está procesando tu consulta. Revisa los reportes del sistema para información detallada.'
    ];
    echo json_encode(['respuesta' => $fallbacks[$contexto], 'ok' => false, 'http' => $httpCode]);
}
