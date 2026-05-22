<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();
$db = getDB();
$user = currentUser();
$pageTitle = 'Mi Perfil';

// Guardar foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $file = $_FILES['foto'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];
    if (in_array($ext, $allowed) && $file['size'] < 2*1024*1024) {
        $filename = 'avatar_' . $user['id'] . '.' . $ext;
        $dest = __DIR__ . '/uploads/avatars/' . $filename;
        if (!is_dir(__DIR__ . '/uploads/avatars')) mkdir(__DIR__ . '/uploads/avatars', 0755, true);
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $db->query("UPDATE usuarios SET foto='/uploads/avatars/$filename' WHERE id=" . (int)$user['id']);
            $_SESSION['toast'] = ['tipo'=>'success','mensaje'=>'Foto actualizada correctamente.'];
            header('Location: perfil.php'); exit();
        }
    } else {
        $_SESSION['toast'] = ['tipo'=>'error','mensaje'=>'Formato no válido o archivo muy grande (máx 2MB).'];
        header('Location: perfil.php'); exit();
    }
}

// Obtener datos completos del usuario
$u = $db->query("SELECT * FROM usuarios WHERE id=" . (int)$user['id'])->fetch_assoc();
$iniciales = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $u['nombre']), 0, 2))));

// Historial de accesos (si tienes tabla logs, si no mostramos datos estáticos)
$ultimoAcceso = date('d/m/Y H:i');

include 'views/layouts/header.php';
?>

<style>
.perfil-container { max-width: 700px; margin: 0 auto; }
.perfil-card { background: var(--surface); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow); margin-bottom: 20px; }
.perfil-banner { height: 120px; background: linear-gradient(135deg, var(--primary), var(--accent)); position: relative; }
.perfil-avatar-wrap { position: absolute; bottom: -50px; left: 32px; }
.perfil-avatar {
    width: 100px; height: 100px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border: 4px solid var(--surface);
    display: flex; align-items: center; justify-content: center;
    font-size: 36px; font-weight: 700; color: #fff;
    overflow: hidden; position: relative;
}
.perfil-avatar img { width: 100%; height: 100%; object-fit: cover; }
.perfil-avatar-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .2s; cursor: pointer; border-radius: 50%;
}
.perfil-avatar-wrap:hover .perfil-avatar-overlay { opacity: 1; }
.perfil-avatar-overlay i { color: #fff; font-size: 22px; }
.perfil-info { padding: 60px 32px 28px; }
.perfil-nombre { font-size: 22px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.perfil-email { color: var(--text-muted); font-size: 13px; margin-bottom: 12px; }
.perfil-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.badge-rol { background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-estado { background: #dcfce7; color: #166534; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 24px 32px; border-top: 1px solid var(--border); }
.info-item label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .8px; display: block; margin-bottom: 4px; }
.info-item span { font-size: 14px; color: var(--text); font-weight: 500; }

.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 0 32px 24px; }
.stat-card { background: var(--surface-2); border-radius: 12px; padding: 16px; text-align: center; border: 1px solid var(--border); }
.stat-card .stat-val { font-size: 22px; font-weight: 700; color: var(--primary); }
.stat-card .stat-lbl { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

.foto-upload-area { padding: 24px 32px; border-top: 1px solid var(--border); }
.upload-btn { background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; border: none; border-radius: 10px; padding: 10px 20px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
</style>

<div class="perfil-container">

  <!-- CARD PRINCIPAL -->
  <div class="perfil-card">
    <div class="perfil-banner"></div>
    <div class="perfil-avatar-wrap">
      <div class="perfil-avatar" onclick="document.getElementById('fotoInput').click()">
        <?php if (!empty($u['foto'])): ?>
          <img src="<?= htmlspecialchars($u['foto']) ?>" alt="Avatar">
        <?php else: ?>
          <?= $iniciales ?>
        <?php endif; ?>
        <div class="perfil-avatar-overlay"><i class="bi bi-camera-fill"></i></div>
      </div>
    </div>
    <div class="perfil-info">
      <div class="perfil-nombre"><?= htmlspecialchars($u['nombre']) ?></div>
      <div class="perfil-email"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($u['email'] ?? '—') ?></div>
      <div class="perfil-badges">
        <span class="badge-rol"><i class="bi bi-shield-check me-1"></i><?= ucfirst($u['rol']) ?></span>
        <span class="badge-estado"><i class="bi bi-circle-fill me-1" style="font-size:8px"></i>Activo</span>
      </div>
    </div>

    <!-- INFO -->
    <div class="info-grid">
      <div class="info-item">
        <label>Miembro desde</label>
        <span><?= isset($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : 'N/A' ?></span>
      </div>
      <div class="info-item">
        <label>Último acceso</label>
        <span><?= $ultimoAcceso ?></span>
      </div>
      <div class="info-item">
        <label>Rol</label>
        <span><?= ucfirst($u['rol']) ?></span>
      </div>
      <div class="info-item">
        <label>Estado</label>
        <span style="color:#16a34a">● Activo</span>
      </div>
    </div>

    <!-- FOTO UPLOAD -->
    <div class="foto-upload-area">
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">Haz clic en tu avatar para cambiar la foto, o usa el botón:</p>
      <form method="POST" enctype="multipart/form-data">
        <input type="file" name="foto" id="fotoInput" accept="image/*" style="display:none" onchange="this.form.submit()">
        <button type="button" class="upload-btn" onclick="document.getElementById('fotoInput').click()">
          <i class="bi bi-camera-fill"></i> Cambiar foto de perfil
        </button>
        <small style="display:block;margin-top:8px;color:var(--text-muted);">JPG, PNG o WebP · Máximo 2MB</small>
      </form>
    </div>
  </div>

</div>

<?php include 'views/layouts/footer.php'; ?>
