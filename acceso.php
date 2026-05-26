<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (isLoggedIn())        { header('Location: dashboard.php'); exit(); }
if (isClienteLoggedIn()) { header('Location: catalogo.php');  exit(); }

$error   = $success = '';
$modo    = $_GET['modo'] ?? 'cliente';

// ── LOGIN CLIENTE ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login_cliente') {
    $email = trim($_POST['email_c'] ?? '');
    $pass  = trim($_POST['password_c'] ?? '');
    if ($email && $pass) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id,nombre,email,password FROM clientes WHERE email=? AND activo=1");
        $stmt->bind_param('s', $email); $stmt->execute();
        $cli  = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($cli && password_verify($pass, $cli['password'])) {
            $_SESSION['cliente_id']     = $cli['id'];
            $_SESSION['cliente_nombre'] = $cli['nombre'];
            $_SESSION['cliente_email']  = $cli['email'];
            header('Location: catalogo.php'); exit();
        } else { $error = 'Correo o contraseña incorrectos.'; }
    } else { $error = 'Completa todos los campos.'; }
}

// ── OLVIDÉ MI CONTRASEÑA ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'olvide_pass') {
    $email_olvide = trim($_POST['email_olvide'] ?? '');
    $modo = 'olvide';

    if (!$email_olvide) {
        $error = 'Por favor ingresa tu correo electrónico.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, nombre, email FROM clientes WHERE email=? AND activo=1");
        $stmt->bind_param('s', $email_olvide);
        $stmt->execute();
        $cli = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$cli) {
            $error = 'No encontramos una cuenta con ese correo.';
        } else {
            $adminEmail    = 'deurys35@gmail.com';
            $nombreCliente = $cli['nombre'];
            $correoCliente = $cli['email'];
            $fecha         = date('d/m/Y H:i');

            $asunto  = "NEXSYS — Solicitud de cambio de contrasena";
            $mensaje  = "Hola Administrador,\n\n";
            $mensaje .= "El cliente {$nombreCliente} ha solicitado un cambio de contrasena.\n\n";
            $mensaje .= "Datos del cliente:\n";
            $mensaje .= "  * Nombre: {$nombreCliente}\n";
            $mensaje .= "  * Correo: {$correoCliente}\n";
            $mensaje .= "  * Fecha de solicitud: {$fecha}\n\n";
            $mensaje .= "Por favor accede al sistema y cambia su contrasena desde el modulo de Clientes.\n\n";
            $mensaje .= "— Sistema NEXSYS";

            $headers  = "From: no-reply@nexsys.app\r\n";
            $headers .= "Reply-To: {$correoCliente}\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            $webhookUrl = 'https://n8n-production-91d2.up.railway.app/webhook/recuperar-password';
$payload = json_encode([
    'nombre' => $nombreCliente,
    'correo' => $correoCliente,
    'fecha'  => $fecha
]);
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    $success = 'Solicitud enviada. El administrador recibira un aviso y te contactara para restablecer tu contrasena.';
    $modo = 'cliente';
} else {
    $error = 'Hubo un problema al enviar la solicitud. Intenta mas tarde.';
}
        }
    }
}

