<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (isLoggedIn())        { header('Location: dashboard.php'); exit(); }
if (isClienteLoggedIn()) { header('Location: catalogo.php');  exit(); }

$error          = '';
$mostrarCarga   = false;
$rolRedireccion = '';
$nombreUsuario  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
    $email = trim($_POST['email']    ?? '');
    $pass  = trim($_POST['password'] ?? '');
    if ($email && $pass) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id,nombre,email,password,rol FROM usuarios WHERE email=? AND activo=1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['rol']        = $user['rol'];
            $mostrarCarga   = true;
            $rolRedireccion = $user['rol'];
            $nombreUsuario  = $user['nombre'];
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    } else {
        $error = 'Completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXSYS — Inicio de Sesión</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --primary: #1a3a5c;
  --accent:  #2563a8;
  --sidebar-bg: #1a3a5c;
  --sidebar-text: rgba(255,255,255,0.85);
  --right-bg: #f0f4f8;
  --card-bg: #ffffff;
  --card-text: #1a2535;
  --input-bg: #f4f7fa;
  --input-border: #dde3ea;
  --label-color: #4a5568;
  --muted: #8fa3b8;
  --tab-active: #1a3a5c;
  --divider: #e2e8f0;
  --gold: #F59E0B;
}
[data-theme="dark"] {
  --sidebar-bg: #0e2236;
  --right-bg: #111e2d;
  --card-bg: #162333;
  --card-text: #d0e0ef;
  --input-bg: #1a2d42;
  --input-border: #1e3a52;
  --label-color: #7ba0bc;
  --muted: #4d7a9a;
  --divider: #1e3a52;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; }

body {
  font-family: 'DM Sans', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--right-bg);
  transition: background 0.3s;
}

