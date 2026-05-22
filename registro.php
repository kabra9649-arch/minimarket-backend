<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (isClienteLoggedIn()) {
    header('Location: catalogo.php');
    exit();
}
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password']          ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    if (!$nombre || !$email || !$password) {
        $error = 'Nombre, correo y contraseña son obligatorios.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM clientes WHERE email=?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Ya existe una cuenta con ese correo. ¿Ya tienes cuenta?';
        } else {
            $hash      = password_hash($password, PASSWORD_DEFAULT);
            $nombreFull = $apellido ? "$nombre $apellido" : $nombre;
            $stmt = $db->prepare("INSERT INTO clientes (nombre,email,telefono,password,activo) VALUES (?,?,?,?,1)");
            $stmt->bind_param('ssss', $nombreFull, $email, $telefono, $hash);
            if ($stmt->execute()) {
                $success = '¡Cuenta creada con éxito! Ya puedes iniciar sesión.';
            } else {
                $error = 'Error al crear la cuenta: ' . $db->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — NEXSYS</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --rose:#C9536A; --cream:#FDF6EC; --brown:#3D1C02; }
        *,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:'DM Sans',sans-serif;
            background:var(--cream);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:40px 20px;
        }
        .register-card {
            width:100%; max-width:520px;
            background:#fff;
            border-radius:28px;
            padding:48px 44px;
            box-shadow:0 20px 60px rgba(61,28,2,0.08);
            animation:fadeUp .5s ease both;
        }
        @keyframes fadeUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }
        .brand { font-family:'Cormorant Garamond',serif; font-size:1.8rem; font-weight:700; color:var(--brown); text-align:center; margin-bottom:4px; }
        .brand span { color:var(--rose); }
        .brand-sub { text-align:center; color:#aaa; font-size:.82rem; margin-bottom:32px; }
        .section-title { font-family:'Cormorant Garamond',serif; font-size:1.6rem; font-weight:700; color:var(--brown); margin-bottom:6px; }
        .section-sub { color:#999; font-size:.85rem; margin-bottom:26px; }
        .row-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .field-label { display:block; color:#999; font-size:.7rem; letter-spacing:2px; text-transform:uppercase; margin-bottom:7px; }
        .field-wrap { position:relative; margin-bottom:16px; }
        .field-wrap i.icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#ccc; font-size:.95rem; pointer-events:none; }
        .field-wrap input {
            width:100%; background:#FAFAFA; border:1.5px solid #EDE5DA; color:var(--brown);
            padding:12px 14px 12px 40px; border-radius:11px;
            font-family:'DM Sans',sans-serif; font-size:.88rem; outline:none;
            transition:border-color .25s, background .25s;
        }
        .field-wrap input:focus { border-color:var(--rose); background:#fff; box-shadow:0 0 0 3px rgba(201,83,106,.1); }
        .field-wrap input::placeholder { color:#ccc; }
        .toggle-pass { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#ccc; cursor:pointer; font-size:.95rem; transition:color .2s; }
        .toggle-pass:hover { color:#888; }
        .password-strength { height:4px; border-radius:4px; background:#EEE; margin-top:6px; overflow:hidden; }
        .strength-bar { height:100%; border-radius:4px; width:0%; transition:width .4s,background .4s; }
        .btn-register {
            width:100%; background:var(--rose); color:#fff; border:none;
            padding:15px; border-radius:12px; font-family:'DM Sans',sans-serif;
            font-weight:600; font-size:.9rem; cursor:pointer;
            transition:background .3s,transform .2s,box-shadow .3s; margin-top:8px;
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .btn-register:hover { background:#b03f58; transform:translateY(-2px); box-shadow:0 10px 25px rgba(201,83,106,.3); }
        .btn-register:disabled { opacity:.7; cursor:not-allowed; transform:none; }
        .alert { border-radius:10px; padding:11px 15px; font-size:.83rem; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
        .alert-error   { background:#fff0f3; border:1px solid #f5c6cb; color:#c0392b; }
        .alert-success { background:#f0fff4; border:1px solid #b7ebc8; color:#1e7e34; }
        .login-link { text-align:center; margin-top:22px; color:#aaa; font-size:.83rem; }
        .login-link a { color:var(--rose); font-weight:600; text-decoration:none; }
        .login-link a:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="register-card">
    <div class="brand">NEX<span>SYS</span></div>
    <p class="brand-sub">Portal de Clientes</p>
    <h2 class="section-title">Crea tu cuenta</h2>
    <p class="section-sub">Regístrate para hacer pedidos y más</p>

    <?php if ($error): ?>
    <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle"></i>
        <?= htmlspecialchars($success) ?>
        <a href="acceso.php" style="color:inherit;font-weight:600;margin-left:4px;">Iniciar sesión →</a>
    </div>
    <?php else: ?>
    <form method="POST" onsubmit="this.querySelector('button[type=submit]').disabled=true">
        <div class="row-2">
            <div>
                <label class="field-label">Nombre</label>
                <div class="field-wrap">
                    <i class="bi bi-person icon"></i>
                    <input type="text" name="nombre" required placeholder="Juan" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                </div>
            </div>
            <div>
                <label class="field-label">Apellido</label>
                <div class="field-wrap">
                    <i class="bi bi-person icon"></i>
                    <input type="text" name="apellido" placeholder="Pérez" value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                </div>
            </div>
        </div>
        <label class="field-label">Correo electrónico</label>
        <div class="field-wrap">
            <i class="bi bi-envelope icon"></i>
            <input type="email" name="email" required placeholder="tu@correo.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <label class="field-label">Teléfono (opcional)</label>
        <div class="field-wrap">
            <i class="bi bi-phone icon"></i>
            <input type="tel" name="telefono" placeholder="809-000-0000" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
        </div>
        <label class="field-label">Contraseña</label>
        <div class="field-wrap">
            <i class="bi bi-lock icon"></i>
            <input type="password" name="password" id="passField" required placeholder="Mínimo 6 caracteres" oninput="checkStrength(this.value)">
            <button type="button" class="toggle-pass" onclick="togglePass('passField','eye1')"><i class="bi bi-eye" id="eye1"></i></button>
        </div>
        <div class="password-strength"><div class="strength-bar" id="strengthBar"></div></div>
        <br>
        <label class="field-label">Confirmar contraseña</label>
        <div class="field-wrap">
            <i class="bi bi-lock-fill icon"></i>
            <input type="password" name="confirm_password" id="confirmField" required placeholder="Repite tu contraseña">
            <button type="button" class="toggle-pass" onclick="togglePass('confirmField','eye2')"><i class="bi bi-eye" id="eye2"></i></button>
        </div>
        <button type="submit" class="btn-register"><i class="bi bi-person-plus"></i> Crear mi cuenta</button>
    </form>
    <?php endif; ?>

    <p class="login-link">¿Ya tienes cuenta? <a href="acceso.php">Inicia sesión</a></p>
</div>
<script>
function togglePass(f,i){const el=document.getElementById(f),ic=document.getElementById(i);el.type=el.type==='password'?'text':'password';ic.className=el.type==='password'?'bi bi-eye':'bi bi-eye-slash';}
function checkStrength(val){const bar=document.getElementById('strengthBar');let s=0;if(val.length>=6)s++;if(val.length>=10)s++;if(/[A-Z]/.test(val))s++;if(/[0-9]/.test(val))s++;if(/[^A-Za-z0-9]/.test(val))s++;const c=['#e74c3c','#e67e22','#f1c40f','#2ecc71','#27ae60'];bar.style.width=(s*20)+'%';bar.style.background=c[s-1]||'#eee';}
</script>
</body>
</html>
