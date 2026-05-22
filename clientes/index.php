<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../views/layouts/toast.php';
requireLogin();

$db        = getDB();
$pageTitle = 'Gestión de Clientes';
$user      = currentUser();
$esCajero  = $user['rol'] === 'cajero';

// Crear
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear') {
    $nombre    = trim($_POST['nombre']    ?? '');
    $cedula    = trim($_POST['cedula']    ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    if ($nombre) {
        $stmt = $db->prepare("INSERT INTO clientes (nombre,cedula,telefono,email,direccion,activo) VALUES (?,?,?,?,?,1)");
        $stmt->bind_param('sssss', $nombre, $cedula, $telefono, $email, $direccion);
        $stmt->execute() ? setToast('success','Cliente registrado correctamente.') : setToast('error','Error: '.$db->error);
        $stmt->close();
    } else {
        setToast('error','El nombre es obligatorio.');
    }
    header('Location: index.php'); exit();
}

// Editar (solo admin/gerente)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'editar' && !$esCajero) {
    $id=$_POST['id']; $nombre=trim($_POST['nombre']??'');
    $cedula=trim($_POST['cedula']??''); $telefono=trim($_POST['telefono']??'');
    $email=trim($_POST['email']??''); $direccion=trim($_POST['direccion']??'');
    $stmt=$db->prepare("UPDATE clientes SET nombre=?,cedula=?,telefono=?,email=?,direccion=? WHERE id=?");
    $stmt->bind_param('sssssi',$nombre,$cedula,$telefono,$email,$direccion,$id);
    $stmt->execute() ? setToast('success','Cliente actualizado.') : setToast('error','Error: '.$db->error);
    $stmt->close();
    header('Location: index.php'); exit();
}

// Eliminar (solo admin/gerente)
if (isset($_GET['eliminar']) && !$esCajero) {
    $id=(int)$_GET['eliminar'];
    $check=$db->query("SELECT COUNT(*) as cnt FROM ventas WHERE cliente_id=$id")->fetch_assoc();
    if ($check['cnt']>0) {
        setToast('error','No puedes eliminar un cliente con ventas registradas.');
    } else {
        $db->query("DELETE FROM clientes WHERE id=$id");
        setToast('warning','Cliente eliminado.');
    }
    header('Location: index.php'); exit();
}

// Listado solo admin/gerente
$clientes = !$esCajero ? $db->query("SELECT c.*, COUNT(v.id) AS total_compras, IFNULL(SUM(v.total),0) AS total_gastado
    FROM clientes c LEFT JOIN ventas v ON c.id=v.cliente_id AND v.estado='completada'
    GROUP BY c.id ORDER BY c.nombre") : null;

// Datos para editar
$editando = null;
if (isset($_GET['editar']) && !$esCajero) {
    $editando = $db->query("SELECT * FROM clientes WHERE id=".(int)$_GET['editar'])->fetch_assoc();
}

include '../views/layouts/header.php';
?>

<!-- FORMULARIO NUEVO CLIENTE -->
<div class="card mb-3">
  <div class="card-header"><i class="bi bi-person-plus me-2"></i>Nuevo Cliente</div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="action" value="crear">
      <div class="row g-2 mb-2">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Nombre completo <span class="text-danger">*</span></label>
          <input type="text" name="nombre" class="form-control form-control-sm" placeholder="Ej: Juan Pérez" required>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Cédula</label>
          <input type="text" name="cedula" class="form-control form-control-sm" placeholder="000-0000000-0">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Teléfono</label>
          <input type="text" name="telefono" class="form-control form-control-sm" placeholder="809-000-0000">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Correo</label>
          <input type="email" name="email" class="form-control form-control-sm" placeholder="cliente@email.com">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Dirección</label>
          <input type="text" name="direccion" class="form-control form-control-sm" placeholder="Calle, sector, ciudad...">
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">
        <i class="bi bi-person-check me-1"></i>Registrar cliente
      </button>
    </form>
  </div>
</div>

<?php if (!$esCajero && $clientes): ?>

<?php if ($editando): ?>
<!-- MODAL EDITAR -->
<div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Editar Cliente</h5>
        <a href="index.php" class="btn-close"></a>
      </div>
      <div class="modal-body">
        <form method="POST">
          <input type="hidden" name="action" value="editar">
          <input type="hidden" name="id" value="<?= $editando['id'] ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Nombre <span class="text-danger">*</span></label>
              <input type="text" name="nombre" class="form-control form-control-sm" value="<?= htmlspecialchars($editando['nombre']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Cédula</label>
              <input type="text" name="cedula" class="form-control form-control-sm" value="<?= htmlspecialchars($editando['cedula']??'') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Teléfono</label>
              <input type="text" name="telefono" class="form-control form-control-sm" value="<?= htmlspecialchars($editando['telefono']??'') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Correo</label>
              <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($editando['email']??'') ?>">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Dirección</label>
              <input type="text" name="direccion" class="form-control form-control-sm" value="<?= htmlspecialchars($editando['direccion']??'') ?>">
            </div>
          </div>
          <div class="mt-3 d-flex gap-2 justify-content-end">
            <a href="index.php" class="btn btn-secondary btn-sm">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- TABLA CLIENTES -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span><i class="bi bi-people me-2"></i>Listado de Clientes
      <span class="badge bg-info text-dark ms-2"><?= $clientes->num_rows ?></span>
    </span>
    <input type="text" class="form-control form-control-sm" style="width:200px"
           placeholder="Buscar..." oninput="filtrar(this.value)">
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0" id="tbl">
        <thead>
          <tr>
            <th class="ps-3">#</th><th>Nombre</th><th>Cédula</th>
            <th>Teléfono</th><th>Correo</th>
            <th class="text-center">Compras</th>
            <th class="text-end">Total gastado</th>
            <th class="text-center pe-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($c = $clientes->fetch_assoc()): ?>
          <tr data-buscar="<?= strtolower(htmlspecialchars($c['nombre'].' '.$c['cedula'].' '.$c['email'])) ?>">
            <td class="ps-3 text-muted small"><?= $c['id'] ?></td>
            <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
            <td class="small text-muted"><?= htmlspecialchars($c['cedula']?:'—') ?></td>
            <td class="small text-muted"><?= htmlspecialchars($c['telefono']?:'—') ?></td>
            <td class="small text-muted"><?= htmlspecialchars($c['email']?:'—') ?></td>
            <td class="text-center"><span class="badge bg-info text-dark"><?= $c['total_compras'] ?></span></td>
            <td class="text-end small">RD$ <?= number_format($c['total_gastado'],2) ?></td>
            <td class="text-center pe-3">
              <a href="?editar=<?= $c['id'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2 me-1"><i class="bi bi-pencil"></i></a>
              <a href="?eliminar=<?= $c['id'] ?>" class="btn btn-outline-danger btn-sm py-0 px-2"
                 onclick="return confirm('¿Eliminar a <?= addslashes(htmlspecialchars($c['nombre'])) ?>?')"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
function filtrar(q){
  q=q.toLowerCase();
  document.querySelectorAll('#tbl tbody tr').forEach(r=>{
    r.style.display=(r.getAttribute('data-buscar')||'').includes(q)?'':'none';
  });
}
</script>
<?php endif; ?>

<?php include '../views/layouts/footer.php'; ?>
