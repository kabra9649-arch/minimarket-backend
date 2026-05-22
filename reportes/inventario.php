<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$db = getDB();
$pageTitle = 'Reporte de Inventario';

$categoria = $_GET['categoria'] ?? '';
$estado    = $_GET['estado']    ?? '';

$where = "WHERE p.activo=1";
if ($categoria) $where .= " AND p.categoria_id=$categoria";
if ($estado === 'stock_bajo') $where .= " AND p.stock_actual <= p.stock_minimo";
if ($estado === 'por_vencer') $where .= " AND p.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND p.fecha_vencimiento >= CURDATE()";

$productos  = $db->query("SELECT p.*, c.nombre AS categoria, pr.nombre AS proveedor, (p.stock_actual * p.precio_venta) AS valor_inventario FROM productos p JOIN categorias c ON p.categoria_id=c.id JOIN proveedores pr ON p.proveedor_id=pr.id $where ORDER BY p.nombre");
$categorias = $db->query("SELECT * FROM categorias ORDER BY nombre");
$resumen    = $db->query("SELECT COUNT(*) AS total, IFNULL(SUM(stock_actual * precio_venta),0) AS valor, SUM(CASE WHEN stock_actual<=stock_minimo THEN 1 ELSE 0 END) AS stock_bajo FROM productos WHERE activo=1")->fetch_assoc();

// Datos para gráfico de líneas: stock por categoría
$grafCat = $db->query("SELECT c.nombre, SUM(p.stock_actual) AS stock_total, SUM(p.stock_actual * p.precio_venta) AS valor FROM productos p JOIN categorias c ON p.categoria_id=c.id WHERE p.activo=1 GROUP BY c.id ORDER BY valor DESC");
$catLabels = []; $catStock = []; $catValor = [];
while ($r = $grafCat->fetch_assoc()) {
    $catLabels[] = $r['nombre'];
    $catStock[]  = (int)$r['stock_total'];
    $catValor[]  = (float)$r['valor'];
}

include '../views/layouts/header.php';
?>