/* ── SPLASH ── */
#splash-screen { display:none; position:fixed; inset:0; z-index:9999; flex-direction:column; align-items:center; justify-content:center; gap:24px; }
#splash-screen.active { display:flex; }
.splash-bg { position:absolute; inset:0; background:linear-gradient(135deg,#06101f 0%,#0d2340 50%,#1F4E79 100%); }
.splash-content { position:relative; z-index:1; text-align:center; display:flex; flex-direction:column; align-items:center; gap:16px; }
.splash-logo-box { width:100px; height:100px; border-radius:24px; background:rgba(255,255,255,.1); border:1px solid rgba(245,158,11,.4); display:flex; align-items:center; justify-content:center; font-size:44px; animation:splashPop .6s cubic-bezier(.34,1.56,.64,1) forwards; opacity:0; box-shadow:0 0 40px rgba(245,158,11,.2); }
@keyframes splashPop { from{opacity:0;transform:scale(.5)} to{opacity:1;transform:scale(1)} }
.splash-title { color:#fff; font-size:28px; font-weight:700; letter-spacing:6px; animation:fadeUp .6s ease .3s forwards; opacity:0; }
.splash-title span { color:var(--gold); }
.splash-bienvenida { color:rgba(255,255,255,.9); font-size:16px; animation:fadeUp .6s ease .5s forwards; opacity:0; font-weight:500; }
.splash-rol { color:rgba(255,255,255,.5); font-size:11px; letter-spacing:3px; text-transform:uppercase; animation:fadeUp .6s ease .6s forwards; opacity:0; }
.splash-bar-wrap { width:260px; background:rgba(255,255,255,.15); border-radius:50px; height:5px; overflow:hidden; animation:fadeUp .6s ease .7s forwards; opacity:0; }
.splash-bar { height:100%; width:0%; background:linear-gradient(90deg,#2E75B6,#F59E0B); border-radius:50px; transition:width .08s linear; }
.splash-pct { color:rgba(255,255,255,.6); font-size:11px; letter-spacing:2px; animation:fadeUp .6s ease .8s forwards; opacity:0; }
@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:none} }

/* ── WRAPPER ── */
.login-wrapper {
  display: flex;
  width: 100%;
  max-width: 960px;
  min-height: 560px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(0,0,0,0.15);
  margin: 20px;
}

/* ── LEFT SIDEBAR ── */
.login-left {
  width: 300px;
  flex-shrink: 0;
  background: var(--sidebar-bg);
  padding: 44px 32px;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
  transition: background 0.3s;
}
.login-left::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    radial-gradient(circle at 20% 20%, rgba(46,117,182,0.15) 0%, transparent 60%),
    radial-gradient(circle at 80% 80%, rgba(46,117,182,0.1) 0%, transparent 50%);
}
.sidebar-logo-wrap {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 10px;
}
.sidebar-logo {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.25);
  background: rgba(255,255,255,0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.sidebar-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}
.sidebar-brand {
  position: relative;
  z-index: 1;
}
.sidebar-brand-name {
  color: #fff;
  font-size: 20px;
  font-weight: 700;
  letter-spacing: 0.5px;
  line-height: 1.1;
}
.sidebar-brand-sub {
  color: rgba(255,255,255,0.45);
  font-size: 10px;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  margin-top: 2px;
}
.sidebar-divider {
  position: relative;
  z-index: 1;
  width: 36px;
  height: 2px;
  background: rgba(255,255,255,0.2);
  border-radius: 2px;
  margin: 28px 0;
}
.sidebar-features {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
  flex: 1;
}
.sidebar-feature {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  color: var(--sidebar-text);
  font-size: 13px;
  line-height: 1.4;
}
.sidebar-feature::before {
  content: '•';
  color: rgba(255,255,255,0.4);
  flex-shrink: 0;
  margin-top: 1px;
  font-size: 16px;
  line-height: 1.2;
}
.sidebar-footer {
  position: relative;
  z-index: 1;
  margin-top: auto;
  padding-top: 28px;
  color: rgba(255,255,255,0.25);
  font-size: 11px;
}

/* ── RIGHT PANEL ── */
.login-right {
  flex: 1;
  background: var(--card-bg);
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 52px 56px;
  transition: background 0.3s;
}
.right-title {
  color: var(--card-text);
  font-size: 26px;
  font-weight: 700;
  margin-bottom: 4px;
  transition: color 0.3s;
}
.right-subtitle {
  color: var(--muted);
  font-size: 13px;
  margin-bottom: 32px;
  transition: color 0.3s;
}

/* ── TABS ── */
.auth-tabs {
  display: flex;
  border-bottom: 1.5px solid var(--divider);
  margin-bottom: 28px;
  gap: 0;
  transition: border-color 0.3s;
}
.auth-tab {
  flex: 1;
  text-align: center;
  padding: 10px 0;
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
  cursor: pointer;
  border: none;
  background: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1.5px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: color 0.2s, border-color 0.2s;
  text-decoration: none;
}
.auth-tab.active {
  color: var(--tab-active);
  border-bottom-color: var(--tab-active);
  font-weight: 600;
}
.auth-tab:hover:not(.active) {
  color: var(--label-color);
}

/* ── FORM ── */
.field-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--label-color);
  margin-bottom: 6px;
  display: block;
  transition: color 0.3s;
}
.field-wrap {
  position: relative;
  margin-bottom: 18px;
}
.field-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--muted);
  font-size: 15px;
  pointer-events: none;
  transition: color 0.3s;
}
.field-input {
  width: 100%;
  background: var(--input-bg);
  border: 1.5px solid var(--input-border);
  border-radius: 10px;
  padding: 12px 14px 12px 40px;
  font-size: 14px;
  color: var(--card-text);
  font-family: 'DM Sans', sans-serif;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.3s, color 0.3s;
}
.field-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(37,99,168,0.1);
  background: var(--card-bg);
}
.field-input::placeholder {
  color: var(--muted);
}
.eye-btn {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--muted);
  cursor: pointer;
  font-size: 15px;
  padding: 0;
  line-height: 1;
  transition: color 0.2s;
}
.eye-btn:hover { color: var(--label-color); }

