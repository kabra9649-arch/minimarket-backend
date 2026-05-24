<?php
require_once __DIR__ . '/../../config/session.php';
requireLogin();
$user = currentUser();
require_once __DIR__ . '/toast.php';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXSYS — <?= $pageTitle ?? 'Sistema' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* ════════════════════════════════════════
   MODO CLARO
   ════════════════════════════════════════ */
:root {
  --sidebar-width: 240px;
  --primary: #1F4E79;
  --accent:  #2E75B6;
  --bg:          #F0F4F8;
  --surface:     #ffffff;
  --surface-2:   #F8FAFC;
  --border:      #E2E8F0;
  --text:        #1a1a2e;
  --text-muted:  #6B7280;
  --topbar-bg:   #ffffff;
  --input-bg:    #F9FAFB;
  --shadow:      0 2px 8px rgba(0,0,0,.06);
  --alert-bg:    #FFF3CD;
}

/* ════════════════════════════════════════
   MODO NOCTURNO
   ════════════════════════════════════════ */
[data-theme="dark"] {
  --bg:          #0d1b2a;
  --surface:     #142233;
  --surface-2:   #1a2d42;
  --border:      #1e3a52;
  --text:        #d6e4f0;
  --text-muted:  #6e9ab8;
  --topbar-bg:   #0f1e2e;
  --input-bg:    #1a2d42;
  --shadow:      0 2px 12px rgba(0,0,0,.4);
  --alert-bg:    rgba(245,158,11,0.12);
}

*, *::before, *::after { box-sizing: border-box; }
body, .topbar, .sidebar, .card, .card-header,
.form-control, .form-select, .input-group-text,
.table, .modal-content, .dropdown-menu {
  transition: background-color .3s ease, border-color .3s ease, color .2s ease;
}
body { background: var(--bg); font-family: 'Segoe UI', sans-serif; color: var(--text); }

