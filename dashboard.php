<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$db = getDB();
$pageTitle = 'Dashboard';

$ventasHoy = $db->query("SELECT COUNT(*) AS total, IFNULL(SUM(total),0) AS ingresos FROM ventas WHERE DATE(fecha)=CURDATE() AND estado='completada'")->fetch_assoc();
$stockBajo = $db->query("SELECT COUNT(*) AS total FROM productos WHERE stock_actual<=stock_minimo AND activo=1")->fetch_assoc();
$porVencer = $db->query("SELECT COUNT(*) AS total FROM productos WHERE fecha_vencimiento IS NOT NULL AND fecha_vencimiento<=DATE_ADD(CURDATE(),INTERVAL 7 DAY) AND fecha_vencimiento>=CURDATE() AND activo=1")->fetch_assoc();
$pedHoy    = $db->query("SELECT COUNT(*) AS total FROM pedidos WHERE DATE(fecha)=CURDATE()")->fetch_assoc();

$prods_bajo   = $db->query("SELECT p.nombre,p.stock_actual,p.stock_minimo FROM productos p WHERE p.stock_actual<=p.stock_minimo AND p.activo=1 LIMIT 6");
$prods_vencer = $db->query("SELECT nombre,fecha_vencimiento,DATEDIFF(fecha_vencimiento,CURDATE()) AS dias FROM productos WHERE fecha_vencimiento IS NOT NULL AND fecha_vencimiento<=DATE_ADD(CURDATE(),INTERVAL 7 DAY) AND fecha_vencimiento>=CURDATE() AND activo=1 ORDER BY dias LIMIT 6");

$data7 = [];
for ($i = 6; $i >= 0; $i--) $data7[date('d/m', strtotime("-$i days"))] = 0;
$r7 = $db->query("SELECT DATE(fecha) AS dia, IFNULL(SUM(total),0) AS total FROM ventas WHERE fecha>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) AND estado='completada' GROUP BY DATE(fecha)");
while ($r = $r7->fetch_assoc()) { $k = date('d/m', strtotime($r['dia'])); if(isset($data7[$k])) $data7[$k]=(float)$r['total']; }

$labelsV = []; $dataV = [];
$mv = $db->query("SELECT p.nombre,SUM(dv.cantidad) AS qty FROM detalle_ventas dv JOIN productos p ON dv.producto_id=p.id JOIN ventas v ON dv.venta_id=v.id WHERE v.estado='completada' GROUP BY p.id ORDER BY qty DESC LIMIT 5");
while ($r = $mv->fetch_assoc()) {
    $labelsV[] = strlen($r['nombre'])>16 ? substr($r['nombre'],0,14).'…' : $r['nombre'];
    $dataV[]   = (int)$r['qty'];
}
if (empty($dataV)) { $labelsV=['Sin ventas']; $dataV=[1]; }

$metodos = ['efectivo'=>0,'tarjeta'=>0,'transferencia'=>0];

// Ingresos por mes (año actual)
$meses = array_fill(1, 12, 0);
$rm2 = $db->query("SELECT MONTH(fecha) AS mes, IFNULL(SUM(total),0) AS total FROM ventas WHERE YEAR(fecha)=YEAR(CURDATE()) AND estado='completada' GROUP BY MONTH(fecha)");
while ($r = $rm2->fetch_assoc()) $meses[(int)$r['mes']] = (float)$r['total'];
$rm = $db->query("SELECT metodo_pago, COUNT(*) AS cnt FROM ventas WHERE DATE(fecha)>=DATE_SUB(CURDATE(),INTERVAL 30 DAY) AND estado='completada' GROUP BY metodo_pago");
while ($r = $rm->fetch_assoc()) $metodos[$r['metodo_pago']] = (int)$r['cnt'];

// Ticket promedio hoy
$ticketPromedio = $ventasHoy['total'] > 0 ? $ventasHoy['ingresos'] / $ventasHoy['total'] : 0;

// Queries para cajero

