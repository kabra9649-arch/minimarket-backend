<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../views/layouts/toast.php';
requireLogin();

$db       = getDB();
$pageTitle = 'Ventas';
$user     = currentUser();
$esCajero = $user['rol'] === 'cajero';

// Anular (solo admin/gerente)
if (isset($_GET['anular']) && !$esCajero) {
    $id = (int)$_GET['anular'];
    $db->query("UPDATE ventas SET estado='anulada' WHERE id=$id");
    setToast('warning', 'Venta anulada correctamente.');
    header('Location: index.php'); exit();
}

// Registrar venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear') {
    $usuario_id  = $_SESSION['usuario_id'];
    $cliente_id  = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
    $metodo_pago = $_POST['metodo_pago'];
    $prods_ids   = $_POST['producto_id'] ?? [];
    $cantidades  = $_POST['cantidad'] ?? [];
    $precios     = $_POST['precio_unitario'] ?? [];

    if (empty($prods_ids)) {
        setToast('error', 'Debes agregar al menos un producto.');
    } else {
        $num_factura = 'FAC-' . date('Ymd') . '-' . str_pad(rand(1,9999),4,'0',STR_PAD_LEFT);
        $total = 0;
        foreach ($cantidades as $k => $cant) $total += $cant * $precios[$k];

        $db->begin_transaction();
        try {
            $stmt = $db->prepare("INSERT INTO ventas (usuario_id,cliente_id,num_factura,total,metodo_pago) VALUES (?,?,?,?,?)");
            $stmt->bind_param('iisds', $usuario_id, $cliente_id, $num_factura, $total, $metodo_pago);
            $stmt->execute();
            $venta_id = $db->insert_id; $stmt->close();

            foreach ($prods_ids as $k => $prod_id) {
                $cant     = (int)$cantidades[$k];
                $precio   = (float)$precios[$k];
                $subtotal = $cant * $precio;
                $prod = $db->query("SELECT stock_actual,nombre,fecha_vencimiento FROM productos WHERE id=$prod_id AND activo=1")->fetch_assoc();
                if (!$prod) throw new Exception("Producto no encontrado.");
                if ($prod['stock_actual'] < $cant) throw new Exception("Stock insuficiente: {$prod['nombre']}");
                if ($prod['fecha_vencimiento'] && $prod['fecha_vencimiento'] < date('Y-m-d')) throw new Exception("Producto vencido: {$prod['nombre']}");
                $s = $db->prepare("INSERT INTO detalle_ventas (venta_id,producto_id,cantidad,precio_unitario,subtotal) VALUES (?,?,?,?,?)");
                $s->bind_param('iiidd', $venta_id, $prod_id, $cant, $precio, $subtotal);
                $s->execute(); $s->close();
                $db->query("UPDATE productos SET stock_actual=stock_actual-$cant WHERE id=$prod_id");
            }
            $db->commit();
            setToast('success', "Venta registrada — Factura: <strong>$num_factura</strong> — Total: RD$ " . number_format($total,2));
        } catch (Exception $e) {
            $db->rollback();
            setToast('error', $e->getMessage());
        }
    }
    header('Location: index.php'); exit();
}

// Cargar productos y clientes
$productos = $db->query("SELECT id,nombre,precio_venta,stock_actual FROM productos WHERE activo=1 AND stock_actual>0 ORDER BY nombre");
$clientes  = $db->query("SELECT id,nombre FROM clientes ORDER BY nombre");
$prods_arr = [];
while ($p = $productos->fetch_assoc()) $prods_arr[] = $p;