/* ── SIDEBAR ── */
.sidebar {
  position: fixed; top: 0; left: 0; height: 100vh;
  width: var(--sidebar-width); background: var(--primary);
  z-index: 1000; overflow-y: auto;
}
[data-theme="dark"] .sidebar { background: #0a1628; border-right: 1px solid var(--border); }
.sidebar-brand { padding: 20px 16px; border-bottom: 1px solid rgba(255,255,255,.1); }
.sidebar-brand h6 { color: #fff; margin: 0; font-weight: 700; font-size: 15px; }
.sidebar-brand small { color: rgba(255,255,255,.6); font-size: 11px; }
[data-theme="dark"] .sidebar-brand small { color: var(--text-muted); }
.sidebar-nav { padding: 12px 0; }
.nav-section { padding: 8px 16px 4px; color: rgba(255,255,255,.4); font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
[data-theme="dark"] .nav-section { color: #3d6b8f; }
.sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: rgba(255,255,255,.8); text-decoration: none; font-size: 14px; transition: all .2s; border-left: 3px solid transparent; }
.sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,.1); color: #fff; border-left-color: #F59E0B; }
[data-theme="dark"] .sidebar-nav a:hover,
[data-theme="dark"] .sidebar-nav a.active { background: rgba(46,117,182,0.15); border-left-color: #F59E0B; }
.sidebar-nav a i { font-size: 18px; width: 22px; }

/* ── MAIN ── */
.main-content { margin-left: var(--sidebar-width); min-height: 100vh; }

/* ── TOPBAR ── */
.topbar { background: var(--topbar-bg); padding: 12px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
.topbar h5 { margin: 0; font-weight: 600; color: var(--primary); font-size: 16px; }
[data-theme="dark"] .topbar h5 { color: #6eafd4; }

/* ── CONTENT ── */
.content-area { padding: 24px; }
.badge-rol { font-size: 11px; padding: 4px 10px; border-radius: 20px; }

/* ── CARDS ── */
.card { border: none; border-radius: 12px; box-shadow: var(--shadow); background: var(--surface); }
[data-theme="dark"] .card { border: 1px solid var(--border); }
.card-header { background: var(--primary); color: #fff; border-radius: 12px 12px 0 0 !important; font-weight: 600; }
[data-theme="dark"] .card-header { background: linear-gradient(135deg, #1a3a5c, #0f2840); border-bottom: 1px solid var(--border); }

/* ── BUTTONS ── */
.btn-primary { background: var(--primary); border-color: var(--primary); }
.btn-primary:hover { background: var(--accent); border-color: var(--accent); }
[data-theme="dark"] .btn-outline-light { border-color: var(--border); color: var(--text-muted); }
[data-theme="dark"] .btn-outline-light:hover { background: rgba(46,117,182,0.2); color: #fff; }
[data-theme="dark"] .btn-outline-secondary { border-color: var(--border); color: var(--text-muted); }

/* ── TABLES ── */
table thead { background: var(--surface-2); }
[data-theme="dark"] table { color: var(--text); border-color: var(--border); }
[data-theme="dark"] .table > :not(caption) > * > * { background: transparent; border-color: var(--border); color: var(--text); }
[data-theme="dark"] .table-hover tbody tr:hover td { background: rgba(46,117,182,0.08) !important; }
[data-theme="dark"] thead tr { background: var(--surface-2); }

/* ── FORMS ── */
[data-theme="dark"] .form-control,
[data-theme="dark"] .form-select { background: var(--input-bg); border-color: var(--border); color: var(--text); }
[data-theme="dark"] .form-control:focus,
[data-theme="dark"] .form-select:focus { background: var(--input-bg); border-color: var(--accent); color: var(--text); box-shadow: 0 0 0 3px rgba(46,117,182,0.2); }
[data-theme="dark"] .input-group-text { background: var(--input-bg); border-color: var(--border); color: var(--text-muted); }
[data-theme="dark"] .form-label { color: var(--text); }

/* ── TEXT ── */
[data-theme="dark"] .text-muted { color: var(--text-muted) !important; }
[data-theme="dark"] .text-dark  { color: var(--text) !important; }
[data-theme="dark"] small { color: var(--text-muted); }

/* ── ALERTS ── */
.alert-mini { background: var(--alert-bg); border-left: 4px solid #F59E0B; border-radius: 4px; padding: 8px 12px; font-size: 13px; }
[data-theme="dark"] .alert { background: rgba(46,117,182,0.08); border-color: var(--border); color: var(--text); }
[data-theme="dark"] .alert-danger { background: rgba(192,0,0,0.12); border-color: rgba(192,0,0,0.3); }
[data-theme="dark"] .alert-success { background: rgba(15,110,86,0.12); border-color: rgba(15,110,86,0.3); }

/* ── BADGES ── */
[data-theme="dark"] .badge.bg-light { background: var(--surface-2) !important; color: var(--text) !important; }
[data-theme="dark"] .badge.bg-secondary { background: #1e3a52 !important; }

/* ── MODALS ── */
[data-theme="dark"] .modal-content { background: var(--surface); color: var(--text); border-color: var(--border); }
[data-theme="dark"] .modal-header, [data-theme="dark"] .modal-footer { border-color: var(--border); }

/* ── DROPDOWNS ── */
[data-theme="dark"] .dropdown-menu { background: var(--surface); border-color: var(--border); }
[data-theme="dark"] .dropdown-item { color: var(--text); }
[data-theme="dark"] .dropdown-item:hover { background: rgba(46,117,182,0.12); }

/* ── INDICADOR API ── */
.api-status {
  display: flex; align-items: center; gap: 5px;
  font-size: 11px; font-weight: 600; padding: 4px 10px;
  border-radius: 20px; background: var(--surface-2);
  border: 1px solid var(--border); cursor: default; color: var(--text);
}
.api-dot { width: 8px; height: 8px; border-radius: 50%; background: #9CA3AF; animation: pulse-gray 2s infinite; }
.api-dot.online  { background: #10B981; animation: pulse-green 2s infinite; }
.api-dot.offline { background: #EF4444; animation: none; }
@keyframes pulse-green { 0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,0.4)} 50%{box-shadow:0 0 0 4px rgba(16,185,129,0)} }
@keyframes pulse-gray  { 0%,100%{opacity:1} 50%{opacity:.4} }

/* ── TOGGLE TEMA ── */
.theme-toggle {
  width: 38px; height: 22px; border-radius: 11px;
  background: #CBD5E0; border: none; position: relative;
  cursor: pointer; transition: background .3s; padding: 0; flex-shrink: 0;
}
.theme-toggle.dark-on { background: linear-gradient(135deg, #1F4E79, #2E75B6); }
.theme-toggle::after {
  content: ''; position: absolute; top: 3px; left: 3px;
  width: 16px; height: 16px; border-radius: 50%;
  background: #fff; transition: transform .3s; box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.theme-toggle.dark-on::after { transform: translateX(16px); }
.theme-label { font-size: 11px; font-weight: 600; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }
.theme-label i { font-size: 13px; }

/* ── MOBILE ── */
.mobile-menu-btn { display: flex; background: none; border: none; color: var(--primary); font-size: 22px; cursor: pointer; padding: 4px; }
[data-theme="dark"] .mobile-menu-btn { color: #6eafd4; }
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 999; }
.sidebar-overlay.open { display: block; }
.sidebar { transform: translateX(-100%); transition: transform .3s; }
.sidebar.open { transform: translateX(0); }
.main-content { margin-left: 0; }

@media (min-width: 769px) {
  .sidebar { transform: translateX(0); }
  .main-content { margin-left: var(--sidebar-width); }
}
@media (max-width: 768px) {
  .topbar { padding: 10px 16px; }
  .content-area { padding: 12px; }
  #apiLabel { display: none; }
}

/* ════════════════════════════════════════
   PANEL DE USUARIO DESLIZABLE
   ════════════════════════════════════════ */
.user-panel-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.45); z-index: 2000;
  backdrop-filter: blur(3px);
}
.user-panel-overlay.open { display: block; }

.user-panel {
  position: fixed; top: 0; right: -320px;
  width: 300px; height: 100vh;
  background: var(--surface);
  z-index: 2001;
  box-shadow: -8px 0 40px rgba(0,0,0,0.2);
  transition: right .32s cubic-bezier(0.4,0,0.2,1);
  display: flex; flex-direction: column;
  overflow: hidden;
  border-left: 1px solid var(--border);
}
.user-panel.open { right: 0; }

/* Cabecera */
.up-header {
  background: linear-gradient(135deg, #0a1628 0%, #1F4E79 100%);
  padding: 24px 20px 20px;
  position: relative; flex-shrink: 0;
}
.up-close {
  position: absolute; top: 12px; right: 12px;
  width: 28px; height: 28px; border-radius: 50%;
  background: rgba(255,255,255,0.1); border: none;
  color: rgba(255,255,255,0.7); font-size: 15px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background .2s;
}
.up-close:hover { background: rgba(255,255,255,0.22); color: #fff; }

.up-avatar {
  width: 56px; height: 56px; border-radius: 50%;
  background: linear-gradient(135deg, #2E75B6, #F59E0B);
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 700; color: #fff;
  margin-bottom: 12px;
  border: 3px solid rgba(255,255,255,0.2);
  box-shadow: 0 4px 16px rgba(0,0,0,0.25);
}
.up-name { color: #fff; font-weight: 700; font-size: 15px; margin: 0 0 2px; }
.up-email-txt { color: rgba(255,255,255,0.5); font-size: 11px; margin: 0 0 8px; }
.up-role-badge {
  display: inline-block; font-size: 10px; font-weight: 600;
  padding: 2px 10px; border-radius: 20px;
  background: rgba(245,158,11,0.2); color: #F59E0B;
  border: 1px solid rgba(245,158,11,0.35);
  text-transform: uppercase; letter-spacing: 1px;
}

/* Cuerpo */
.up-body { flex: 1; overflow-y: auto; padding: 6px 0; }

.up-section-label {
  font-size: 10px; font-weight: 700; letter-spacing: 1.2px;
  text-transform: uppercase; color: var(--text-muted);
  padding: 12px 20px 4px; margin: 0;
}
.up-item {
  display: flex; align-items: center; gap: 14px;
  padding: 10px 20px; cursor: pointer;
  font-size: 13px; font-weight: 500; color: var(--text);
  text-decoration: none; border: none; background: none;
  width: 100%; text-align: left;
  transition: background .15s;
}
.up-item:hover { background: var(--surface-2); color: var(--text); text-decoration: none; }

.up-icon {
  width: 34px; height: 34px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; flex-shrink: 0;
}
.up-icon.blue   { background: rgba(46,117,182,0.12); color: #2E75B6; }
.up-icon.green  { background: rgba(16,185,129,0.12); color: #10B981; }
.up-icon.yellow { background: rgba(245,158,11,0.12); color: #F59E0B; }
.up-icon.purple { background: rgba(139,92,246,0.12); color: #8B5CF6; }
.up-icon.teal   { background: rgba(20,184,166,0.12); color: #14B8A6; }

.up-arrow { margin-left: auto; color: var(--text-muted); font-size: 12px; opacity: .5; }
.up-divider { height: 1px; background: var(--border); margin: 4px 20px; }

/* Pie */
.up-footer {
  flex-shrink: 0; padding: 12px 16px;
  border-top: 1px solid var(--border);
  background: var(--surface-2);
}
.up-logout {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; padding: 10px; border-radius: 10px;
  background: linear-gradient(135deg, #b91c1c, #ef4444);
  color: #fff; font-size: 13px; font-weight: 700;
  border: none; cursor: pointer; text-decoration: none;
  transition: opacity .2s;
}
.up-logout:hover { opacity: .88; color: #fff; }

/* Botón avatar en topbar */
.user-avatar-btn {
  width: 34px; height: 34px; border-radius: 50%;
  background: linear-gradient(135deg, #1F4E79, #2E75B6);
  border: 2px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 14px; font-weight: 700;
  cursor: pointer; transition: all .2s; flex-shrink: 0;
}
.user-avatar-btn:hover {
  transform: scale(1.08);
  box-shadow: 0 0 0 3px rgba(46,117,182,0.3);
}
</style>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#1F4E79">
<link rel="apple-touch-icon" href="/uploads/icon-192.png">
<script>
(function(){
  const t = localStorage.getItem('mm-theme') || 'light';
  document.documentElement.setAttribute('data-theme', t);
})();
if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/service-worker.js'); }
</script>
</head>
<body>

<!-- ── OVERLAY PANEL USUARIO ── -->
<div class="user-panel-overlay" id="userPanelOverlay" onclick="closeUserPanel()"></div>

<!-- ══════════════════════════════════════
     PANEL DESLIZABLE DE USUARIO
     ══════════════════════════════════════ -->
<div class="user-panel" id="userPanel">

  <!-- Cabecera -->
  <div class="up-header">
    <button class="up-close" onclick="closeUserPanel()"><i class="bi bi-x-lg"></i></button>
    <div class="up-avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
    <div class="up-name"><?= htmlspecialchars($user['nombre']) ?></div>
    <div class="up-email-txt"><?= htmlspecialchars($user['email'] ?? '') ?></div>
    <div class="up-role-badge"><?= ucfirst($user['rol']) ?></div>
  </div>

  <!-- Cuerpo -->
  <div class="up-body">

    <p class="up-section-label">Mi cuenta</p>

    <a href="/perfil.php" class="up-item">
      <span class="up-icon blue"><i class="bi bi-person-circle"></i></span>
      <span>Mi Perfil</span>
      <i class="bi bi-chevron-right up-arrow"></i>
    </a>

    <button class="up-item" onclick="toggleTheme()">
      <span class="up-icon purple"><i class="bi bi-moon-stars" id="upThemeIcon"></i></span>
      <span id="upThemeLabel">Modo Nocturno</span>
      <i class="bi bi-chevron-right up-arrow"></i>
    </button>

    <div class="up-divider"></div>
    <p class="up-section-label">Sistema</p>

    <a href="/configuracion.php" class="up-item">
      <span class="up-icon yellow"><i class="bi bi-gear"></i></span>
      <span>Configuración</span>
      <i class="bi bi-chevron-right up-arrow"></i>
    </a>

    <a href="/notificaciones.php" class="up-item">
      <span class="up-icon teal"><i class="bi bi-bell"></i></span>
      <span>Notificaciones</span>
      <i class="bi bi-chevron-right up-arrow"></i>
    </a>

    <?php if ($user['rol'] === 'administrador'): ?>
    <a href="/usuarios/index.php" class="up-item">
      <span class="up-icon green"><i class="bi bi-people"></i></span>
      <span>Gestión de Usuarios</span>
      <i class="bi bi-chevron-right up-arrow"></i>
    </a>
    <?php endif; ?>
  </div>

  <!-- Pie: cerrar sesión -->
  <div class="up-footer">
    <a href="/logout.php" class="up-logout">
      <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
    </a>
  </div>
</div>

<!-- ── SIDEBAR OVERLAY ── -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ── SIDEBAR ── -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <h6><img src="https://res.cloudinary.com/dutvxrjml/image/upload/v1778966199/nexsys_1_f0cpoe.png" style="height:32px;margin-right:8px;vertical-align:middle;">NEXSYS</h6>
    <small>Sistema de Gestión</small>
  </div>
  <div class="sidebar-nav">
    <?php $self = $_SERVER['PHP_SELF']; $rol = $user['rol']; ?>

    <?php if ($rol === 'cajero'): ?>
      <div class="nav-section">Principal</div>
      <a href="/dashboard.php" class="<?= basename($self)=='dashboard.php'?'active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>

      <div class="nav-section">Ventas</div>
      <a href="/ventas/nueva_venta.php" class="<?= basename($self)=='nueva_venta.php'?'active':'' ?>"><i class="bi bi-plus-circle"></i> Nueva Venta</a>
      <a href="/ventas/index.php" class="<?= basename($self)=='index.php'&&strpos($self,'ventas')!==false?'active':'' ?>"><i class="bi bi-clock-history"></i> Historial Ventas</a>

      <div class="nav-section">Clientes</div>
      <a href="/clientes/crear.php" class="<?= basename($self)=='crear.php'&&strpos($self,'clientes')!==false?'active':'' ?>"><i class="bi bi-person-plus"></i> Nuevo Cliente</a>
      <a href="/clientes/index.php" class="<?= basename($self)=='index.php'&&strpos($self,'clientes')!==false?'active':'' ?>"><i class="bi bi-people"></i> Clientes</a>

      <div class="nav-section">Inventario</div>
      <a href="/productos/index.php" class="<?= strpos($self,'productos')!==false?'active':'' ?>"><i class="bi bi-box-seam"></i> Buscar Producto</a>

      <div class="nav-section">Mi Turno</div>
      <a href="/ventas/index.php?cajero=<?= $user['id'] ?>" class="<?= '' ?>"><i class="bi bi-person-check"></i> Mis Ventas</a>
      <a href="/ventas/metodos.php" class="<?= basename($self)=='metodos.php'?'active':'' ?>"><i class="bi bi-credit-card"></i> Métodos de Pago</a>

    <?php else: ?>
      <div class="nav-section">Principal</div>
      <a href="/dashboard.php" class="<?= basename($self)=='dashboard.php'?'active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <div class="nav-section">Inventario</div>
      <a href="/productos/index.php" class="<?= strpos($self,'productos')!==false?'active':'' ?>"><i class="bi bi-box-seam"></i> Productos</a>
      <a href="/categorias/index.php" class="<?= strpos($self,'categorias')!==false?'active':'' ?>"><i class="bi bi-tags"></i> Categorías</a>
      <a href="/proveedores/index.php" class="<?= strpos($self,'proveedores')!==false?'active':'' ?>"><i class="bi bi-truck"></i> Proveedores</a>
      <a href="/compras/index.php" class="<?= strpos($self,'compras')!==false?'active':'' ?>"><i class="bi bi-cart-plus"></i> Compras</a>
      <div class="nav-section">Ventas</div>
      <a href="/ventas/index.php" class="<?= strpos($self,'ventas')!==false?'active':'' ?>"><i class="bi bi-bag-plus"></i> Ventas</a>
      <a href="/clientes/index.php" class="<?= strpos($self,'clientes')!==false?'active':'' ?>"><i class="bi bi-people"></i> Clientes</a>
      <div class="nav-section">Pedidos</div>
      <a href="/pedidos/nuevo.php" class="<?= basename($self)=='nuevo.php'&&strpos($self,'pedidos')!==false?'active':'' ?>"><i class="bi bi-bag-plus"></i> Nuevo Pedido</a>
      <a href="/pedidos/index.php?tipo=mostrador" class="<?= basename($self)=='index.php'&&strpos($self,'pedidos')!==false?'active':'' ?>"><i class="bi bi-list-check"></i> Pedidos Mostrador</a>
      <a href="/pedidos/index.php?tipo=domicilio"><i class="bi bi-bicycle"></i> Pedidos Domicilio</a>
      <div class="nav-section">Reportes</div>
      <a href="/reportes/ventas.php" class="<?= strpos($self,'reportes/ventas')!==false?'active':'' ?>"><i class="bi bi-bar-chart"></i> Reporte Ventas</a>
      <a href="/reportes/inventario.php" class="<?= strpos($self,'inventario')!==false?'active':'' ?>"><i class="bi bi-clipboard-data"></i> Reporte Inventario</a>
      <a href="/alertas/index.php" class="<?= strpos($self,'alertas')!==false?'active':'' ?>"><i class="bi bi-bell"></i> Alertas</a>
      <div class="nav-section">Comunicación</div>
      <a href="/whatsapp/whatsapp.php" class="<?= strpos($self,'whatsapp')!==false?'active':'' ?>"><i class="bi bi-whatsapp"></i> WhatsApp</a>
      <a href="/mensajes/index.php" class="<?= strpos($self,'mensajes')!==false?'active':'' ?>">
        <i class="bi bi-chat-dots"></i> Mensajes
        <?php
        $db_menu = getDB();
        $no_leidos_menu = $db_menu->query("SELECT COUNT(*) AS t FROM mensajes WHERE leido=0")->fetch_assoc()['t'];
        if ($no_leidos_menu > 0): ?>
          <span class="badge bg-danger ms-auto" style="font-size:10px"><?= $no_leidos_menu ?></span>
        <?php endif; ?>
      </a>
      <?php if ($rol === 'administrador' || $rol === 'gerente'): ?>
<div class="nav-section">Administración</div>
<a href="/empleados/index.php" class="<?= strpos($self,'empleados')!==false?'active':'' ?>"><i class="bi bi-person-badge"></i> Empleados</a>
<?php endif; ?>
<?php if ($rol === 'administrador'): ?>
<a href="/usuarios/index.php" class="<?= strpos($self,'usuarios')!==false?'active':'' ?>"><i class="bi bi-person-gear"></i> Usuarios</a>
<?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- ── MAIN ── -->
<div class="main-content">
  <div class="topbar">
    <div class="d-flex align-items-center gap-2">
      <button class="mobile-menu-btn" onclick="openSidebar()"><i class="bi bi-list"></i></button>
      <h5><?= $pageTitle ?? 'Dashboard' ?></h5>
    </div>
    <div class="d-flex align-items-center gap-3">

      <!-- INDICADOR API -->
      <div class="api-status" id="apiStatus" title="Estado de la API">
        <div class="api-dot" id="apiDot"></div>
        <span id="apiLabel">API...</span>
      </div>

      <!-- TOGGLE MODO NOCTURNO -->
      <label class="theme-label" title="Modo nocturno">
        <i class="bi bi-sun" id="themeIconSun"></i>
        <button class="theme-toggle" id="themeToggleBtn" onclick="toggleTheme()"></button>
        <i class="bi bi-moon" id="themeIconMoon"></i>
      </label>

      <?php $badgeColor = match($user['rol']) { 'administrador'=>'bg-primary','gerente'=>'bg-warning text-dark',default=>'bg-success' }; ?>
      <span class="badge <?= $badgeColor ?> badge-rol d-none d-md-inline"><?= ucfirst($user['rol']) ?></span>

      <!-- AVATAR → abre panel de usuario -->
      <button class="user-avatar-btn" onclick="openUserPanel()" title="Mi cuenta">
        <?= strtoupper(substr($user['nombre'], 0, 1)) ?>
      </button>

    </div>
  </div>

<?php include __DIR__ . '/nexsys_widget.php'; ?>
  <div class="content-area">

<?php include __DIR__ . '/toast.php'; ?>

<script>
/* ── API STATUS ── */
const API_URL = 'https://api-fastapi-production-fa70.up.railway.app';
function checkApiStatus() {
  fetch(API_URL + '/health', { signal: AbortSignal.timeout(5000) })
    .then(r => {
      const dot = document.getElementById('apiDot');
      const lbl = document.getElementById('apiLabel');
      if (r.ok) { dot.className='api-dot online'; lbl.textContent='API Online'; lbl.style.color='#10B981'; }
      else setOffline();
    }).catch(() => setOffline());
}
function setOffline() {
  document.getElementById('apiDot').className = 'api-dot offline';
  const lbl = document.getElementById('apiLabel');
  lbl.textContent = 'API Offline'; lbl.style.color = '#EF4444';
}
checkApiStatus();
setInterval(checkApiStatus, 30000);

/* ── TEMA ── */
function applyTheme(t) {
  document.documentElement.setAttribute('data-theme', t);
  const btn = document.getElementById('themeToggleBtn');
  if (btn) btn.classList.toggle('dark-on', t === 'dark');
  // Sincronizar icono y texto dentro del panel
  const icon  = document.getElementById('upThemeIcon');
  const label = document.getElementById('upThemeLabel');
  if (icon)  icon.className    = t === 'dark' ? 'bi bi-sun'      : 'bi bi-moon-stars';
  if (label) label.textContent = t === 'dark' ? 'Modo Claro'     : 'Modo Nocturno';
}
function toggleTheme() {
  const cur  = document.documentElement.getAttribute('data-theme') || 'light';
  const next = cur === 'dark' ? 'light' : 'dark';
  localStorage.setItem('mm-theme', next);
  applyTheme(next);
}
applyTheme(localStorage.getItem('mm-theme') || 'light');

/* ── SIDEBAR MOBILE ── */
function openSidebar()  {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebarOverlay').classList.add('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
}

/* ── PANEL USUARIO ── */
function openUserPanel() {
  document.getElementById('userPanel').classList.add('open');
  document.getElementById('userPanelOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeUserPanel() {
  document.getElementById('userPanel').classList.remove('open');
  document.getElementById('userPanelOverlay').classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeUserPanel(); });
</script>