$cajero_id = $_SESSION['usuario_id'] ?? 0;
$ultimaVenta = $db->query("SELECT v.id, v.num_factura, v.total, v.metodo_pago, v.fecha, c.nombre AS cliente FROM ventas v LEFT JOIN clientes c ON v.cliente_id=c.id WHERE v.usuario_id=$cajero_id AND v.estado='completada' ORDER BY v.fecha DESC LIMIT 1")->fetch_assoc();
$topHoy = $db->query("SELECT p.nombre, SUM(dv.cantidad) AS qty, SUM(dv.subtotal) AS sub FROM detalle_ventas dv JOIN productos p ON dv.producto_id=p.id JOIN ventas v ON dv.venta_id=v.id WHERE DATE(v.fecha)=CURDATE() AND v.estado='completada' GROUP BY p.id ORDER BY qty DESC LIMIT 5");

include 'views/layouts/header.php';
?>

<style>
/* ── RESPONSIVE DASHBOARD ── */
@media (max-width: 768px) {
  .content-area { padding: 12px !important; }
  .card-body { padding: 10px !important; }
  .card-header { padding: 10px 12px !important; font-size: 13px; }
  .fs-4 { font-size: 1.3rem !important; }
  .fs-5 { font-size: 1rem !important; }
  .grafico-container { height: 160px !important; }
  .table td, .table th { font-size: 11px !important; padding: 6px 8px !important; }
}
.grafico-container { position: relative; height: 190px; }
</style>

<!-- TARJETAS -->
<?php if ($_SESSION['rol'] !== 'cajero'): ?>
<div class="row g-2 mb-2">
  <div class="col">
    <div class="card text-white" style="background:#1F4E79;">
      <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
        <div><div style="font-size:10px;opacity:.75">Ventas hoy</div><div class="fw-bold" style="font-size:18px"><?= $ventasHoy['total'] ?></div></div>
        <i class="bi bi-receipt opacity-30" style="font-size:20px"></i>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card text-white" style="background:#0F6E56;">
      <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
        <div><div style="font-size:10px;opacity:.75">Ingresos hoy</div><div class="fw-bold" style="font-size:14px">RD$ <?= number_format($ventasHoy['ingresos'],0) ?></div></div>
        <i class="bi bi-cash-stack opacity-30" style="font-size:20px"></i>
      </div>
    </div>
  </div>
  <?php if (true): // always show rest for admin ?>
  <div class="col">
    <div class="card text-white" style="background:linear-gradient(135deg,#BF5800,#e07020);">
      <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
        <div><div style="font-size:10px;opacity:.75">Ticket promedio</div><div class="fw-bold" style="font-size:14px">RD$ <?= number_format($ticketPromedio,2) ?></div></div>
        <i class="bi bi-graph-up opacity-30" style="font-size:20px"></i>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card text-white" style="background:<?= $stockBajo['total']>0?'#C00000':'#555' ?>;">
      <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
        <div><div style="font-size:10px;opacity:.75">Stock bajo</div><div class="fw-bold" style="font-size:18px"><?= $stockBajo['total'] ?></div></div>
        <i class="bi bi-exclamation-triangle opacity-30" style="font-size:20px"></i>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card text-white" style="background:#5B4FCF;">
      <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
        <div><div style="font-size:10px;opacity:.75">Pedidos hoy</div><div class="fw-bold" style="font-size:18px"><?= $pedHoy['total'] ?></div></div>
        <i class="bi bi-bag-check opacity-30" style="font-size:20px"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; // end hide for cajero ?>

