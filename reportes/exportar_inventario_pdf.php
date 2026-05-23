<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$db = getDB();
$categoria = $_GET['categoria'] ?? '';
$estado    = $_GET['estado']    ?? '';

$where = "WHERE p.activo=1";
if ($categoria) $where .= " AND p.categoria_id=$categoria";
if ($estado === 'stock_bajo') $where .= " AND p.stock_actual <= p.stock_minimo";
if ($estado === 'por_vencer') $where .= " AND p.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND p.fecha_vencimiento >= CURDATE()";

$productos = $db->query("SELECT p.*, c.nombre AS categoria, pr.nombre AS proveedor, (p.stock_actual * p.precio_venta) AS valor_inventario FROM productos p JOIN categorias c ON p.categoria_id=c.id JOIN proveedores pr ON p.proveedor_id=pr.id $where ORDER BY p.nombre");
$resumen   = $db->query("SELECT COUNT(*) AS total, IFNULL(SUM(stock_actual * precio_venta),0) AS valor, SUM(CASE WHEN stock_actual<=stock_minimo THEN 1 ELSE 0 END) AS stock_bajo FROM productos WHERE activo=1")->fetch_assoc();

$filas = '';
while ($p = $productos->fetch_assoc()) {
    $sb = $p['stock_actual'] <= $p['stock_minimo'];
    $pv = $p['fecha_vencimiento'] && $p['fecha_vencimiento'] <= date('Y-m-d', strtotime('+7 days'));
    $estado_txt = $sb ? 'Stock Bajo' : ($pv ? 'Por Vencer' : 'OK');
    $estado_color = $sb ? '#dc2626' : ($pv ? '#d97706' : '#16a34a');
    $filas .= "<tr>
        <td>".htmlspecialchars($p['nombre'])."</td>
        <td>".htmlspecialchars($p['categoria'])."</td>
        <td>RD$ ".number_format($p['precio_compra'],2)."</td>
        <td>RD$ ".number_format($p['precio_venta'],2)."</td>
        <td style='text-align:center;font-weight:bold;color:".($sb?'#dc2626':'#16a34a')."'>".$p['stock_actual']."</td>
        <td style='text-align:center'>".$p['stock_minimo']."</td>
        <td style='text-align:center'>".($p['fecha_vencimiento'] ? date('d/m/Y',strtotime($p['fecha_vencimiento'])) : '—')."</td>
        <td style='text-align:right'>RD$ ".number_format($p['valor_inventario'],0)."</td>
        <td style='text-align:center;color:$estado_color;font-weight:bold'>$estado_txt</td>
    </tr>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Inventario</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a2e; padding: 20px; }
  .header { text-align:center; margin-bottom:20px; border-bottom:3px solid #1F4E79; padding-bottom:12px; }
  .header h1 { font-size:22px; color:#1F4E79; margin-bottom:4px; }
  .header p { color:#666; font-size:11px; }
  .resumen { display:flex; gap:16px; margin-bottom:16px; }
  .resumen-card { flex:1; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 14px; text-align:center; }
  .resumen-card .valor { font-size:20px; font-weight:bold; color:#1F4E79; }
  .resumen-card .label { font-size:10px; color:#666; margin-top:2px; }
  table { width:100%; border-collapse:collapse; margin-top:10px; }
  thead { background:#1F4E79; color:#fff; }
  thead th { padding:8px 6px; text-align:left; font-size:10px; }
  tbody tr:nth-child(even) { background:#f8fafc; }
  tbody td { padding:6px; border-bottom:1px solid #e2e8f0; }
  .footer { margin-top:16px; text-align:center; color:#999; font-size:10px; border-top:1px solid #e2e8f0; padding-top:8px; }
  @media print {
    body { padding: 0; }
    .no-print { display: none; }
  }
</style>
</head>
<body>
<div class="no-print" style="text-align:center;margin-bottom:16px;">
  <button onclick="window.print()" style="background:#1F4E79;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:13px;cursor:pointer;margin-right:8px;">
    🖨️ Imprimir / Guardar PDF
  </button>
  <button onclick="window.close()" style="background:#6b7280;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:13px;cursor:pointer;">
    ✕ Cerrar
  </button>
</div>

<div class="header">
  <h1>⚡ NEXSYS — Reporte de Inventario</h1>
  <p>Generado el <?= date('d/m/Y H:i') ?> · MiniMarket G2</p>
</div>

<div class="resumen">
  <div class="resumen-card">
    <div class="valor"><?= $resumen['total'] ?></div>
    <div class="label">Total Productos</div>
  </div>
  <div class="resumen-card">
    <div class="valor" style="color:#16a34a">RD$ <?= number_format($resumen['valor'],0) ?></div>
    <div class="label">Valor Inventario</div>
  </div>
  <div class="resumen-card">
    <div class="valor" style="color:#dc2626"><?= $resumen['stock_bajo'] ?></div>
    <div class="label">Stock Bajo</div>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>Producto</th><th>Categoría</th><th>P.Compra</th><th>P.Venta</th>
      <th>Stock</th><th>Mín.</th><th>Vencimiento</th><th>Valor</th><th>Estado</th>
    </tr>
  </thead>
  <tbody><?= $filas ?></tbody>
</table>

<div class="footer">NEXSYS Sistema de Gestión · MiniMarket G2 · <?= date('Y') ?></div>

<script>
  // Auto-abrir diálogo de impresión/PDF
  window.onload = function() { window.print(); }
</script>
</body>
</html>
