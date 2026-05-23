<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireCliente();

$db      = getDB();
$cliente = currentCliente();

// Carrito en sesión
if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

// Acciones carrito rápido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['producto_id'] ?? 0);
    $qty = (int)($_POST['cantidad']    ?? 1);
    if ($pid > 0 && $qty > 0) {
        if (isset($_SESSION['carrito'][$pid])) {
            $_SESSION['carrito'][$pid] += $qty;
        } else {
            $_SESSION['carrito'][$pid] = $qty;
        }
    }
    header('Location: catalogo.php?agregado=1'); exit();
}

// Filtros
$buscar = trim($_GET['buscar'] ?? '');
$cat    = (int)($_GET['cat']  ?? 0);

$where = "WHERE p.activo=1 AND p.stock_actual > 0";
if ($buscar) $where .= " AND p.nombre LIKE '%".mysqli_real_escape_string($db,$buscar)."%'";
if ($cat)    $where .= " AND p.categoria_id=$cat";

$productos  = $db->query("SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON p.categoria_id=c.id $where ORDER BY p.nombre");
$categorias = $db->query("SELECT * FROM categorias ORDER BY nombre");
$totalCarrito = array_sum($_SESSION['carrito']);

// Mostrar splash solo si no viene de una acción (primera visita)
$mostrarSplash = !isset($_GET['agregado']) && !isset($_GET['buscar']) && !$cat && !isset($_GET['nosplash']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXSYS — Catálogo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root { --primary:#1F4E79; --accent:#2E75B6; --gold:#F59E0B; --bg:#F0F4F8; --card-bg:#fff; --text:#1E293B; --border:#E2E8F0; }
  [data-theme="dark"] { --bg:#0a1628; --card-bg:#0f1e2e; --text:#d6e4f0; --border:#1e3a52; }
  body  { background:var(--bg); font-family:'Segoe UI',sans-serif; color:var(--text); transition:background .3s,color .3s; }

  /* ── SPLASH SCREEN ── */
  #splash {
    position: fixed; inset: 0; z-index: 9999;
    background: linear-gradient(135deg, #06101f 0%, #0d2340 50%, #1F4E79 100%);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 0;
  }
  #splash.fade-out {
    animation: splashFade 0.6s ease forwards;
  }
  @keyframes splashFade {
    from { opacity: 1; transform: scale(1); }
    to   { opacity: 0; transform: scale(1.03); }
  }

  /* Grid de fondo */
  #splash::before {
    content: '';
    position: absolute; inset: 0;
    background-image:
      linear-gradient(rgba(46,117,182,0.07) 1px, transparent 1px),
      linear-gradient(90deg, rgba(46,117,182,0.07) 1px, transparent 1px);
    background-size: 48px 48px;
    animation: gridMove 20s linear infinite;
  }
  @keyframes gridMove { from{transform:translateY(0)} to{transform:translateY(48px)} }

  .splash-inner {
    position: relative; z-index: 1;
    display: flex; flex-direction: column;
    align-items: center; gap: 28px;
    padding: 40px;
    text-align: center;
  }

  /* Logo animado */
  .splash-logo-wrap {
    width: 120px; height: 120px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(46,117,182,0.4);
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    position: relative;
    animation: logoAppear 0.7s cubic-bezier(0.34,1.56,0.64,1) forwards;
    opacity: 0;
    box-shadow: 0 0 40px rgba(46,117,182,0.2);
  }
  @keyframes logoAppear {
    from { opacity: 0; transform: scale(0.5) rotate(-10deg); }
    to   { opacity: 1; transform: scale(1) rotate(0deg); }
  }
  .splash-logo-wrap i {
    font-size: 52px; color: #fff;
    filter: drop-shadow(0 0 12px rgba(46,117,182,0.6));
  }
  /* Esquinas doradas */
  .splash-logo-wrap::before,
  .splash-logo-wrap::after {
    content: '';
    position: absolute;
    width: 12px; height: 12px;
  }
  .splash-logo-wrap::before {
    top: 6px; left: 6px;
    border-top: 2px solid var(--gold);
    border-left: 2px solid var(--gold);
  }
  .splash-logo-wrap::after {
    bottom: 6px; right: 6px;
    border-bottom: 2px solid var(--gold);
    border-right: 2px solid var(--gold);
  }

  .splash-brand {
    animation: fadeUp 0.6s ease 0.3s forwards;
    opacity: 0;
  }
  .splash-brand h1 {
    font-size: clamp(28px, 6vw, 42px);
    font-weight: 700; letter-spacing: 6px;
    color: #fff; margin: 0;
    text-shadow: 0 0 30px rgba(46,117,182,0.5);
  }
  .splash-brand h1 span { color: var(--gold); }
  .splash-brand p {
    font-size: 11px; letter-spacing: 5px;
    color: rgba(214,228,240,0.6);
    text-transform: uppercase; margin: 6px 0 0;
  }

  .splash-welcome {
    animation: fadeUp 0.6s ease 0.55s forwards;
    opacity: 0;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 20px 36px;
  }
  .splash-welcome .greeting {
    font-size: 13px; letter-spacing: 3px;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase; margin-bottom: 8px;
  }
  .splash-welcome .name {
    font-size: clamp(22px, 4vw, 32px);
    font-weight: 700; color: #fff;
    margin: 0;
  }
  .splash-welcome .name span {
    color: var(--gold);
  }
  .splash-welcome .sub {
    font-size: 13px; color: rgba(255,255,255,0.5);
    margin: 6px 0 0;
  }

  /* Barra de progreso */
  .splash-progress {
    animation: fadeUp 0.6s ease 0.7s forwards;
    opacity: 0;
    width: 220px;
  }
  .progress-bar-wrap {
    background: rgba(255,255,255,0.1);
    border-radius: 50px; height: 4px;
    overflow: hidden;
  }
  .progress-bar-fill {
    height: 100%; width: 0%;
    background: linear-gradient(90deg, var(--accent), var(--gold));
    border-radius: 50px;
    transition: width 0.1s linear;
  }
  .progress-text {
    font-size: 11px; letter-spacing: 2px;
    color: rgba(255,255,255,0.4);
    text-align: center; margin-top: 8px;
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ── NAVBAR ── */
  .navbar-top { background:linear-gradient(135deg,var(--primary),var(--accent)); padding:10px 24px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:100; box-shadow:0 2px 12px rgba(0,0,0,.2); }
  .navbar-top .brand { color:#fff; font-weight:700; font-size:18px; text-decoration:none; }
  .navbar-top .brand i { margin-right:8px; }
  .btn-carrito { background:rgba(255,255,255,.15); color:#fff; border:2px solid rgba(255,255,255,.4); border-radius:10px; padding:6px 16px; font-weight:600; font-size:13px; text-decoration:none; transition:all .2s; }
  .btn-carrito:hover { background:#fff; color:var(--primary); }
  .user-info { color:rgba(255,255,255,.85); font-size:13px; }

  /* ── HERO ── */
  .hero { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; padding:32px 24px; text-align:center; }
  .hero h2 { font-weight:700; font-size:24px; margin:0 0 6px; }
  .hero p  { opacity:.8; font-size:14px; margin:0; }

  /* ── FILTROS ── */
  .filtros { background:#fff; padding:16px 24px; border-bottom:1px solid #E2E8F0; }

  /* ── CARDS ── */
  .prod-card { background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.07); transition:transform .2s,box-shadow .2s; height:100%; display:flex; flex-direction:column; }
  .prod-card:hover { transform:translateY(-4px); box-shadow:0 8px 24px rgba(0,0,0,.12); }
  .prod-img { width:100%; height:200px; object-fit:contain; background:#f8f9fa; padding:16px; }
  .prod-img-placeholder { width:100%; height:170px; background:linear-gradient(135deg,#E2E8F0,#CBD5E1); display:flex; align-items:center; justify-content:center; color:#94A3B8; font-size:40px; }
  .prod-body { padding:14px; flex:1; display:flex; flex-direction:column; }
  .prod-cat  { font-size:10px; font-weight:600; color:var(--accent); text-transform:uppercase; letter-spacing:.8px; margin-bottom:4px; }
  .prod-name { font-weight:700; font-size:14px; color:#1E293B; margin-bottom:6px; line-height:1.3; }
  .prod-price{ font-size:18px; font-weight:800; color:var(--primary); margin-bottom:10px; }
  .prod-stock{ font-size:11px; color:#64748B; margin-bottom:10px; }
  .btn-add   { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; border:none; border-radius:8px; padding:8px; font-size:13px; font-weight:600; width:100%; margin-top:auto; transition:opacity .2s; }
  .btn-add:hover { opacity:.85; }
  .content { padding:24px; max-width:1400px; margin:0 auto; }
</style>
</head>
<body>


<!-- TOAST CONTAINER -->
<div id="toastContainer" style="position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;min-width:300px;max-width:400px;"></div>
<style>
.toast-item{display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.18);animation:slideIn .3s ease;font-size:14px;position:relative;overflow:hidden;}
.toast-item.success{background:linear-gradient(135deg,#0F6E56,#1aad87);}
.toast-item.info{background:linear-gradient(135deg,#1F4E79,#2E75B6);}
.toast-icon{font-size:20px;flex-shrink:0;}
.toast-body{flex:1;}
.toast-title{font-weight:700;font-size:13px;margin-bottom:2px;}
.toast-msg{font-size:13px;opacity:.92;}
.toast-close{background:none;border:none;color:rgba(255,255,255,.7);font-size:18px;cursor:pointer;padding:0;}
.toast-progress{position:absolute;bottom:0;left:0;height:3px;background:rgba(255,255,255,.4);animation:progress 4s linear forwards;}
@keyframes slideIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
@keyframes slideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(120%);opacity:0}}
@keyframes progress{from{width:100%}to{width:0%}}
</style>
<script>
(function(){const t=localStorage.getItem('mm-theme')||'light';document.documentElement.setAttribute('data-theme',t);})();
function showToast(tipo,mensaje){
  const container=document.getElementById('toastContainer');
  const id='toast_'+Date.now();
  const icons={success:'bi-check-circle-fill',info:'bi-info-circle-fill'};
  const titles={success:'Éxito',info:'Información'};
  const el=document.createElement('div');
  el.id=id;el.className='toast-item '+(tipo||'info');
  el.innerHTML=`<i class="bi ${icons[tipo]||icons.info} toast-icon"></i><div class="toast-body"><div class="toast-title">${titles[tipo]||'Aviso'}</div><div class="toast-msg">${mensaje}</div></div><button class="toast-close" onclick="closeToast('${id}')">&times;</button><div class="toast-progress"></div>`;
  container.appendChild(el);
  setTimeout(()=>closeToast(id),4000);
}
function closeToast(id){const el=document.getElementById(id);if(!el)return;el.style.animation='slideOut .3s ease forwards';setTimeout(()=>el.remove(),300);}
</script>

<?php if ($mostrarSplash): ?>
<div id="splash">
  <div class="splash-inner">
    <div class="splash-logo-wrap">
      <i class="bi bi-shop"></i>
    </div>
    <div class="splash-brand">
      <h1>NEX<span>SYS</span></h1>
      <p>Sistema de Gestión Integral</p>
    </div>
    <div class="splash-welcome">
      <div class="greeting">Bienvenido al catálogo de productos</div>
      <p class="name">¡Hola, <span><?= htmlspecialchars(explode(' ', $cliente['nombre'])[0]) ?>!</span></p>
      <p class="sub">Compra desde casa, recibe en tu puerta</p>
    </div>
    <div class="splash-progress">
      <div class="progress-bar-wrap">
        <div class="progress-bar-fill" id="splashBar"></div>
      </div>
      <div class="progress-text" id="splashTxt">Cargando catálogo...</div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- NAVBAR -->
<div class="navbar-top">
  <a href="catalogo.php?nosplash=1" class="brand"><img src="https://res.cloudinary.com/dutvxrjml/image/upload/v1778966199/nexsys_1_f0cpoe.png" style="height:28px;margin-right:8px;vertical-align:middle;">NEXSYS</a>
  <div class="d-flex align-items-center gap-3">
    <span class="user-info d-none d-md-inline"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($cliente['nombre']) ?></span>
    <a href="carrito.php" class="btn-carrito">
      <i class="bi bi-cart3 me-1"></i>Carrito
      <?php if ($totalCarrito > 0): ?>
        <span class="badge bg-warning text-dark ms-1"><?= $totalCarrito ?></span>
      <?php endif; ?>
    </a>
    <a href="logout_cliente.php" class="btn-carrito" style="background:rgba(255,0,0,.2);border-color:rgba(255,100,100,.4);">
      <i class="bi bi-box-arrow-right"></i>
    </a>
    <button onclick="toggleTheme()" style="background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.4);border-radius:10px;padding:6px 12px;color:#fff;cursor:pointer;" title="Cambiar tema">
      <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
    </button>
  </div>
</div>

<!-- HERO -->
<div class="hero">
  <h2><i class="bi bi-bag-heart me-2"></i>Nuestros Productos</h2>
  <p>Compra desde casa, recibe en tu puerta</p>
</div>

<!-- FILTROS -->
<div class="filtros">
  <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
    <input type="hidden" name="nosplash" value="1">
    <div class="input-group" style="max-width:280px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar producto..." value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <select name="cat" class="form-select form-select-sm" style="max-width:180px;" onchange="this.form.submit()">
      <option value="0">Todas las categorías</option>
      <?php while ($c = $categorias->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>" <?= $cat==$c['id']?'selected':''?>><?= htmlspecialchars($c['nombre']) ?></option>
      <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
    <?php if ($buscar || $cat): ?>
      <a href="catalogo.php?nosplash=1" class="btn btn-outline-secondary btn-sm">Limpiar</a>
    <?php endif; ?>
  </form>
</div>

<!-- PRODUCTOS -->
<div class="content">
  <?php if (isset($_GET['agregado'])): ?>
  <script>document.addEventListener('DOMContentLoaded',()=>showToast('success','Producto agregado al carrito. <a href="carrito.php" style="color:#fff;font-weight:700;">Ver carrito →</a>'));</script>
  <?php endif; ?>

  <div class="row g-3">
    <?php
      $count = 0;
      while ($p = $productos->fetch_assoc()):
      $count++;
    ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="prod-card">
        <?php if ($p['imagen']): ?>
          <img src="<?= htmlspecialchars($p['imagen']) ?>" class="prod-img" alt="<?= htmlspecialchars($p['nombre']) ?>" onerror="this.parentElement.innerHTML='<div class=\'prod-img-placeholder\'><i class=\'bi bi-box-seam\'></i></div>'">
        <?php else: ?>
          <div class="prod-img-placeholder"><i class="bi bi-box-seam"></i></div>
        <?php endif; ?>
        <div class="prod-body">
          <div class="prod-cat"><?= htmlspecialchars($p['categoria']) ?></div>
          <div class="prod-name"><?= htmlspecialchars($p['nombre']) ?></div>
          <div class="prod-price">RD$ <?= number_format($p['precio_venta'],2) ?></div>
          <div class="prod-stock"><i class="bi bi-check-circle-fill text-success me-1"></i><?= $p['stock_actual'] ?> disponibles</div>
          <form method="POST">
            <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
            <div class="input-group input-group-sm mb-2">
              <span class="input-group-text">Cant.</span>
              <input type="number" name="cantidad" class="form-control" value="1" min="1" max="<?= $p['stock_actual'] ?>">
            </div>
            <button type="submit" class="btn-add"><i class="bi bi-cart-plus me-1"></i>Agregar</button>
          </form>
        </div>
      </div>
    </div>
    <?php endwhile; ?>

    <?php if ($count === 0): ?>
    <div class="col-12 text-center py-5 text-muted">
      <i class="bi bi-search fs-1 d-block mb-3"></i>
      <h5>No se encontraron productos</h5>
      <a href="catalogo.php?nosplash=1" class="btn btn-outline-primary btn-sm mt-2">Ver todos</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleTheme(){
  const cur=document.documentElement.getAttribute('data-theme')||'light';
  const next=cur==='dark'?'light':'dark';
  document.documentElement.setAttribute('data-theme',next);
  localStorage.setItem('mm-theme',next);
  document.getElementById('themeIcon').className=next==='dark'?'bi bi-sun-fill':'bi bi-moon-stars-fill';
}
(function(){
  const t=localStorage.getItem('mm-theme')||'light';
  document.documentElement.setAttribute('data-theme',t);
  const icon=document.getElementById('themeIcon');
  if(icon) icon.className=t==='dark'?'bi bi-sun-fill':'bi bi-moon-stars-fill';
})();
</script>

<?php if ($mostrarSplash): ?>
<script>
(function() {
  const bar  = document.getElementById('splashBar');
  const txt  = document.getElementById('splashTxt');
  const splash = document.getElementById('splash');
  let progress = 0;

  const messages = ['Cargando catálogo...', 'Preparando productos...', '¡Listo!'];
  let msgIdx = 0;

  const interval = setInterval(() => {
    progress += Math.random() * 5 + 2;
    if (progress >= 100) {
      progress = 100;
      bar.style.width = '100%';
      txt.textContent = '¡Listo!';
      clearInterval(interval);
      setTimeout(() => {
        splash.classList.add('fade-out');
        setTimeout(() => splash.remove(), 600);
      }, 300);
    } else {
      bar.style.width = progress + '%';
      if (progress > 40 && msgIdx === 0) { txt.textContent = messages[1]; msgIdx = 1; }
      if (progress > 80 && msgIdx === 1) { txt.textContent = messages[2]; msgIdx = 2; }
    }
  }, 50);
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/views/layouts/nexsys_widget.php'; ?>

</body>
</html>