<!-- GRÁFICOS -->
<?php if ($_SESSION['rol'] !== 'cajero'): ?>
<div class="row g-2 mb-3">
  <div class="col-12 col-md-5">
    <div class="card h-100">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bar-chart me-2"></i>Ventas 7 días</span>
        <span class="badge bg-light text-dark" style="font-size:10px;" id="lblActualizado"></span>
      </div>
      <div class="card-body py-2">
        <div class="grafico-container"><canvas id="grafVentas"></canvas></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="card h-100">
      <div class="card-header py-2"><i class="bi bi-pie-chart me-2"></i>Más vendidos</div>
      <div class="card-body py-2 d-flex align-items-center justify-content-center">
        <div class="grafico-container w-100"><canvas id="grafTop"></canvas></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-header py-2"><i class="bi bi-credit-card me-2"></i>Métodos pago</div>
      <div class="card-body py-2 d-flex align-items-center justify-content-center">
        <div class="grafico-container w-100"><canvas id="grafMetodos"></canvas></div>
      </div>
    </div>
  </div>
</div>
<?php endif; // end graficos for admin ?>

<!-- HISTOGRAMA INGRESOS POR MES -->
<?php if ($_SESSION['rol'] !== 'cajero'): ?>
<div class="row g-2 mb-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-graph-up-arrow me-2"></i>Ingresos por Mes <?= date('Y') ?></span>
        <span class="badge bg-light text-dark" style="font-size:10px;">Actualización en tiempo real</span>
      </div>
      <div class="card-body py-2">
        <div style="position:relative;height:220px;"><canvas id="grafMeses"></canvas></div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ALERTAS -->
<?php if ($_SESSION['rol'] !== 'cajero'): ?>
<div class="row g-2">
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header py-2 text-white" style="background:#C00000;"><i class="bi bi-exclamation-triangle me-2"></i>Stock Bajo</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead><tr><th>Producto</th><th>Stock</th><th>Mínimo</th></tr></thead>
            <tbody>
              <?php $cnt=0; while ($p=$prods_bajo->fetch_assoc()): $cnt++; ?>
              <tr>
                <td class="small"><?= htmlspecialchars($p['nombre']) ?></td>
                <td><span class="badge bg-danger"><?= $p['stock_actual'] ?></span></td>
                <td class="small"><?= $p['stock_minimo'] ?></td>
              </tr>
              <?php endwhile; ?>
              <?php if ($cnt===0): ?><tr><td colspan="3" class="text-center text-muted py-2 small">✅ Sin alertas</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header py-2 text-white" style="background:#BF5800;"><i class="bi bi-calendar-x me-2"></i>Por Vencer (7 días)</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead><tr><th>Producto</th><th>Vence</th><th>Días</th></tr></thead>
            <tbody>
              <?php $cnt2=0; while ($p=$prods_vencer->fetch_assoc()): $cnt2++; ?>
              <tr>
                <td class="small"><?= htmlspecialchars($p['nombre']) ?></td>
                <td class="small"><?= date('d/m/Y', strtotime($p['fecha_vencimiento'])) ?></td>
                <td><span class="badge bg-warning text-dark"><?= $p['dias'] ?>d</span></td>
              </tr>
              <?php endwhile; ?>
              <?php if ($cnt2===0): ?><tr><td colspan="3" class="text-center text-muted py-2 small">✅ Sin productos por vencer</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>

<!-- SECCIÓN CAJERO -->
<?php if ($_SESSION['rol'] === 'cajero'): ?>

