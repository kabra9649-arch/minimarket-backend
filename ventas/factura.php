<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit(); }

$venta   = $db->query("SELECT v.*, u.nombre AS cajero, c.nombre AS cliente, c.cedula, c.telefono FROM ventas v JOIN usuarios u ON v.usuario_id=u.id LEFT JOIN clientes c ON v.cliente_id=c.id WHERE v.id=$id")->fetch_assoc();
$detalle = $db->query("SELECT dv.*, p.nombre AS producto, p.codigo_barras FROM detalle_ventas dv JOIN productos p ON dv.producto_id=p.id WHERE dv.venta_id=$id");

if (!$venta) { echo 'Venta no encontrada'; exit(); }

// Función para truncar texto con ancho fijo (para impresora térmica 32 cols)
function padLeft($text, $width) {
    return str_pad(mb_substr($text, 0, $width), $width, ' ', STR_PAD_RIGHT);
}
function padRight($text, $width) {
    return str_pad(mb_substr($text, 0, $width), $width, ' ', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura <?= $venta['num_factura'] ?></title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    width: 300px;
    margin: 0 auto;
    padding: 10px;
  }
  .center { text-align: center; }
  .bold { font-weight: bold; }
  .line { border-top: 1px dashed #000; margin: 6px 0; }
  .row { display: flex; justify-content: space-between; margin: 2px 0; }
  .total-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    font-weight: bold;
    margin: 4px 0;
  }
  /* Tabla de productos con columnas fijas para impresora térmica */
  table { width: 100%; border-collapse: collapse; table-layout: fixed; }
  td { padding: 2px 1px; vertical-align: top; word-break: break-word; }
  .col-prod  { width: 44%; }
  .col-cant  { width: 12%; text-align: right; }
  .col-price { width: 22%; text-align: right; }
  .col-sub   { width: 22%; text-align: right; }
  .btn-print {
    display: block;
    margin: 16px auto;
    padding: 8px 24px;
    background: #1F4E79;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 14px;
    border-radius: 4px;
  }
  @media print {
    .btn-print { display: none; }
    body { width: 100%; }
  }
</style>
</head>
<body>

<div class="center bold" style="font-size:15px;">NEXSYS</div>
<div class="center">Santo Domingo, Rep. Dominicana</div>
<div class="center">Tel: 809-555-0000</div>
<div class="line"></div>

<div class="center bold">COMPROBANTE DE VENTA</div>
<div class="line"></div>

<div class="row"><span>Factura:</span><span class="bold"><?= htmlspecialchars($venta['num_factura']) ?></span></div>
<div class="row"><span>Fecha:</span><span><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></span></div>
<div class="row"><span>Cajero:</span><span><?= htmlspecialchars($venta['cajero']) ?></span></div>
<div class="row"><span>Cliente:</span><span><?= htmlspecialchars($venta['cliente'] ?? 'General') ?></span></div>
<?php if (!empty($venta['cedula'])): ?>
<div class="row"><span>Cédula:</span><span><?= htmlspecialchars($venta['cedula']) ?></span></div>
<?php endif; ?>
<div class="line"></div>

<table>
  <thead>
    <tr>
      <td class="col-prod bold">Producto</td>
      <td class="col-cant bold">Cant</td>
      <td class="col-price bold">Precio</td>
      <td class="col-sub bold">Sub</td>
    </tr>
  </thead>
  <tbody>
    <tr><td colspan="4"><div class="line"></div></td></tr>
    <?php while ($d = $detalle->fetch_assoc()): ?>
    <tr>
      <td class="col-prod"><?= htmlspecialchars($d['producto']) ?></td>
      <td class="col-cant"><?= $d['cantidad'] ?></td>
      <td class="col-price"><?= number_format($d['precio_unitario'], 2) ?></td>
      <td class="col-sub"><?= number_format($d['subtotal'], 2) ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<div class="line"></div>
<div class="total-row">
  <span>TOTAL:</span>
  <span>RD$ <?= number_format($venta['total'], 2) ?></span>
</div>
<div class="row">
  <span>Metodo de pago:</span>
  <span><?= ucfirst($venta['metodo_pago']) ?></span>
</div>
<div class="line"></div>

<div class="center">¡Gracias por su compra!</div>
<div class="center">Vuelva pronto</div>
<div class="center" style="margin-top:6px; font-size:10px;">Sistema Nexsys &mdash; 2026</div>

<button class="btn-print" onclick="window.print()">🖨️ Imprimir Factura</button>

</body>
</html>