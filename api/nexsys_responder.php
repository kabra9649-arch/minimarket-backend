<?php
// ═══════════════════════════════════════════════════════
//  NEXSYS AI — Auto-respuesta a mensajes de clientes
//  Llamar después de insertar un mensaje nuevo
// ═══════════════════════════════════════════════════════
require_once '../config/database.php';

function nexsysAutoResponder($db, $mensaje_id, $nombre_cliente, $mensaje_texto, $correo_cliente = null) {
    $systemPrompt = "Eres NEXSYS, el asistente virtual de MiniMarket G2. Escribe una respuesta automática profesional, amable y en español para un cliente que envió un mensaje de contacto. La respuesta debe:
- Agradecer al cliente por contactarnos
- Confirmar que recibimos su mensaje
- Mencionar que un agente le responderá en menos de 24 horas
- Ser breve (máximo 3 oraciones)
- Firmar como NEXSYS AI — MiniMarket G2
No uses markdown, solo texto plano.";

    $userMsg = "El cliente {$nombre_cliente} envió: \"{$mensaje_texto}\"";

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . (getenv('ANTHROPIC_API_KEY') ?: ''),
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 200,
            'system' => $systemPrompt,
            'messages' => [['role' => 'user', 'content' => $userMsg]]
        ]),
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $respuesta = "Hola {$nombre_cliente}, gracias por contactarnos. Hemos recibido tu mensaje y un agente de MiniMarket G2 te responderá en menos de 24 horas. — NEXSYS AI · MiniMarket G2";

    if ($code === 200) {
        $data = json_decode($response, true);
        $respuesta = $data['content'][0]['text'] ?? $respuesta;
    }

    // Guardar la auto-respuesta como mensaje del sistema
    $stmt = $db->prepare("INSERT INTO mensajes (nombre, correo, asunto, mensaje, leido) VALUES ('NEXSYS AI', 'nexsys@minimarket.g2', 'Respuesta automática', ?, 0)");
    $autoMsg = "AUTO-RESPUESTA para {$nombre_cliente}:\n\n{$respuesta}";
    $stmt->bind_param('s', $autoMsg);
    $stmt->execute();
    $stmt->close();

    return $respuesta;
}