<!-- Bienvenida cajero -->
<div class="row g-2 mb-3">
  <div class="col-12">
    <div class="card text-white" style="background:linear-gradient(135deg,#1F4E79 0%,#2E75B6 50%,#0F6E56 100%);border:none;">
      <div class="card-body py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <div style="font-size:11px;opacity:.7;letter-spacing:2px;text-transform:uppercase;">Bienvenido al sistema</div>
          <div style="font-size:22px;font-weight:700;"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Cajero') ?> <span style="opacity:.6;font-size:14px;">— <?= date('l, d \d\e F \d\e Y') ?></span></div>
          <div style="font-size:12px;opacity:.7;margin-top:2px;"><i class="bi bi-clock me-1"></i><span id="reloj-cajero"></span></div>
        </div>
        <div class="d-flex gap-2">
          <a href="/ventas/index.php" class="btn text-white fw-bold px-4" style="background:rgba(255,255,255,.2);backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,.3);">
            <i class="bi bi-plus-circle me-2"></i>Nueva Venta
          </a>
          <a href="/clientes/index.php" class="btn text-white fw-bold px-4" style="background:rgba(255,255,255,.15);backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,.2);">
            <i class="bi bi-person-plus me-2"></i>Nuevo Cliente
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- KPIs cajero -->
<div class="row g-2 mb-3">
  <div class="col-6 col-md-3">
    <div class="card text-white h-100" style="background:#1F4E79;">
      <div class="card-body py-3 d-flex justify-content-between align-items-center">
        <div>
          <div class="small opacity-75">Mis ventas hoy</div>
          <div class="fs-4 fw-bold"><?= $ventasHoy['total'] ?></div>
        </div>
        <i class="bi bi-receipt fs-2 opacity-40"></i>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-white h-100" style="background:#0F6E56;">
      <div class="card-body py-3 d-flex justify-content-between align-items-center">
        <div>
          <div class="small opacity-75">Ingresos hoy</div>
          <div class="fw-bold" style="font-size:clamp(12px,3vw,17px)">RD$ <?= number_format($ventasHoy['ingresos'],0) ?></div>
        </div>
        <i class="bi bi-cash-stack fs-2 opacity-40"></i>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-white h-100" style="background:linear-gradient(135deg,#BF5800,#e07020);">
      <div class="card-body py-3 d-flex justify-content-between align-items-center">
        <div>
          <div class="small opacity-75">Ticket promedio</div>
          <div class="fw-bold" style="font-size:clamp(12px,3vw,17px)">RD$ <?= number_format($ticketPromedio,2) ?></div>
        </div>
        <i class="bi bi-graph-up fs-2 opacity-40"></i>
      </div>
    </div>
  </div>

</div>

<!-- Más vendidos hoy (cajero) -->
<div class="row g-2 mb-3">
  <div class="col-12 col-md-8">
    <div class="card h-100">
      <div class="card-header py-2 text-white d-flex justify-content-between align-items-center" style="background:#0F6E56;">
        <span><i class="bi bi-star-fill me-2"></i>Más Vendidos Hoy</span>
        <span class="badge bg-light text-dark" style="font-size:10px;"><?= date('d/m/Y') ?></span>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead style="background:rgba(0,0,0,.05);">
            <tr>
              <th style="width:40px;" class="ps-3">#</th>
              <th>Producto</th>
              <th class="text-center">Unidades</th>
              <th class="text-end pe-3">Subtotal</th>
              <th class="text-end pe-3">Barra</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $topHoyArr = [];
              while($tp=$topHoy->fetch_assoc()) $topHoyArr[] = $tp;
              $maxQty = !empty($topHoyArr) ? max(array_column($topHoyArr,'qty')) : 1;
              foreach($topHoyArr as $rank => $tp):
              $pct = $maxQty > 0 ? round($tp['qty']/$maxQty*100) : 0;
              $colors = ['#1F4E79','#2E75B6','#0F6E56','#BF5800','#5B4FCF'];
              $color = $colors[$rank] ?? '#888';
            ?>
            <tr>
              <td class="ps-3"><span class="badge rounded-pill" style="background:<?= $color ?>;min-width:24px;"><?= $rank+1 ?></span></td>
              <td class="fw-semibold small"><?= htmlspecialchars($tp['nombre']) ?></td>
              <td class="text-center"><span class="badge bg-light text-dark border"><?= $tp['qty'] ?> uds</span></td>
              <td class="text-end pe-3 small fw-bold">RD$ <?= number_format($tp['sub'],2) ?></td>
              <td class="text-end pe-3" style="width:120px;">
                <div style="background:#e9ecef;border-radius:4px;height:8px;overflow:hidden;">
                  <div style="width:<?= $pct ?>%;background:<?= $color ?>;height:100%;border-radius:4px;transition:width .5s;"></div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($topHoyArr)): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">
              <i class="bi bi-bar-chart fs-2 d-block mb-2 opacity-25"></i>
              Sin ventas registradas hoy
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Clima/Hora + Mensaje motivacional -->
  <div class="col-12 col-md-4">
    <div class="row g-2 h-100">
      <!-- Reloj -->
      <div class="col-12">
        <div class="card text-white" style="background:linear-gradient(135deg,#1a1a2e,#16213e);">
          <div class="card-body text-center py-3">
            <div style="font-size:11px;letter-spacing:3px;opacity:.6;text-transform:uppercase;">Hora actual</div>
            <div id="reloj-grande" style="font-size:36px;font-weight:800;font-family:monospace;letter-spacing:2px;color:#2E75B6;"></div>
            <div id="fecha-cajero" style="font-size:12px;opacity:.6;"></div>
          </div>
        </div>
      </div>
      <!-- Mensaje motivacional -->
      <div class="col-12">
        <div class="card h-100" style="border-left:4px solid #BF5800;">
          <div class="card-body py-3">
            <div style="font-size:11px;color:#BF5800;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;"><i class="bi bi-lightning-fill me-1"></i>Mensaje del día</div>
            <?php
              $mensajes = [
                "¡Cada venta cuenta! Sigue así, " . htmlspecialchars($_SESSION['nombre'] ?? '') . ".",
                "La actitud positiva es tu mejor herramienta hoy.",
                "Un cliente satisfecho regresa siempre. ¡Tú lo logras!",
                "El trabajo duro de hoy es el éxito de mañana.",
                "¡Buen turno! Haz que cada cliente se sienta especial.",
                "La excelencia no es un acto, es un hábito. ¡Tú lo tienes!",
                "Cada pequeña venta construye el gran resultado del mes.",
              ];
              $msg = $mensajes[date('N')-1];
            ?>
            <div style="font-size:13px;line-height:1.6;font-style:italic;">"<?= $msg ?>"</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Reloj cajero en tiempo real
