<?php
// Detectar si la conexion viene por HTTPS (Railway usa proxy)
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
         || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
         || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');

ini_set('session.cookie_secure', $isSecure ? 1 : 0);
ini_set('session.cookie_samesite', $isSecure ? 'None' : 'Lax');
ini_set('session.cookie_httponly', 1);
ini_set('session.gc_maxlifetime', 7200);

if (session_status() === PHP_SESSION_NONE) session_start();

// ── EMPLEADOS ──────────────────────────────────────────
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['rol'], (array)$roles)) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['toast'] = [
            'tipo'    => 'error',
            'mensaje' => 'Acceso restringido. No tienes los permisos necesarios para ingresar a este módulo.'
        ];
        header('Location: /ventas/index.php');
        exit();
    }
}

function currentUser() {
    return [
        'id'     => $_SESSION['usuario_id'] ?? null,
        'nombre' => $_SESSION['nombre']     ?? '',
        'rol'    => $_SESSION['rol']        ?? '',
        'email'  => $_SESSION['email']      ?? ''
    ];
}

// ── CLIENTES ───────────────────────────────────────────
function isClienteLoggedIn() {
    return isset($_SESSION['cliente_id']);
}

function requireCliente() {
    if (!isClienteLoggedIn()) {
        header('Location: /login_cliente.php');
        exit();
    }
}

function currentCliente() {
    return [
        'id'     => $_SESSION['cliente_id']     ?? null,
        'nombre' => $_SESSION['cliente_nombre'] ?? '',
        'email'  => $_SESSION['cliente_email']  ?? ''
    ];
}
