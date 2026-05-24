<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$db = getDB();
$pageTitle = 'WhatsApp — Alertas';

// Marcar como leída
if (isset($_GET['leer_todas'])) {
    $db->query("UPDATE alertas_whatsapp SET leido=1");
    header('Location: whatsapp.php'); exit();
}

// Obtener alertas de n8n
$alertas = $db->query("SELECT * FROM alertas_whatsapp ORDER BY id ASC");
$total_no_leidas = $db->query("SELECT COUNT(*) AS t FROM alertas_whatsapp WHERE leido=0")->fetch_assoc()['t'];

include '../views/layouts/header.php';
?>

<style>
.wa-container {
    max-width: 700px;
    margin: 0 auto;
    background: #f0f2f5;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
    min-height: 600px;
    display: flex;
    flex-direction: column;
}
.wa-header {
    background: #075e54;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    color: #fff;
}
.wa-avatar {
    width: 44px; height: 44px;
    background: #25d366;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
}
.wa-header-info .name { font-weight: 700; font-size: 16px; }
.wa-header-info .status { font-size: 12px; opacity: .8; }
.wa-header-actions { margin-left: auto; display: flex; gap: 10px; }
.wa-header-actions a {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12px;
    transition: all .2s;
}
.wa-header-actions a:hover { background: rgba(255,255,255,0.2); color: #fff; }

.wa-messages {
    flex: 1;
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
    background: #e5ddd5;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23000000' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.wa-date-divider {
    text-align: center;
    margin: 8px 0;
}
.wa-date-divider span {
    background: rgba(225,245,254,0.92);
    color: #555;
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.wa-bubble {
    max-width: 85%;
    align-self: flex-start;
    background: #fff;
    border-radius: 0 12px 12px 12px;
    padding: 10px 14px 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    position: relative;
}
.wa-bubble::before {
    content: '';
    position: absolute;
    top: 0; left: -8px;
    border-width: 0 8px 8px 0;
    border-style: solid;
    border-color: transparent #fff transparent transparent;
}
.wa-bubble.nueva { border-left: 3px solid #25d366; }

.wa-bubble-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.wa-bubble-icon { font-size: 18px; }
.wa-bubble-type {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.wa-bubble-type.stock { color: #e53935; }
.wa-bubble-type.venc { color: #f57c00; }

.wa-bubble-body { font-size: 13px; color: #1a1a1a; line-height: 1.5; }

.wa-factura {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 10px 12px;
    margin-top: 8px;
    font-size: 12px;
}
.wa-factura-title {
    font-weight: 700;
    color: #075e54;
    font-size: 12px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 6px;
}
.wa-factura table { width: 100%; border-collapse: collapse; }
.wa-factura th {
    font-size: 10px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 3px 4px;
    border-bottom: 1px solid #eee;
}
.wa-factura td { padding: 4px; font-size: 11px; color: #333; }
.wa-factura tr:nth-child(even) td { background: #f0f7f0; }
.badge-critico { background: #ffebee; color: #c62828; border-radius: 4px; padding: 1px 6px; font-size: 10px; font-weight: 700; }
.badge-alerta  { background: #fff3e0; color: #e65100; border-radius: 4px; padding: 1px 6px; font-size: 10px; font-weight: 700; }

.wa-time {
    text-align: right;
    font-size: 10px;
    color: #999;
    margin-top: 4px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
}
.wa-time .check { color: #25d366; }

.wa-empty {
    text-align: center;
    padding: 60px 20px;
    color: #888;
}
.wa-empty i { font-size: 48px; color: #25d366; margin-bottom: 12px; display: block; }

.wa-badge {
    background: #25d366;
    color: #fff;
    border-radius: 50%;
    width: 20px; height: 20px;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-whatsapp text-success me-2"></i>Módulo WhatsApp</h5>
    <?php if ($total_no_leidas > 0): ?>
        <a href="?leer_todas=1" class="btn btn-sm btn-outline-success">
            <i class="bi bi-check-all me-1"></i>Marcar todas como leídas
        </a>
    <?php endif; ?>
</div>

<div class="wa-container">
    <div class="wa-header">
        <div class="wa-avatar">📱</div>
        <div class="wa-header-info">
            <div class="name">NEXSYS Alertas</div>
            <div class="status">🟢 Bot activo · Cada 8 horas</div>
        </div>
        <div class="wa-header-actions">
            <?php if ($total_no_leidas > 0): ?>
                <span class="wa-badge"><?= $total_no_leidas ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="wa-messages" id="waMessages">
        <?php
        $filas = [];
        while ($a = $alertas->fetch_assoc()) $filas[] = $a;

        if (empty($filas)):
        ?>
        <div class="wa-empty">
            <i class="bi bi-check-circle-fill"></i>
            <p>No hay alertas registradas.</p>
            <small>Las alertas aparecerán aquí cuando n8n detecte stock bajo o productos por vencer.</small>
        </div>
        <?php else:
            $fecha_actual = '';
            foreach ($filas as $a):
                $fecha_msg = date('d/m/Y', strtotime($a['fecha']));
                if ($fecha_msg !== $fecha_actual):
                    $fecha_actual = $fecha_msg;
                    $label = $fecha_msg === date('d/m/Y') ? 'HOY' : ($fecha_msg === date('d/m/Y', strtotime('-1 day')) ? 'AYER' : $fecha_msg);
        ?>
        <div class="wa-date-divider"><span><?= $label ?></span></div>
        <?php endif; ?>

        <div class="wa-bubble <?= !$a['leido'] ? 'nueva' : '' ?>">
            <div class="wa-bubble-header">
                <?php if ($a['tipo'] === 'stock_bajo'): ?>
                    <span class="wa-bubble-icon">⚠️</span>
                    <span class="wa-bubble-type stock">Stock Bajo</span>
                <?php else: ?>
                    <span class="wa-bubble-icon">📅</span>
                    <span class="wa-bubble-type venc">Producto por Vencer</span>
                <?php endif; ?>
                <?php if (!$a['leido']): ?>
                    <span class="badge bg-success ms-auto" style="font-size:9px">NUEVO</span>
                <?php endif; ?>
            </div>

            <div class="wa-bubble-body">
                <?= htmlspecialchars($a['mensaje']) ?>
            </div>

            <div class="wa-time">
                <?= date('h:i a', strtotime($a['fecha'])) ?>
                <?php if ($a['leido']): ?>
                    <span class="check">✓✓</span>
                <?php else: ?>
                    <span>✓</span>
                <?php endif; ?>
            </div>
        </div>

        <?php endforeach; endif; ?>
    </div>
</div>

<script>
// Auto-scroll al último mensaje
const msgs = document.getElementById('waMessages');
if (msgs) msgs.scrollTop = msgs.scrollHeight;
</script>

<?php include '../views/layouts/footer.php'; ?>
