<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../views/layouts/toast.php';
requireLogin();
requireRole(['administrador', 'gerente']);

$db        = getDB();
$pageTitle = 'Gestión de Empleados';
$user      = currentUser();

// Crear tabla si no existe
$db->query("CREATE TABLE IF NOT EXISTS `empleados` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT DEFAULT NULL,
    `nombre` VARCHAR(100) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `cedula` VARCHAR(20) DEFAULT NULL,
    `telefono` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `cargo` ENUM('cajero','gerente','supervisor','almacenista','repartidor') DEFAULT 'cajero',
    `salario` DECIMAL(10,2) DEFAULT 0.00,
    `fecha_ingreso` DATE DEFAULT NULL,
    `direccion` TEXT DEFAULT NULL,
    `activo` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_emp_usr` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Crear
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear') {
    $nombre=$_POST['nombre']; $apellido=$_POST['apellido']; $cedula=$_POST['cedula']??'';
    $telefono=$_POST['telefono']??''; $email=$_POST['email']??''; $cargo=$_POST['cargo'];
    $salario=(float)$_POST['salario']; $fecha=$_POST['fecha_ingreso']??''; $direccion=$_POST['direccion']??'';
    $uid=!empty($_POST['usuario_id'])?(int)$_POST['usuario_id']:null;
    $stmt=$db->prepare("INSERT INTO empleados (usuario_id,nombre,apellido,cedula,telefono,email,cargo,salario,fecha_ingreso,direccion) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('issssssdss',$uid,$nombre,$apellido,$cedula,$telefono,$email,$cargo,$salario,$fecha,$direccion);
    $stmt->execute() ? setToast('success','Empleado registrado correctamente.') : setToast('error','Error: '.$db->error);
    $stmt->close();
    header('Location: index.php'); exit();
}

// Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'editar') {
    $id=(int)$_POST['id']; $nombre=$_POST['nombre']; $apellido=$_POST['apellido'];
    $cedula=$_POST['cedula']??''; $telefono=$_POST['telefono']??''; $email=$_POST['email']??'';
    $cargo=$_POST['cargo']; $salario=(float)$_POST['salario'];
    $fecha=$_POST['fecha_ingreso']??''; $direccion=$_POST['direccion']??'';
    $uid=!empty($_POST['usuario_id'])?(int)$_POST['usuario_id']:null;
    $stmt=$db->prepare("UPDATE empleados SET usuario_id=?,nombre=?,apellido=?,cedula=?,telefono=?,email=?,cargo=?,salario=?,fecha_ingreso=?,direccion=? WHERE id=?");
    $stmt->bind_param('issssssdssi',$uid,$nombre,$apellido,$cedula,$telefono,$email,$cargo,$salario,$fecha,$direccion,$id);
    $stmt->execute() ? setToast('success','Empleado actualizado.') : setToast('error','Error: '.$db->error);
    $stmt->close();
    header('Location: index.php'); exit();
}

// Eliminar
if (isset($_GET['eliminar'])) {
    $db->query("DELETE FROM empleados WHERE id=".(int)$_GET['eliminar']);
    setToast('warning','Empleado eliminado.');
    header('Location: index.php'); exit();
}

// Toggle activo
if (isset($_GET['toggle'])) {
    $db->query("UPDATE empleados SET activo=IF(activo=1,0,1) WHERE id=".(int)$_GET['toggle']);
    header('Location: index.php'); exit();
}

$empleados = $db->query("SELECT e.*, u.email AS usuario_email FROM empleados e LEFT JOIN usuarios u ON e.usuario_id=u.id ORDER BY e.nombre");
$usuarios  = $db->query("SELECT id,nombre,email,rol FROM usuarios WHERE activo=1 ORDER BY nombre");
$editando  = isset($_GET['editar']) ? $db->query("SELECT * FROM empleados WHERE id=".(int)$_GET['editar'])->fetch_assoc() : null;

include '../views/layouts/header.php';
?>

<div class="card mb-3">
  <div class="card-header">
    <i class="bi bi-person-badge me-2"></i><?= $editando ? 'Editar Empleado' : 'Registrar Nuevo Empleado' ?>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="action" value="<?= $editando ? 'editar' : 'crear' ?>">
      <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
      <div class="row g-2 mb-2">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Nombre <span class="text-danger">*</span></label>
          <input type="text" name="nombre" class="form-control form-control-sm" required value="<?= htmlspecialchars($editando['nombre'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Apellido <span class="text-danger">*</span></label>
          <input type="text" name="apellido" class="form-control form-control-sm" required value="<?= htmlspecialchars($editando['apellido'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Cédula</label>
          <input type="text" name="cedula" class="form-control form-control-sm" placeholder="000-0000000-0" value="<?= htmlspecialchars($editando['cedula'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Teléfono</label>
          <input type="text" name="telefono" class="form-control form-control-sm" placeholder="809-000-0000" value="<?= htmlspecialchars($editando['telefono'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Correo</label>
          <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($editando['email'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Cargo</label>
          <select name="cargo" class="form-select form-select-sm">
            <?php foreach(['cajero','gerente','supervisor','almacenista','repartidor'] as $c): ?>
              <option value="<?= $c ?>" <?= ($editando['cargo'] ?? 'cajero') === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Salario (RD$)</label>
          <input type="number" step="0.01" name="salario" class="form-control form-control-sm" value="<?= $editando['salario'] ?? '0' ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Fecha ingreso</label>
          <input type="date" name="fecha_ingreso" class="form-control form-control-sm" value="<?= $editando['fecha_ingreso'] ?? '' ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Usuario del sistema</label>
          <select name="usuario_id" class="form-select form-select-sm">
            <option value="">Sin usuario asignado</option>
            <?php $usuarios->data_seek(0); while ($u = $usuarios->fetch_assoc()): ?>
              <option value="<?= $u['id'] ?>" <?= ($editando['usuario_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['nombre']) ?> (<?= $u['rol'] ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Dirección</label>
          <input type="text" name="direccion" class="form-control form-control-sm" value="<?= htmlspecialchars($editando['direccion'] ?? '') ?>">
        </div>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-<?= $editando ? 'save' : 'person-plus' ?> me-1"></i>
          <?= $editando ? 'Guardar cambios' : 'Registrar empleado' ?>
        </button>
        <?php if ($editando): ?>
          <a href="index.php" class="btn btn-secondary btn-sm">Cancelar</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span><i class="bi bi-people me-2"></i>Listado de Empleados
      <span class="badge bg-info text-dark ms-2"><?= $empleados->num_rows ?></span>
    </span>
    <input type="text" class="form-control form-control-sm" style="width:200px"
           placeholder="Buscar..." oninput="filtrar(this.value)">
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0" id="tbl">
        <thead>
          <tr>
            <th class="ps-3">#</th><th>Nombre</th><th>Cédula</th><th>Teléfono</th>
            <th>Cargo</th><th class="text-end">Salario</th><th>Ingreso</th>
            <th>Usuario</th><th class="text-center">Estado</th><th class="text-center pe-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($e = $empleados->fetch_assoc()): ?>
          <tr data-buscar="<?= strtolower($e['nombre'].' '.$e['apellido'].' '.$e['cargo'].' '.($e['cedula']??'')) ?>">
            <td class="ps-3 text-muted small"><?= $e['id'] ?></td>
            <td><strong><?= htmlspecialchars($e['nombre'].' '.$e['apellido']) ?></strong></td>
            <td class="small text-muted"><?= htmlspecialchars($e['cedula']?:'—') ?></td>
            <td class="small text-muted"><?= htmlspecialchars($e['telefono']?:'—') ?></td>
            <td><?php
              $colores=['cajero'=>'bg-primary','gerente'=>'bg-warning text-dark','supervisor'=>'bg-info text-dark','almacenista'=>'bg-secondary','repartidor'=>'bg-success'];
              echo '<span class="badge '.($colores[$e['cargo']]??'bg-secondary').'" style="font-size:10px">'.ucfirst($e['cargo']).'</span>';
            ?></td>
            <td class="text-end small">RD$ <?= number_format($e['salario'],2) ?></td>
            <td class="small text-muted"><?= $e['fecha_ingreso'] ? date('d/m/Y',strtotime($e['fecha_ingreso'])) : '—' ?></td>
            <td class="small text-muted"><?= htmlspecialchars($e['usuario_email']?:'Sin asignar') ?></td>
            <td class="text-center">
              <a href="?toggle=<?= $e['id'] ?>" class="badge <?= $e['activo']?'bg-success':'bg-danger' ?> text-decoration-none">
                <?= $e['activo']?'Activo':'Inactivo' ?>
              </a>
            </td>
            <td class="text-center pe-3">
              <a href="?editar=<?= $e['id'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2 me-1"><i class="bi bi-pencil"></i></a>
              <a href="?eliminar=<?= $e['id'] ?>" class="btn btn-outline-danger btn-sm py-0 px-2"
                 onclick="return confirm('¿Eliminar a <?= addslashes($e['nombre'].' '.$e['apellido']) ?>?')">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
function filtrar(q){q=q.toLowerCase();document.querySelectorAll('#tbl tbody tr').forEach(r=>{r.style.display=(r.getAttribute('data-buscar')||'').includes(q)?'':'none';});}
</script>
<?php include '../views/layouts/footer.php'; ?>