<!-- FILTROS -->
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Categoría</label>
        <select name="categoria" class="form-select form-select-sm">
          <option value="">Todas</option>
          <?php $categorias->data_seek(0); while ($c = $categorias->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= $categoria==$c['id']?'selected':''?>><?= htmlspecialchars($c['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Estado</label>
        <select name="estado" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="stock_bajo" <?= $estado=='stock_bajo'?'selected':''?>>Stock Bajo</option>
          <option value="por_vencer" <?= $estado=='por_vencer'?'selected':''?>>Por Vencer</option>
        </select>
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filtrar</button>
      </div>
    </form>
  </div>
</div>

<!-- RESUMEN -->
<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card text-white" style="background:#1F4E79;">
      <div class="card-body py-3 d-flex justify-content-between align-items-center">
        <div><div class="small opacity-75">Total productos</div><div class="fs-3 fw-bold"><?= $resumen['total'] ?></div></div>
        <i class="bi bi-box-seam fs-2 opacity-40"></i>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-white" style="background:#0F6E56;">
      <div class="card-body py-3 d-flex justify-content-between align-items-center">
        <div><div class="small opacity-75">Valor inventario</div><div class="fs-5 fw-bold">RD$ <?= number_format($resumen['valor'],0) ?></div></div>
        <i class="bi bi-cash-stack fs-2 opacity-40"></i>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-white" style="background:#C00000;">
      <div class="card-body py-3 d-flex justify-content-between align-items-center">
        <div><div class="small opacity-75">Stock bajo</div><div class="fs-3 fw-bold"><?= $resumen['stock_bajo'] ?></div></div>
        <i class="bi bi-exclamation-triangle fs-2 opacity-40"></i>
      </div>
    </div>
  </div>
</div>

<!-- GRÁFICO DE LÍNEAS -->
<div class="card mb-3">
  <div class="card-header py-2"><i class="bi bi-graph-up me-2"></i>Stock y valor por categoría</div>
  <div class="card-body py-2" style="height:240px; position:relative;">
    <canvas id="grafInv"></canvas>
  </div>
</div>

<!-- TABLA -->
<div class="card">
  <div class="card-header py-2 d-flex justify-content-between align-items-center">
    <span><i class="bi bi-clipboard-data me-2"></i>Inventario de Productos</span>
    <div class="d-flex gap-2">
      <a href="exportar_inventario_pdf.php" class="btn btn-sm btn-outline-danger py-0 px-3">
        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
      </a>
      <a href="exportar_inventario_excel.php" class="btn btn-sm btn-outline-success py-0 px-3">
        <i class="bi bi-file-earmark-excel me-1"></i>Excel
      </a>
      <button onclick="window.print()" class="btn btn-sm btn-outline-light py-0 px-3">
        <i class="bi bi-printer me-1"></i>Imprimir
      </button>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead>
          <tr><th>Producto</th><th>Categoría</th><th>P.Compra</th><th>P.Venta</th><th>Stock</th><th>Mín.</th><th>Vencimiento</th><th>Valor</th><th>Estado</th></tr>
        </thead>
        <tbody>
          <?php while ($p = $productos->fetch_assoc()):
            $sb = $p['stock_actual'] <= $p['stock_minimo'];
            $pv = $p['fecha_vencimiento'] && $p['fecha_vencimiento'] <= date('Y-m-d', strtotime('+7 days'));
          ?>
          <tr>
            <td class="small fw-semibold"><?= htmlspecialchars($p['nombre']) ?></td>
            <td><span class="badge bg-secondary" style="font-size:10px"><?= htmlspecialchars($p['categoria']) ?></span></td>
            <td class="small">RD$ <?= number_format($p['precio_compra'],2) ?></td>
            <td class="small">RD$ <?= number_format($p['precio_venta'],2) ?></td>
            <td><span class="badge bg-<?= $sb?'danger':'success' ?>"><?= $p['stock_actual'] ?></span></td>
            <td class="small"><?= $p['stock_minimo'] ?></td>
            <td class="small">
              <?php if ($p['fecha_vencimiento']): ?>
                <span class="badge bg-<?= $pv?'warning text-dark':'light text-dark' ?>"><?= date('d/m/Y', strtotime($p['fecha_vencimiento'])) ?></span>
              <?php else: echo '—'; endif; ?>
            </td>
            <td class="small">RD$ <?= number_format($p['valor_inventario'],0) ?></td>
            <td>
              <?php if ($sb): ?>
                <span class="badge bg-danger" style="font-size:10px">Stock Bajo</span>
              <?php elseif ($pv): ?>
                <span class="badge bg-warning text-dark" style="font-size:10px">Por Vencer</span>
              <?php else: ?>
                <span class="badge bg-success" style="font-size:10px">OK</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ctx = document.getElementById('grafInv');
  if (!ctx) return;
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
  const textColor = isDark ? '#94a3b8' : '#666';

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode($catLabels) ?>,
      datasets: [
        {
          label: 'Unidades en Stock',
          data: <?= json_encode($catStock) ?>,
          borderColor: '#2E75B6',
          backgroundColor: 'rgba(46,117,182,0.12)',
          tension: 0.4,
          fill: true,
          borderWidth: 2.5,
          pointRadius: 5,
          pointBackgroundColor: '#2E75B6',
          yAxisID: 'y'
        },
        {
          label: 'Valor (RD$)',
          data: <?= json_encode($catValor) ?>,
          borderColor: '#F59E0B',
          backgroundColor: 'rgba(245,158,11,0.08)',
          tension: 0.4,
          fill: true,
          borderWidth: 2.5,
          pointRadius: 5,
          pointBackgroundColor: '#F59E0B',
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'top', labels: { font: { size: 11 }, color: textColor, boxWidth: 14 } },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              if (ctx.datasetIndex === 1) return ' RD$ ' + ctx.parsed.y.toLocaleString('es-DO');
              return ' ' + ctx.parsed.y + ' uds.';
            }
          }
        }
      },
      scales: {
        x: { ticks: { color: textColor, font: { size: 11 } }, grid: { color: gridColor } },
        y: {
          type: 'linear', position: 'left',
          ticks: { color: '#2E75B6', font: { size: 10 } },
          grid: { color: gridColor },
          title: { display: true, text: 'Unidades', color: '#2E75B6', font: { size: 10 } }
        },
        y1: {
          type: 'linear', position: 'right',
          ticks: {
            color: '#F59E0B', font: { size: 10 },
            callback: v => 'RD$ ' + v.toLocaleString('es-DO')
          },
          grid: { drawOnChartArea: false },
          title: { display: true, text: 'Valor', color: '#F59E0B', font: { size: 10 } }
        }
      }
    }
  });
});
</script>

<?php include '../views/layouts/footer.php'; ?>