// Historial solo para admin/gerente
$ventas = null;
if (!$esCajero) {
    $ventas = $db->query("SELECT v.*,u.nombre AS cajero,c.nombre AS cliente FROM ventas v
        JOIN usuarios u ON v.usuario_id=u.id
        LEFT JOIN clientes c ON v.cliente_id=c.id
        ORDER BY v.fecha DESC LIMIT 50");
}

include '../views/layouts/header.php';
?>

<!-- FORMULARIO NUEVA VENTA -->
<div class="card mb-3">
  <div class="card-header"><i class="bi bi-bag-plus me-2"></i>Registrar Nueva Venta</div>
  <div class="card-body">
    <form method="POST" id="formVenta">
      <input type="hidden" name="action" value="crear">
      <div class="row g-2 mb-3">
        <div class="col-md-5">
          <label class="form-label small fw-semibold">Cliente</label>
          <select name="cliente_id" class="form-select form-select-sm">
            <option value="">Cliente General</option>
            <?php while ($c = $clientes->fetch_assoc()): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Método de pago</label>
          <select name="metodo_pago" class="form-select form-select-sm">
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="transferencia">Transferencia</option>
          </select>
        </div>
      </div>
      <div class="table-responsive mb-2">
        <table class="table table-bordered table-sm" id="tablaProductos">
          <thead class="table-light">
            <tr><th>Producto</th><th style="width:120px">Precio</th><th style="width:100px">Cantidad</th><th style="width:120px">Subtotal</th><th style="width:40px"></th></tr>
          </thead>
          <tbody id="filas">
            <tr id="fila_0">
              <td>
                <select name="producto_id[]" class="form-select form-select-sm prod-select" onchange="actualizarPrecio(0)" required>
                  <option value="">Seleccionar producto...</option>
                  <?php foreach ($prods_arr as $p): ?>
                    <option value="<?= $p['id'] ?>" data-precio="<?= $p['precio_venta'] ?>" data-stock="<?= $p['stock_actual'] ?>">
                      <?= htmlspecialchars($p['nombre']) ?> (Stock: <?= $p['stock_actual'] ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="number" step="0.01" name="precio_unitario[]" id="precio_0" class="form-control form-control-sm" readonly></td>
              <td><input type="number" name="cantidad[]" id="cant_0" class="form-control form-control-sm" min="1" value="1" oninput="calcularSubtotal(0)" required></td>
              <td><input type="number" step="0.01" name="subtotal[]" id="sub_0" class="form-control form-control-sm" readonly></td>
              <td><button type="button" class="btn btn-sm btn-danger py-0" onclick="eliminarFila(0)"><i class="bi bi-trash"></i></button></td>
            </tr>
          </tbody>
          <tfoot>
            <tr><td colspan="3" class="text-end fw-bold small">TOTAL:</td><td colspan="2"><span id="totalVenta" class="fw-bold text-primary">RD$ 0.00</span></td></tr>
          </tfoot>
        </table>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="agregarFila()">
          <i class="bi bi-plus-circle me-1"></i>Agregar producto
        </button>
        <button type="submit" class="btn btn-primary btn-sm" id="btnVenta">
          <i class="bi bi-check-circle me-1"></i>Registrar Venta
        </button>
      </div>
    </form>
  </div>
</div>

<?php if (!$esCajero && $ventas): ?>
<!-- HISTORIAL - solo admin/gerente -->
<div class="card">
  <div class="card-header"><i class="bi bi-receipt me-2"></i>Últimas 50 ventas</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead>
          <tr><th>Factura</th><th>Cajero</th><th>Cliente</th><th>Total</th><th>Método</th><th>Fecha</th><th>Estado</th><th>Acción</th></tr>
        </thead>
        <tbody>
          <?php while ($v = $ventas->fetch_assoc()): ?>
          <tr>
            <td class="small"><strong><?= $v['num_factura'] ?></strong></td>
            <td class="small"><?= htmlspecialchars($v['cajero']) ?></td>
            <td class="small"><?= htmlspecialchars($v['cliente'] ?? 'General') ?></td>
            <td class="small">RD$ <?= number_format($v['total'],2) ?></td>
            <td class="small"><?= ucfirst($v['metodo_pago']) ?></td>
            <td class="small"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
            <td><span class="badge bg-<?= $v['estado']==='completada'?'success':'danger' ?>"><?= ucfirst($v['estado']) ?></span></td>
            <td class="d-flex gap-1">
              <a href="factura.php?id=<?= $v['id'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2" target="_blank"><i class="bi bi-printer"></i></a>
              <?php if ($v['estado'] === 'completada'): ?>
              <a href="?anular=<?= $v['id'] ?>" class="btn btn-outline-danger btn-sm py-0 px-2"
                 onclick="return confirm('¿Anular esta venta?')"><i class="bi bi-x-circle"></i></a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
const productos = <?= json_encode($prods_arr) ?>;
let filaCount = 1;
function actualizarPrecio(idx){
  const sel=document.querySelector(`#fila_${idx} .prod-select`);
  const opt=sel.options[sel.selectedIndex];
  document.getElementById(`precio_${idx}`).value=opt.dataset.precio||0;
  calcularSubtotal(idx);
}
function calcularSubtotal(idx){
  const p=parseFloat(document.getElementById(`precio_${idx}`).value)||0;
  const c=parseFloat(document.getElementById(`cant_${idx}`).value)||0;
  document.getElementById(`sub_${idx}`).value=(p*c).toFixed(2);
  calcularTotal();
}
function calcularTotal(){
  let t=0;
  document.querySelectorAll('[id^="sub_"]').forEach(e=>t+=parseFloat(e.value)||0);
  document.getElementById('totalVenta').textContent='RD$ '+t.toLocaleString('es-DO',{minimumFractionDigits:2});
}
function agregarFila(){
  const idx=filaCount++;
  const opts=productos.map(p=>`<option value="${p.id}" data-precio="${p.precio_venta}" data-stock="${p.stock_actual}">${p.nombre} (Stock: ${p.stock_actual})</option>`).join('');
  const tr=document.createElement('tr');
  tr.id=`fila_${idx}`;
  tr.innerHTML=`<td><select name="producto_id[]" class="form-select form-select-sm prod-select" onchange="actualizarPrecio(${idx})" required><option value="">Seleccionar...</option>${opts}</select></td>
    <td><input type="number" step="0.01" name="precio_unitario[]" id="precio_${idx}" class="form-control form-control-sm" readonly></td>
    <td><input type="number" name="cantidad[]" id="cant_${idx}" class="form-control form-control-sm" min="1" value="1" oninput="calcularSubtotal(${idx})" required></td>
    <td><input type="number" step="0.01" name="subtotal[]" id="sub_${idx}" class="form-control form-control-sm" readonly></td>
    <td><button type="button" class="btn btn-sm btn-danger py-0" onclick="eliminarFila(${idx})"><i class="bi bi-trash"></i></button></td>`;
  document.getElementById('filas').appendChild(tr);
}
function eliminarFila(idx){
  const f=document.getElementById(`fila_${idx}`);
  if(document.querySelectorAll('#filas tr').length>1){f.remove();calcularTotal();}
}
document.getElementById('formVenta').addEventListener('submit',function(){
  document.getElementById('btnVenta').disabled=true;
  document.getElementById('btnVenta').innerHTML='<i class="bi bi-hourglass-split me-1"></i>Procesando...';
});
</script>

<?php include '../views/layouts/footer.php'; ?>
