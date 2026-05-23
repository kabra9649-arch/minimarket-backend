<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$input  = json_decode(file_get_contents('php://input'), true);
$msg    = trim($input['mensaje'] ?? '');
$ctx    = $input['contexto'] ?? 'admin';

if (!$msg) {
    echo json_encode(['respuesta' => 'Por favor escribe tu mensaje.']);
    exit;
}

// Contexto del sistema según quién pregunta
$db = getDB();
$infoTienda = '';

// Obtener info básica de la tienda
$prods = $db->query("SELECT COUNT(*) as total FROM productos WHERE activo=1 AND stock_actual>0")->fetch_assoc();
$cats  = $db->query("SELECT GROUP_CONCAT(nombre SEPARATOR ', ') as lista FROM categorias")->fetch_assoc();
$ventasHoy = $db->query("SELECT IFNULL(SUM(total),0) as total FROM ventas WHERE DATE(fecha)=CURDATE() AND estado='completada'")->fetch_assoc();

$infoTienda = "Productos disponibles: {$prods['total']}. Categorías: {$cats['lista']}. Ventas de hoy: RD\$ {$ventasHoy['total']}.";

if ($ctx === 'admin') {
    $systemPrompt = "Eres NEXSYS AI, el asistente inteligente del sistema de gestión MiniMarket G2. Ayudas a los empleados y administradores con preguntas sobre el sistema, inventario, ventas, clientes y operaciones del negocio. Datos actuales: {$infoTienda} Responde en español, de forma concisa y profesional. Máximo 3 oraciones.";
} else {
    $systemPrompt = "Eres NEXSYS AI, el asistente virtual de MiniMarket G2. Ayudas a los clientes con preguntas sobre productos, pedidos, delivery y el catálogo. Datos: {$infoTienda} Responde en español, de forma amable y breve. Máximo 3 oraciones.";
}

// Intentar con Groq primero (es gratis)
$groqKey = getenv('GROQ_API_KEY');
$anthropicKey = getenv('ANTHROPIC_API_KEY');

$respuesta = null;

// Intentar Groq
if ($groqKey) {
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $groqKey
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model'    => 'llama-3.1-8b-instant',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $msg]
            ],
            'max_tokens'  => 200,
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

// Si Groq falla, intentar Anthropic
if (!$respuesta && $anthropicKey) {
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $anthropicKey,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 200,
            'system'     => $systemPrompt,
            'messages'   => [['role' => 'user', 'content' => $msg]]
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

// Respuesta de fallback si ambas APIs fallan
if (!$respuesta) {
    $fallbacks = [
        'hola'      => '¡Hola! Soy NEXSYS AI, tu asistente de MiniMarket G2. ¿En qué puedo ayudarte hoy?',
        'delivery'  => 'Sí, hacemos delivery a domicilio. Puedes hacer tu pedido desde el catálogo y lo recibirás en tu puerta.',
        'horario'   => 'Nuestro horario de atención es de lunes a sábado de 8am a 8pm y domingos de 9am a 5pm.',
        'producto'  => "Tenemos {$prods['total']} productos disponibles en categorías: {$cats['lista']}.",
        'precio'    => 'Puedes ver todos los precios actualizados en nuestro catálogo de productos.',
        'pago'      => 'Aceptamos efectivo, tarjeta de crédito/débito y transferencia bancaria.',
    ];
    foreach ($fallbacks as $key => $resp) {
        if (stripos($msg, $key) !== false) {
            $respuesta = $resp;
            break;
        }
    }
    $respuesta = $respuesta ?? 'Gracias por tu mensaje. En este momento no puedo conectarme al servicio de IA, pero un agente de MiniMarket G2 te atenderá pronto.';
}

echo json_encode(['respuesta' => $respuesta]);