function actualizarReloj() {
  const now = new Date();
  const h = String(now.getHours()).padStart(2,'0');
  const m = String(now.getMinutes()).padStart(2,'0');
  const s = String(now.getSeconds()).padStart(2,'0');
  const dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
  const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
  const relojG = document.getElementById('reloj-grande');
  const fechaEl = document.getElementById('fecha-cajero');
  const relojC = document.getElementById('reloj-cajero');
  if(relojG) relojG.textContent = h+':'+m+':'+s;
  if(fechaEl) fechaEl.textContent = dias[now.getDay()]+', '+now.getDate()+' de '+meses[now.getMonth()]+' '+now.getFullYear();
  if(relojC) relojC.textContent = h+':'+m+':'+s;
}
actualizarReloj();
setInterval(actualizarReloj, 1000);
</script>

<?php endif; ?>

<script>
const initLabels7  = <?= json_encode(array_keys($data7)) ?>;
const initData7    = <?= json_encode(array_values($data7)) ?>;
const initLabelsV  = <?= json_encode($labelsV) ?>;
const initDataV    = <?= json_encode($dataV) ?>;
const initMetodos  = <?= json_encode(array_values($metodos)) ?>;

let chartVentas, chartTop, chartMetodos;

document.addEventListener('DOMContentLoaded', () => {
    chartVentas = new Chart(document.getElementById('grafVentas'), {
        type: 'bar',
        data: {
            labels: initLabels7,
            datasets: [{ label:'RD$', data:initData7, backgroundColor:'#2E75B6', borderRadius:5, borderSkipped:false }]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins: { legend:{display:false}, tooltip:{ callbacks:{ label: c=>'RD$ '+c.parsed.y.toLocaleString('es-DO',{minimumFractionDigits:2}) } } },
            scales: { y:{beginAtZero:true, ticks:{font:{size:10}, callback:v=>'$'+v.toLocaleString()}}, x:{ticks:{font:{size:10}}} }
        }
    });

    chartTop = new Chart(document.getElementById('grafTop'), {
        type: 'doughnut',
        data: {
            labels: initLabelsV,
            datasets: [{ data:initDataV, backgroundColor:['#1F4E79','#2E75B6','#0F6E56','#BF5800','#7030A0'], borderWidth:2, borderColor:'#fff' }]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins: { legend:{ position:'bottom', labels:{ font:{size:10}, boxWidth:12 } } }
        }
    });

    chartMetodos = new Chart(document.getElementById('grafMetodos'), {
        type: 'doughnut',
        data: {
            labels: ['Efectivo','Tarjeta','Transferencia'],
            datasets: [{ data:initMetodos, backgroundColor:['#0F6E56','#1F4E79','#BF5800'], borderWidth:2, borderColor:'#fff' }]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins: { legend:{ position:'bottom', labels:{ font:{size:10}, boxWidth:12 } } }
        }
    });

    // Histograma ingresos por mes
    const mesesLabels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const mesesData   = <?= json_encode(array_values($meses)) ?>;

    new Chart(document.getElementById('grafMeses'), {
        type: 'bar',
        data: {
            labels: mesesLabels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Ingresos RD$',
                    data: mesesData,
                    backgroundColor: 'rgba(46,117,182,0.7)',
                    borderColor: '#2E75B6',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 2
                },
                {
                    type: 'line',
                    label: 'Tendencia',
                    data: mesesData,
                    borderColor: '#BF5800',
                    backgroundColor: 'rgba(191,88,0,0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#BF5800',
                    pointRadius: 4,
                    tension: 0.4,
                    fill: true,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 14 } },
                tooltip: {
                    callbacks: {
                        label: c => 'RD$ ' + c.parsed.y.toLocaleString('es-DO', { minimumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 10 }, callback: v => 'RD$ ' + v.toLocaleString() }
                },
                x: { ticks: { font: { size: 11 } } }
            }
        }
    });

    actualizarHora();
    setInterval(autoRefresh, 60000);
});

