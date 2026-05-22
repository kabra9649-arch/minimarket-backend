<?php
function getDB() {
    $host = getenv('DB_HOST') ?: 'mysql.railway.internal';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: 'wMrTHmynRYxmWmnFuxndQpOQKSEQdMOc';
    $db   = getenv('DB_NAME') ?: 'railway';
    $port = (int)(getenv('DB_PORT') ?: 3306);

    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = new mysqli();
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    $conn->real_connect($host, $user, $pass, $db, $port);

    if ($conn->connect_errno) {
        http_response_code(500);
        die(json_encode([
            'error' => 'No se pudo conectar a la base de datos.',
            'detalle' => $conn->connect_error
        ]));
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
