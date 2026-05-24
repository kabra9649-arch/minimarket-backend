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
<style>
:root {
  --primary:#1F4E79; --accent:#2E75B6; --gold:#F59E0B;
  --card-bg:#ffffff; --card-text:#1a1a2e;
  --input-bg:#F9FAFB; --input-border:#E5E7EB;
  --label-color:#374151; --muted:#9CA3AF;
  --tab-active:#1F4E79; --tab-border:#1F4E79;
}
[data-theme="dark"] {
  --card-bg:#142233; --card-text:#d6e4f0;
  --input-bg:#1a2d42; --input-border:#1e3a52;
  --label-color:#94a3b8; --muted:#6e9ab8;
  --tab-active:#6eafd4; --tab-border:#2E75B6;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;}
body{font-family:'Segoe UI',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;background:linear-gradient(135deg,#06101f 0%,#0d2340 50%,#1F4E79 100%);}
.bg-grid{position:fixed;inset:0;z-index:0;background-image:linear-gradient(rgba(46,117,182,.07) 1px,transparent 1px),linear-gradient(90deg,rgba(46,117,182,.07) 1px,transparent 1px);background-size:48px 48px;animation:gridMove 20s linear infinite;}
@keyframes gridMove{from{transform:translateY(0)}to{transform:translateY(48px)}}
.orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0;animation:orbPulse 6s ease-in-out infinite;}
.orb-1{width:500px;height:500px;background:rgba(46,117,182,.2);top:-100px;right:-100px;}
.orb-2{width:350px;height:350px;background:rgba(31,78,121,.25);bottom:-80px;left:-80px;animation-delay:2s;}
.orb-3{width:200px;height:200px;background:rgba(245,158,11,.08);top:50%;left:40%;animation-delay:4s;}
@keyframes orbPulse{0%,100%{opacity:.6;transform:scale(1)}50%{opacity:1;transform:scale(1.1)}}
.login-wrapper{position:relative;z-index:1;display:flex;gap:0;width:100%;max-width:900px;min-height:520px;border-radius:24px;overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,.4);margin:20px;}
.login-left{width:320px;flex-shrink:0;background:linear-gradient(135deg,#0a1628,#1F4E79);padding:40px 32px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;position:relative;overflow:hidden;}
.login-left::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(46,117,182,.1) 1px,transparent 1px),linear-gradient(90deg,rgba(46,117,182,.1) 1px,transparent 1px);background-size:32px 32px;}
.brand-logo{position:relative;z-index:1;width:80px;height:80px;background:rgba(255,255,255,.1);border:1px solid rgba(245,158,11,.5);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:36px;margin-bottom:20px;box-shadow:0 0 30px rgba(245,158,11,.2);}
.brand-name{position:relative;z-index:1;font-size:32px;font-weight:700;color:#fff;letter-spacing:6px;margin-bottom:4px;}
.brand-name span{color:var(--gold);}
.brand-tagline{position:relative;z-index:1;font-size:11px;letter-spacing:3px;color:rgba(255,255,255,.5);text-transform:uppercase;margin-bottom:32px;}
.brand-features{position:relative;z-index:1;display:flex;flex-direction:column;gap:12px;width:100%;}
.brand-feature{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:10px 14px;text-align:left;}
.brand-feature i{color:var(--gold);font-size:16px;flex-shrink:0;}
.brand-feature span{color:rgba(255,255,255,.75);font-size:12px;}
.login-right{flex:1;background:var(--card-bg);transition:background .3s;display:flex;flex-direction:column;}
.auth-header{background:linear-gradient(135deg,#1F4E79,#2E75B6);padding:20px 28px 16px;text-align:center;color:#fff;}
.auth-header h4{margin:0;font-weight:700;font-size:17px;}
.auth-header small{opacity:.7;font-size:11px;}
.auth-body{padding:24px 28px 28px;flex:1;background:var(--card-bg);color:var(--card-text);transition:background .3s,color .3s;}
.form-label{font-size:12px;font-weight:600;color:var(--label-color);margin-bottom:3px;transition:color .3s;}
.form-control,.form-select{border-radius:8px;border:1.5px solid var(--input-border);font-size:13px;padding:9px 11px;background:var(--input-bg);color:var(--card-text);transition:all .3s;}
.form-control:focus{border-color:#2E75B6;box-shadow:0 0 0 3px rgba(46,117,182,.12);}
.input-group-text{border-radius:8px 0 0 8px;border:1.5px solid var(--input-border);background:var(--input-bg);color:var(--muted);font-size:13px;transition:all .3s;}
.input-group .form-control{border-radius:0 8px 8px 0;}
.btn-auth{background:linear-gradient(135deg,#1F4E79,#2E75B6);color:#fff;border:none;width:100%;padding:11px;border-radius:10px;font-size:14px;font-weight:600;transition:opacity .2s;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-auth:hover{opacity:.88;}
.btn-auth:disabled{opacity:.6;cursor:not-allowed;}
.footer-txt{text-align:center;color:var(--muted);font-size:11px;margin-top:18px;}
.login-theme-toggle{position:fixed;top:20px;right:20px;z-index:100;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;backdrop-filter:blur(8px);}
.login-theme-toggle:hover{background:rgba(255,255,255,.2);transform:scale(1.1);}

/* ── SPLASH ── */
#splash-screen{display:none;position:fixed;inset:0;z-index:9999;flex-direction:column;align-items:center;justify-content:center;gap:24px;}
#splash-screen.active{display:flex;}
.splash-bg{position:absolute;inset:0;background:linear-gradient(135deg,#06101f 0%,#0d2340 50%,#1F4E79 100%);}
.splash-content{position:relative;z-index:1;text-align:center;display:flex;flex-direction:column;align-items:center;gap:16px;}
.splash-logo-box{width:100px;height:100px;border-radius:24px;background:rgba(255,255,255,.1);border:1px solid rgba(245,158,11,.4);display:flex;align-items:center;justify-content:center;font-size:44px;animation:splashPop .6s cubic-bezier(.34,1.56,.64,1) forwards;opacity:0;box-shadow:0 0 40px rgba(245,158,11,.2);}
@keyframes splashPop{from{opacity:0;transform:scale(.5)}to{opacity:1;transform:scale(1)}}
.splash-title{color:#fff;font-size:28px;font-weight:700;letter-spacing:6px;animation:fadeUp .6s ease .3s forwards;opacity:0;}
.splash-title span{color:var(--gold);}
.splash-bienvenida{color:rgba(255,255,255,.9);font-size:16px;animation:fadeUp .6s ease .5s forwards;opacity:0;font-weight:500;}
.splash-rol{color:rgba(255,255,255,.5);font-size:11px;letter-spacing:3px;text-transform:uppercase;animation:fadeUp .6s ease .6s forwards;opacity:0;}
.splash-bar-wrap{width:260px;background:rgba(255,255,255,.15);border-radius:50px;height:5px;overflow:hidden;animation:fadeUp .6s ease .7s forwards;opacity:0;}
.splash-bar{height:100%;width:0%;background:linear-gradient(90deg,#2E75B6,#F59E0B);border-radius:50px;transition:width .08s linear;}
.splash-pct{color:rgba(255,255,255,.6);font-size:11px;letter-spacing:2px;animation:fadeUp .6s ease .8s forwards;opacity:0;}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
@media(max-width:680px){.login-left{display:none;}.login-wrapper{max-width:440px;border-radius:20px;}}
</style>
<link rel="icon" href="https://res.cloudinary.com/da6mdp5h1/image/upload/q_auto/f_auto/v1779593890/nexsys_1_pnai3b.jpg">
<link rel="shortcut icon" href="https://res.cloudinary.com/da6mdp5h1/image/upload/q_auto/f_auto/v1779593890/nexsys_1_pnai3b.jpg">
</head>
<body>
<script>(function(){const t=localStorage.getItem('mm-theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>

<button class="login-theme-toggle" onclick="toggleLoginTheme()" id="loginThemeBtn" title="Cambiar modo">
  <i class="bi bi-moon-fill" id="loginThemeIcon"></i>
</button>

<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

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
  <div class="login-left">
    <div class="brand-logo">⚡</div>
    <div class="brand-name">NEX<span>SYS</span></div>
    <div class="brand-tagline">Sistema de Gestión</div>
    <div class="brand-features">
      <div class="brand-feature"><i class="bi bi-box-seam"></i><span>Control de inventario en tiempo real</span></div>
      <div class="brand-feature"><i class="bi bi-graph-up"></i><span>Reportes y analíticas avanzadas</span></div>
      <div class="brand-feature"><i class="bi bi-robot"></i><span>IA integrada con NEXSYS AI</span></div>
      <div class="brand-feature"><i class="bi bi-shield-check"></i><span>Acceso seguro por roles</span></div>
    </div>
  </div>

  <div class="login-right">
    <div class="auth-header">
      <h4>⚡ NEXSYS — Inicio de Sesión</h4>
      <small>Acceso para Administradores y Empleados</small>
    </div>
    <div class="auth-body">
      <?php if ($error): ?>
        <div class="alert alert-danger py-2 mb-3" style="font-size:12px;border-radius:8px;">
          <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" onsubmit="onLoginSubmit(this)">
        <input type="hidden" name="accion" value="login">
        <div class="mb-3">
          <label class="form-label">Correo electrónico</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="usuario@nexsys.com" required autofocus>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            <button type="button" class="input-group-text" onclick="togglePass()" style="cursor:pointer;border-radius:0 8px 8px 0;">
              <i class="bi bi-eye" id="eyeBtn"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-auth" id="btnLogin">
          <i class="bi bi-box-arrow-in-right"></i> Entrar al Sistema
        </button>
      </form>

      <div style="text-align:center;margin-top:18px;padding-top:16px;border-top:1px solid var(--input-border);">
        <a href="acceso.php" style="color:var(--accent);font-size:12px;text-decoration:none;">
          <i class="bi bi-shop me-1"></i>Acceso de clientes →
        </a>
      </div>

      <p class="footer-txt">NEXSYS · Sistema de Gestión Integral · 2026</p>
    </div>
  </div>
</div>

<script>
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

function togglePass(){
  const inp=document.querySelector('input[name=password]');
  const ic=document.getElementById('eyeBtn');
  inp.type=inp.type==='password'?'text':'password';
  ic.className=inp.type==='password'?'bi bi-eye':'bi bi-eye-slash';
}

function onLoginSubmit(f){
  const btn=document.getElementById('btnLogin');
  btn.disabled=true;
  btn.innerHTML='<i class="bi bi-hourglass-split"></i> Verificando...';
}

<?php if ($mostrarCarga): ?>
(function(){
  const bar  = document.getElementById('splashBar');
  const pct  = document.getElementById('splashPct');
  const rl   = document.getElementById('splashRolLabel');
  const roles = {administrador:'Administrador del Sistema',gerente:'Gerente',cajero:'Cajero'};
  const destinos = {administrador:'dashboard.php',gerente:'dashboard.php',cajero:'ventas/index.php'};
  const rol     = '<?= addslashes($rolRedireccion) ?>';
  const destino = destinos[rol] || 'dashboard.php';
  if(rl) rl.textContent = roles[rol] || rol;
  let progress=0;
  const iv=setInterval(()=>{
    progress += Math.random()*3+1.5;
    if(progress>=100){
      progress=100; bar.style.width='100%'; pct.textContent='100%';
      clearInterval(iv);
      setTimeout(()=>{window.location.href=destino;},400);
    } else {
      bar.style.width=progress+'%';
      pct.textContent=Math.floor(progress)+'%';
    }
  },50);
})();
<?php endif; ?>
</script>
</body>
</html>