function actualizarHora() {
    const el = document.getElementById('lblActualizado');
    if (el) el.textContent = 'Act. ' + new Date().toLocaleTimeString('es-DO',{hour:'2-digit',minute:'2-digit'});
}

function autoRefresh() {
    fetch('/api/dashboard_data.php')
        .then(r => r.json())
        .then(d => {
            if (!d) return;
            chartVentas.data.labels = d.labels7;
            chartVentas.data.datasets[0].data = d.data7;
            chartVentas.update('none');
            chartTop.data.labels = d.labelsV;
            chartTop.data.datasets[0].data = d.dataV;
            chartTop.update('none');
            chartMetodos.data.datasets[0].data = d.metodos;
            chartMetodos.update('none');
            // Histograma ingresos por mes
    const mesesLabels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const mesesData   = <?= json_encode(array_values($meses)) ?>;

    new Chart(document.getElementById('grafMeses'), {
        type: 'bar',
        data: {
            labels: mesesLabels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Ingresos RD$',
                    data: mesesData,
                    backgroundColor: 'rgba(46,117,182,0.7)',
                    borderColor: '#2E75B6',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 2
                },
                {
                    type: 'line',
                    label: 'Tendencia',
                    data: mesesData,
                    borderColor: '#BF5800',
                    backgroundColor: 'rgba(191,88,0,0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#BF5800',
                    pointRadius: 4,
                    tension: 0.4,
                    fill: true,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 14 } },
                tooltip: {
                    callbacks: {
                        label: c => 'RD$ ' + c.parsed.y.toLocaleString('es-DO', { minimumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 10 }, callback: v => 'RD$ ' + v.toLocaleString() }
                },
                x: { ticks: { font: { size: 11 } } }
            }
        }
    });

    actualizarHora();
        })
        .catch(() => {});
}
</script>

<?php include 'views/layouts/footer.php'; ?>