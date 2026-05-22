<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();
$db = getDB();
$user = currentUser();
$pageTitle = 'Notificaciones';

// Marcar todas como leídas
if (isset($_GET['leer_todas'])) {
    $db->query("UPDATE alertas SET leida=1");
    $db->query("UPDATE alertas_whatsapp SET leido=1");
    $_SESSION['toast'] = ['tipo'=>'success','mensaje'=>'Todas las notificaciones marcadas como leídas.'];
    header('Location: notificaciones.php'); exit();
}

// Obtener alertas del sistema
$alertas = $db->query("
    SELECT a.*, p.nombre AS producto 
    FROM alertas a 
    JOIN productos p ON a.producto_id = p.id 
    ORDER BY a.fecha DESC LIMIT 30
");

$alertas_wa = $db->query("SELECT * FROM alertas_whatsapp ORDER BY fecha DESC LIMIT 20");

$total_no_leidas = $db->query("SELECT COUNT(*) AS t FROM alertas WHERE leida=0")->fetch_assoc()['t'];
$total_wa = $db->query("SELECT COUNT(*) AS t FROM alertas_whatsapp WHERE leido=0")->fetch_assoc()['t'];
$total = $total_no_leidas + $total_wa;

include 'views/layouts/header.php';
?>

<style>
.notif-container { max-width: 750px; margin: 0 auto; }
.notif-tabs { display: flex; gap: 8px; margin-bottom: 20px; }
.notif-tab { padding: 8px 20px; border-radius: 20px; border: 2px solid var(--border); background: var(--surface); color: var(--text-muted); font-size: 13px; font-weight: 600; cursor: pointer; transition: all .2s; }
.notif-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.notif-panel { display: none; }
.notif-panel.active { display: block; }
.notif-card { background: var(--surface); border-radius: 14px; box-shadow: var(--shadow); overflow: hidden; }
.notif-item { display: flex; align-items: flex-start; gap: 14px; padding: 16px 20px; border-bottom: 1px solid var(--border); transition: background .2s; }
.notif-item:last-child { border-bottom: none; }
.notif-item.unread { background: rgba(46,117,182,0.05); border-left: 3px solid var(--accent); }
.notif-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.notif-icon.danger { background: #fee2e2; color: #dc2626; }
.notif-icon.warning { background: #fef3c7; color: #d97706; }
.notif-icon.success { background: #dcfce7; color: #16a34a; }
.notif-icon.info { background: #dbeafe; color: #2563eb; }
.notif-body { flex: 1; }
.notif-title { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 2px; }
.notif-desc { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
.notif-time { font-size: 11px; color: var(--text-muted); }
.notif-badge { font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 700; }
.notif-empty { text-align: center; padding: 48px; color: var(--text-muted); }
.notif-empty i { font-size: 48px; display: block; margin-bottom: 12px; color: #10B981; }
</style>

<div class="notif-container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
      <i class="bi bi-bell me-2"></i>Notificaciones
      <?php if ($total > 0): ?>
        <span class="badge bg-danger ms-1"><?= $total ?></span>
      <?php endif; ?>
    </h5>
    <?php if ($total > 0): ?>
      <a href="?leer_todas=1" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-check-all me-1"></i>Marcar todas como leídas
      </a>
    <?php endif; ?>
  </div>

  <!-- TABS -->
  <div class="notif-tabs">
    <button class="notif-tab active" onclick="switchTab('sistema')">
      <i class="bi bi-bell me-1"></i>Sistema
      <?php if ($total_no_leidas > 0): ?><span class="badge bg-danger ms-1"><?= $total_no_leidas ?></span><?php endif; ?>
    </button>
    <button class="notif-tab" onclick="switchTab('whatsapp')">
      <i class="bi bi-whatsapp me-1"></i>WhatsApp
      <?php if ($total_wa > 0): ?><span class="badge bg-success ms-1"><?= $total_wa ?></span><?php endif; ?>
    </button>
  </div>

  <!-- PANEL SISTEMA -->
  <div class="notif-panel active" id="panel-sistema">
    <div class="notif-card">
      <?php
      $cnt = 0;
      while ($a = $alertas->fetch_assoc()):
        $cnt++;
        $icon = $a['tipo'] === 'stock_bajo' ? 'danger' : 'warning';
        $ico  = $a['tipo'] === 'stock_bajo' ? 'bi-exclamation-triangle-fill' : 'bi-calendar-x-fill';
        $tipo_label = $a['tipo'] === 'stock_bajo' ? 'Stock Bajo' : 'Por Vencer';
      ?>
      <div class="notif-item <?= !$a['leida'] ? 'unread' : '' ?>">
        <div class="notif-icon <?= $icon ?>"><i class="bi <?= $ico ?>"></i></div>
        <div class="notif-body">
          <div class="notif-title">
            <?= htmlspecialchars($a['producto']) ?>
            <span class="notif-badge bg-<?= $icon === 'danger' ? 'danger' : 'warning text-dark' ?> ms-1"><?= $tipo_label ?></span>
          </div>
          <div class="notif-desc"><?= htmlspecialchars($a['mensaje']) ?></div>
          <div class="notif-time"><i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($a['fecha'])) ?></div>
        </div>
        <?php if (!$a['leida']): ?>
          <span style="width:8px;height:8px;background:var(--accent);border-radius:50%;flex-shrink:0;margin-top:6px;"></span>
        <?php endif; ?>
      </div>
      <?php endwhile; ?>
      <?php if ($cnt === 0): ?>
      <div class="notif-empty"><i class="bi bi-check-circle-fill"></i><p>Sin notificaciones del sistema</p></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- PANEL WHATSAPP -->
  <div class="notif-panel" id="panel-whatsapp">
    <div class="notif-card">
      <?php
      $cnt2 = 0;
      while ($w = $alertas_wa->fetch_assoc()):
        $cnt2++;
        $icon = $w['tipo'] === 'stock_bajo' ? 'danger' : 'warning';
        $ico  = $w['tipo'] === 'stock_bajo' ? 'bi-exclamation-triangle-fill' : 'bi-calendar-x-fill';
      ?>
      <div class="notif-item <?= !$w['leido'] ? 'unread' : '' ?>">
        <div class="notif-icon <?= $icon ?>"><i class="bi <?= $ico ?>"></i></div>
        <div class="notif-body">
          <div class="notif-title">
            <?= $w['tipo'] === 'stock_bajo' ? 'Alerta de Stock Bajo' : 'Producto por Vencer' ?>
            <span class="notif-badge bg-success ms-1">WhatsApp</span>
          </div>
          <div class="notif-desc"><?= htmlspecialchars($w['mensaje']) ?></div>
          <div class="notif-time"><i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($w['fecha'])) ?></div>
        </div>
        <?php if (!$w['leido']): ?>
          <span style="width:8px;height:8px;background:#25d366;border-radius:50%;flex-shrink:0;margin-top:6px;"></span>
        <?php endif; ?>
      </div>
      <?php endwhile; ?>
      <?php if ($cnt2 === 0): ?>
      <div class="notif-empty"><i class="bi bi-check-circle-fill"></i><p>Sin notificaciones de WhatsApp</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function switchTab(tab) {
  document.querySelectorAll('.notif-tab').forEach((t,i) => t.classList.toggle('active', ['sistema','whatsapp'][i] === tab));
  document.querySelectorAll('.notif-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-' + tab).classList.add('active');
}
</script>

<?php include 'views/layouts/footer.php'; ?>