// ── REGISTRO CLIENTE ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registro') {
    $nombre    = trim($_POST['nombre']    ?? '');
    $cedula    = trim($_POST['cedula']    ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $email     = trim($_POST['email_reg'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $pass      = trim($_POST['pass_reg']  ?? '');
    $pass2     = trim($_POST['pass_reg2'] ?? '');
    $modo = 'registro';

    if (!$nombre || !$email || !$pass) {
        $error = 'Nombre, correo y contraseña son obligatorios.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($pass) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        $db = getDB();
        $st = $db->prepare("SELECT id FROM clientes WHERE email=?");
        $st->bind_param('s', $email); $st->execute();
        if ($st->get_result()->num_rows > 0) {
            $error = 'Ya existe una cuenta con ese correo.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $st2  = $db->prepare("INSERT INTO clientes (nombre,cedula,telefono,email,password,direccion,activo) VALUES (?,?,?,?,?,?,1)");
            $st2->bind_param('ssssss', $nombre, $cedula, $telefono, $email, $hash, $direccion);
            $st2->execute(); $st2->close();
            $success = '¡Cuenta creada! Ya puedes iniciar sesión.';
            $modo = 'cliente';
        }
        $st->close();
    }
}

$esCliente  = $modo === 'cliente';
$esRegistro = $modo === 'registro';
$esOlvide   = $modo === 'olvide';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXSYS — Acceso de Clientes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root{--primary:#1F4E79;--accent:#2E75B6;--gold:#F59E0B;--green:#0F6E56;--card-bg:#fff;--card-text:#1a1a2e;--input-bg:#F9FAFB;--input-border:#E5E7EB;--label-color:#374151;--muted:#9CA3AF;--tab-active:#2E75B6;--tab-border:#2E75B6;}
[data-theme="dark"]{--card-bg:#142233;--card-text:#d6e4f0;--input-bg:#1a2d42;--input-border:#1e3a52;--label-color:#94a3b8;--muted:#6e9ab8;--tab-active:#2E75B6;--tab-border:#2E75B6;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;}
body{font-family:'Segoe UI',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;background:linear-gradient(135deg,#06101f 0%,#0d2340 50%,#1F4E79 100%);}
.bg-grid{position:fixed;inset:0;z-index:0;background-image:linear-gradient(rgba(46,117,182,0.08) 1px,transparent 1px),linear-gradient(90deg,rgba(46,117,182,0.08) 1px,transparent 1px);background-size:48px 48px;animation:gridMove 20s linear infinite;}
@keyframes gridMove{from{transform:translateY(0)}to{transform:translateY(48px)}}
.orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0;animation:orbPulse 6s ease-in-out infinite;}
.orb-1{width:500px;height:500px;background:rgba(46,117,182,0.2);top:-100px;right:-100px;}
.orb-2{width:350px;height:350px;background:rgba(13,35,64,0.3);bottom:-80px;left:-80px;animation-delay:2s;}
.orb-3{width:200px;height:200px;background:rgba(245,158,11,0.06);top:50%;left:40%;animation-delay:4s;}
@keyframes orbPulse{0%,100%{opacity:.6;transform:scale(1)}50%{opacity:1;transform:scale(1.1)}}

.login-wrapper{position:relative;z-index:1;display:flex;width:100%;max-width:860px;min-height:520px;border-radius:24px;overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,0.4);margin:20px;}

/* Panel izquierdo verde */
.login-left{width:300px;flex-shrink:0;background:linear-gradient(135deg,#06101f,#1F4E79);padding:40px 28px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;position:relative;overflow:hidden;}
.login-left::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(46,117,182,0.15) 1px,transparent 1px),linear-gradient(90deg,rgba(46,117,182,0.15) 1px,transparent 1px);background-size:32px 32px;}
.brand-logo{position:relative;z-index:1;width:80px;height:80px;background:rgba(255,255,255,0.1);border:1px solid rgba(46,117,182,0.5);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:36px;margin-bottom:18px;box-shadow:0 0 30px rgba(46,117,182,0.2);}
.brand-name{position:relative;z-index:1;font-size:30px;font-weight:700;color:#fff;letter-spacing:6px;margin-bottom:4px;}
.brand-name span{color:var(--gold);}
.brand-tagline{position:relative;z-index:1;font-size:10px;letter-spacing:3px;color:rgba(255,255,255,0.5);text-transform:uppercase;margin-bottom:24px;}
.brand-features{position:relative;z-index:1;display:flex;flex-direction:column;gap:10px;width:100%;}
.brand-feature{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:9px 12px;text-align:left;}
.brand-feature i{color:var(--gold);font-size:15px;flex-shrink:0;}
.brand-feature span{color:rgba(255,255,255,0.8);font-size:12px;}

/* Panel derecho */
.login-right{flex:1;background:var(--card-bg);transition:background .3s;display:flex;flex-direction:column;}
.auth-header{background:linear-gradient(135deg,#1F4E79,#2E75B6);padding:20px 28px;text-align:center;color:#fff;}
.auth-header h4{margin:0;font-weight:700;font-size:17px;}
.auth-header small{opacity:.7;font-size:11px;}

.auth-tabs{display:flex;border-bottom:1px solid var(--input-border);background:var(--card-bg);transition:background .3s,border-color .3s;}
.auth-tab{flex:1;padding:11px 6px;text-align:center;font-size:12px;font-weight:600;cursor:pointer;color:var(--muted);border:none;background:none;transition:all .2s;border-bottom:3px solid transparent;}
.auth-tab.active{color:var(--tab-active);border-bottom-color:var(--tab-border);}

.auth-body{padding:20px 28px 24px;flex:1;background:var(--card-bg);color:var(--card-text);transition:background .3s,color .3s;overflow-y:auto;}
.form-label{font-size:12px;font-weight:600;color:var(--label-color);margin-bottom:3px;}
.form-control{border-radius:8px;border:1.5px solid var(--input-border);font-size:13px;padding:8px 11px;background:var(--input-bg);color:var(--card-text);transition:all .3s;width:100%;}
.form-control:focus{border-color:#2E75B6;box-shadow:0 0 0 3px rgba(46,117,182,.12);outline:none;}
.input-group-text{border-radius:8px 0 0 8px;border:1.5px solid var(--input-border);background:var(--input-bg);color:var(--muted);font-size:13px;}
.input-group .form-control{border-radius:0 8px 8px 0;}
.btn-auth{background:linear-gradient(135deg,#1F4E79,#2E75B6);color:#fff;border:none;width:100%;padding:10px;border-radius:10px;font-size:14px;font-weight:600;transition:opacity .2s;margin-top:4px;cursor:pointer;}
.btn-auth:hover{opacity:.88;}
.tab-pane{display:none;}.tab-pane.show{display:block;}
.footer-txt{text-align:center;color:var(--muted);font-size:11px;margin-top:14px;}
.divider{text-align:center;color:var(--muted);font-size:11px;margin:10px 0;position:relative;}
.divider::before,.divider::after{content:'';position:absolute;top:50%;width:42%;height:1px;background:var(--input-border);}
.divider::before{left:0;}.divider::after{right:0;}
.emp-link{text-align:center;margin-top:14px;padding-top:14px;border-top:1px solid var(--input-border);}
.emp-link a{color:var(--accent);font-size:12px;text-decoration:none;}
.emp-link a:hover{text-decoration:underline;}

.login-theme-toggle{position:fixed;top:20px;right:20px;z-index:100;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;backdrop-filter:blur(8px);}
.login-theme-toggle:hover{background:rgba(255,255,255,0.2);transform:scale(1.1);}

@media(max-width:640px){.login-left{display:none;}.login-wrapper{max-width:420px;border-radius:20px;}}
</style>
</head>
<body>
<script>(function(){const t=localStorage.getItem('mm-theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>

<button class="login-theme-toggle" onclick="toggleLoginTheme()" title="Cambiar modo">
  <i class="bi bi-moon-fill" id="loginThemeIcon"></i>
</button>

<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="login-wrapper">
  <!-- PANEL IZQUIERDO -->
  <div class="login-left">
    <div class="brand-logo">🛒</div>
    <div class="brand-name">NEX<span>SYS</span></div>
    <div class="brand-tagline">Portal de Clientes</div>
    <div class="brand-features">
      <div class="brand-feature"><i class="bi bi-shop"></i><span>Catálogo completo de productos</span></div>
      <div class="brand-feature"><i class="bi bi-bicycle"></i><span>Delivery a domicilio</span></div>
      <div class="brand-feature"><i class="bi bi-bag-check"></i><span>Seguimiento de pedidos</span></div>
      <div class="brand-feature"><i class="bi bi-shield-check"></i><span>Compra segura y confiable</span></div>
    </div>
  </div>

  <!-- PANEL DERECHO -->
  <div class="login-right">
    <div class="auth-header">
      <h4>🛒 Portal de Clientes — NEXSYS</h4>
      <small>Inicia sesión o crea tu cuenta gratis</small>
    </div>

    <div class="auth-tabs">
      <button class="auth-tab <?= $esCliente?'active':'' ?>" onclick="switchTab('cliente')">
        <i class="bi bi-person me-1"></i>Iniciar Sesión
      </button>
      <button class="auth-tab <?= $esRegistro?'active':'' ?>" onclick="switchTab('registro')">
        <i class="bi bi-person-plus me-1"></i>Crear Cuenta
      </button>
      <button class="auth-tab <?= $esOlvide?'active':'' ?>" onclick="switchTab('olvide')">
        <i class="bi bi-key me-1"></i>¿Olvidé mi contraseña?
      </button>
    </div>

    <div class="auth-body">
      <?php if ($error): ?>
        <div class="alert alert-danger py-2 mb-3" style="font-size:12px;border-radius:8px;">
          <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success py-2 mb-3" style="font-size:12px;border-radius:8px;">
          <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <!-- LOGIN CLIENTE -->
      <div class="tab-pane <?= $esCliente?'show':'' ?>" id="tabCliente">
        <form method="POST">
          <input type="hidden" name="accion" value="login_cliente">
          <div class="mb-2">
            <label class="form-label">Correo</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email_c" class="form-control" placeholder="tucorreo@gmail.com" required autofocus>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" name="password_c" class="form-control" placeholder="••••••••" required>
            </div>
          </div>
          <button type="submit" class="btn-auth"><i class="bi bi-shop me-2"></i>Entrar al Catálogo</button>
        </form>
        <div class="divider mt-3">¿Primera vez aquí?</div>
        <button class="btn btn-outline-success w-100 btn-sm mt-1" onclick="switchTab('registro')"><i class="bi bi-person-plus me-1"></i>Crear cuenta gratis</button>
        <button class="btn btn-outline-warning w-100 btn-sm mt-2" onclick="switchTab('olvide')"><i class="bi bi-key me-1"></i>Olvidé mi contraseña</button>
      </div>

      <!-- REGISTRO -->
      <div class="tab-pane <?= $esRegistro?'show':'' ?>" id="tabRegistro">
        <form method="POST">
          <input type="hidden" name="accion" value="registro">
          <div class="row g-2">
            <div class="col-12">
              <label class="form-label">Nombre completo *</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="nombre" class="form-control" placeholder="Tu nombre" required>
              </div>
            </div>
            <div class="col-6">
              <label class="form-label">Cédula</label>
              <input type="text" name="cedula" class="form-control" placeholder="000-0000000-0">
            </div>
            <div class="col-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control" placeholder="809-000-0000">
            </div>
            <div class="col-12">
              <label class="form-label">Correo *</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email_reg" class="form-control" placeholder="tucorreo@gmail.com" required>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Dirección</label>
              <input type="text" name="direccion" class="form-control" placeholder="Sector, calle...">
            </div>
            <div class="col-6">
              <label class="form-label">Contraseña *</label>
              <input type="password" name="pass_reg" class="form-control" placeholder="••••••" required>
            </div>
            <div class="col-6">
              <label class="form-label">Confirmar *</label>
              <input type="password" name="pass_reg2" class="form-control" placeholder="••••••" required>
            </div>
          </div>
          <button type="submit" class="btn-auth mt-3"><i class="bi bi-person-check me-2"></i>Crear Cuenta</button>
        </form>
        <div class="divider mt-3">¿Ya tienes cuenta?</div>
        <button class="btn btn-outline-primary w-100 btn-sm mt-1" onclick="switchTab('cliente')">Iniciar sesión</button>
      </div>

      <!-- OLVIDÉ MI CONTRASEÑA -->
      <div class="tab-pane <?= $esOlvide?'show':'' ?>" id="tabOlvide">
        <div class="text-center mb-3">
          <i class="bi bi-key" style="font-size:36px;color:#F59E0B;"></i>
          <p class="mt-2" style="font-size:12px;color:var(--muted);">
            Ingresa tu correo y le avisaremos al administrador<br>para que te restablezca la contraseña.
          </p>
        </div>
        <form method="POST">
          <input type="hidden" name="accion" value="olvide_pass">
          <div class="mb-3">
            <label class="form-label">Correo de tu cuenta</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email_olvide" class="form-control" placeholder="tucorreo@gmail.com" required autofocus>
            </div>
          </div>
          <button type="submit" class="btn-auth" style="background:linear-gradient(135deg,#B45309,#F59E0B);">
            <i class="bi bi-send me-2"></i>Enviar solicitud al administrador
          </button>
        </form>
        <div class="divider mt-3">¿Recordaste tu contraseña?</div>
        <button class="btn btn-outline-primary w-100 btn-sm mt-1" onclick="switchTab('cliente')">Volver a iniciar sesión</button>
      </div>

      <div class="emp-link">
        ¿Eres empleado? <a href="login.php">Accede al sistema →</a>
      </div>
      <p class="footer-txt">NEXSYS · Sistema de Gestión Integral · 2026</p>
    </div>
  </div>
</div>

<script>
function switchTab(tab){
  ['cliente','registro','olvide'].forEach(t=>{
    document.getElementById('tab'+t.charAt(0).toUpperCase()+t.slice(1)).classList.toggle('show',t===tab);
  });
  document.querySelectorAll('.auth-tab').forEach((el,i)=>{
    el.classList.toggle('active',['cliente','registro','olvide'][i]===tab);
  });
}
function applyLoginTheme(t){
  document.documentElement.setAttribute('data-theme',t);
  const icon=document.getElementById('loginThemeIcon');
  if(icon) icon.className=t==='dark'?'bi bi-sun-fill':'bi bi-moon-fill';
}
function toggleLoginTheme(){
  const cur=document.documentElement.getAttribute('data-theme')||'light';
  const next=cur==='dark'?'light':'dark';
  localStorage.setItem('mm-theme',next);
  applyLoginTheme(next);
}
applyLoginTheme(localStorage.getItem('mm-theme')||'light');
</script>
</body>
</html>