/* ── SUBMIT BTN ── */
.btn-auth {
  width: 100%;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 13px;
  font-size: 14px;
  font-weight: 600;
  font-family: 'DM Sans', sans-serif;
  cursor: pointer;
  transition: background 0.2s, opacity 0.2s;
  letter-spacing: 0.3px;
  margin-top: 4px;
}
.btn-auth:hover { background: var(--accent); }
.btn-auth:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── BOTTOM LINK ── */
.bottom-hint {
  margin-top: 20px;
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--muted);
  font-size: 12px;
}
.bottom-hint i { font-size: 13px; }
.bottom-hint a {
  color: var(--accent);
  text-decoration: none;
  font-weight: 500;
}
.bottom-hint a:hover { text-decoration: underline; }

/* ── THEME TOGGLE ── */
.login-theme-toggle {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 100;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(26,58,92,0.12);
  border: 1.5px solid rgba(26,58,92,0.15);
  color: var(--primary);
  font-size: 15px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
[data-theme="dark"] .login-theme-toggle {
  background: rgba(255,255,255,0.08);
  border-color: rgba(255,255,255,0.12);
  color: #94b8d4;
}
.login-theme-toggle:hover { transform: scale(1.1); }

/* ── ALERT ── */
.alert-err {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
  border-radius: 8px;
  padding: 9px 13px;
  font-size: 12px;
  margin-bottom: 18px;
  display: flex;
  align-items: center;
  gap: 7px;
}
[data-theme="dark"] .alert-err {
  background: rgba(185,28,28,0.12);
  border-color: rgba(185,28,28,0.25);
  color: #f87171;
}

@media (max-width: 680px) {
  .login-left { display: none; }
  .login-wrapper { max-width: 440px; border-radius: 16px; }
  .login-right { padding: 36px 28px; }
}
</style>
<link rel="icon" href="https://res.cloudinary.com/da6mdp5h1/image/upload/q_auto/f_auto/v1779593890/nexsys_1_pnai3b.jpg">
<link rel="shortcut icon" href="https://res.cloudinary.com/da6mdp5h1/image/upload/q_auto/f_auto/v1779593890/nexsys_1_pnai3b.jpg">
</head>
<body>
<script>(function(){ const t=localStorage.getItem('mm-theme')||'light'; document.documentElement.setAttribute('data-theme',t); })();</script>

<button class="login-theme-toggle" onclick="toggleLoginTheme()" id="loginThemeBtn" title="Cambiar modo">
  <i class="bi bi-moon-fill" id="loginThemeIcon"></i>
</button>

<!-- SPLASH DE CARGA CON BIENVENIDA -->
<div id="splash-screen" <?= $mostrarCarga ? 'class="active"' : '' ?>>
  <div class="splash-bg"></div>
  <div class="splash-content">
    <div class="splash-logo-box">⚡</div>
    <div class="splash-title">NEX<span>SYS</span></div>
    <div class="splash-bienvenida">¡Bienvenido, <?= htmlspecialchars($nombreUsuario) ?>! 👋</div>
    <div class="splash-rol" id="splashRolLabel"></div>
    <div class="splash-bar-wrap"><div class="splash-bar" id="splashBar"></div></div>
    <div class="splash-pct" id="splashPct">0%</div>
  </div>
</div>

<div class="login-wrapper">

  <!-- ── SIDEBAR IZQUIERDO ── -->
  <div class="login-left">
    <div class="sidebar-logo-wrap">
      <div class="sidebar-logo">
        <img src="https://res.cloudinary.com/da6mdp5h1/image/upload/q_auto/f_auto/v1779593890/nexsys_1_pnai3b.jpg"
             alt="NEXSYS"
             onerror="this.parentElement.innerHTML='⚡'">
      </div>
      <div class="sidebar-brand">
        <div class="sidebar-brand-name">NEXSYS</div>
        <div class="sidebar-brand-sub">Sistema de Gestión</div>
      </div>
    </div>

    <div class="sidebar-divider"></div>

    <div class="sidebar-features">
      <div class="sidebar-feature">Control de inventario en tiempo real</div>
      <div class="sidebar-feature">Gestión de clientes, técnicos y roles</div>
      <div class="sidebar-feature">Reportes y seguimiento de órdenes</div>
      <div class="sidebar-feature">Acceso seguro con control de permisos</div>
    </div>

    <div class="sidebar-footer">© NEXSYS — Sistema empresarial</div>
  </div>

  <!-- ── PANEL DERECHO ── -->
  <div class="login-right">

    <div class="right-title">Bienvenido de vuelta</div>
    <div class="right-subtitle">Ingresa tus credenciales para acceder al sistema</div>

    <!-- TABS -->
    <div class="auth-tabs">
      <button class="auth-tab active" type="button">
        <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
      </button>
      <a href="acceso.php" class="auth-tab">
        <i class="bi bi-person-plus"></i> Registro
      </a>
    </div>

    <?php if ($error): ?>
      <div class="alert-err">
        <i class="bi bi-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" onsubmit="onLoginSubmit(this)">
      <input type="hidden" name="accion" value="login">

      <label class="field-label">Usuario</label>
      <div class="field-wrap">
        <i class="bi bi-person field-icon"></i>
        <input type="email" name="email" class="field-input"
               placeholder="usuario@nexsys.com" required autofocus>
      </div>

      <label class="field-label">Contraseña</label>
      <div class="field-wrap">
        <i class="bi bi-lock field-icon"></i>
        <input type="password" name="password" id="passInput" class="field-input"
               placeholder="••••••" required>
        <button type="button" class="eye-btn" onclick="togglePass()">
          <i class="bi bi-eye" id="eyeBtn"></i>
        </button>
      </div>

      <button type="submit" class="btn-auth" id="btnLogin">
        Entrar al sistema
      </button>
    </form>

    <div class="bottom-hint">
      <i class="bi bi-info-circle"></i>
      <span>La solución a tu problema, está aquí.</span>
    </div>

  </div>
</div>

<script>
function applyLoginTheme(t) {
  document.documentElement.setAttribute('data-theme', t);
  const icon = document.getElementById('loginThemeIcon');
  if (icon) icon.className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
}
function toggleLoginTheme() {
  const cur  = document.documentElement.getAttribute('data-theme') || 'light';
  const next = cur === 'dark' ? 'light' : 'dark';
  localStorage.setItem('mm-theme', next);
  applyLoginTheme(next);
}
applyLoginTheme(localStorage.getItem('mm-theme') || 'light');

function togglePass() {
  const inp = document.getElementById('passInput');
  const ic  = document.getElementById('eyeBtn');
  inp.type  = inp.type === 'password' ? 'text' : 'password';
  ic.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function onLoginSubmit(f) {
  const btn = document.getElementById('btnLogin');
  btn.disabled = true;
  btn.textContent = 'Verificando...';
}

<?php if ($mostrarCarga): ?>
(function(){
  const bar  = document.getElementById('splashBar');
  const pct  = document.getElementById('splashPct');
  const rl   = document.getElementById('splashRolLabel');
  const roles    = {administrador:'Administrador del Sistema', gerente:'Gerente', cajero:'Cajero'};
  const destinos = {administrador:'dashboard.php', gerente:'dashboard.php', cajero:'ventas/index.php'};
  const rol     = '<?= addslashes($rolRedireccion) ?>';
  const destino = destinos[rol] || 'dashboard.php';
  if (rl) rl.textContent = roles[rol] || rol;
  let progress = 0;
  const iv = setInterval(() => {
    progress += Math.random() * 3 + 1.5;
    if (progress >= 100) {
      progress = 100; bar.style.width = '100%'; pct.textContent = '100%';
      clearInterval(iv);
      setTimeout(() => { window.location.href = destino; }, 400);
    } else {
      bar.style.width = progress + '%';
      pct.textContent = Math.floor(progress) + '%';
    }
  }, 50);
})();
<?php endif; ?>
</script>
</body>
</html>
