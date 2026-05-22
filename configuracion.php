<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();
$user = currentUser();
$pageTitle = 'Configuración';
include 'views/layouts/header.php';
?>

<style>
.config-container { max-width: 700px; margin: 0 auto; }
.config-section { background: var(--surface); border-radius: 16px; box-shadow: var(--shadow); margin-bottom: 20px; overflow: hidden; }
.config-section-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
.config-section-header i { font-size: 20px; color: var(--accent); }
.config-section-header h6 { margin: 0; font-weight: 700; font-size: 15px; color: var(--text); }
.config-section-header p { margin: 0; font-size: 12px; color: var(--text-muted); }
.config-item { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid var(--border); }
.config-item:last-child { border-bottom: none; }
.config-item-info label { font-size: 14px; font-weight: 600; color: var(--text); display: block; }
.config-item-info small { font-size: 12px; color: var(--text-muted); }
.toggle-switch { position: relative; width: 46px; height: 24px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: var(--border); border-radius: 24px; cursor: pointer; transition: .3s; }
.toggle-slider:before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .3s; }
input:checked + .toggle-slider { background: var(--accent); }
input:checked + .toggle-slider:before { transform: translateX(22px); }
.version-badge { background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px; padding: 4px 12px; font-size: 12px; color: var(--text-muted); }
</style>

<div class="config-container">

  <!-- APARIENCIA -->
  <div class="config-section">
    <div class="config-section-header">
      <i class="bi bi-palette"></i>
      <div>
        <h6>Apariencia</h6>
        <p>Personaliza la interfaz del sistema</p>
      </div>
    </div>
    <div class="config-item">
      <div class="config-item-info">
        <label>Modo Nocturno</label>
        <small>Cambia entre tema claro y oscuro</small>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" id="darkToggle" onchange="toggleTheme()">
        <span class="toggle-slider"></span>
      </label>
    </div>
    <div class="config-item">
      <div class="config-item-info">
        <label>Idioma del sistema</label>
        <small>Idioma de la interfaz</small>
      </div>
      <span class="version-badge">🇩🇴 Español</span>
    </div>
  </div>

  <!-- NOTIFICACIONES -->
  <div class="config-section">
    <div class="config-section-header">
      <i class="bi bi-bell"></i>
      <div>
        <h6>Notificaciones</h6>
        <p>Configura qué alertas quieres recibir</p>
      </div>
    </div>
    <div class="config-item">
      <div class="config-item-info">
        <label>Alertas de stock bajo</label>
        <small>Notificar cuando un producto esté bajo el mínimo</small>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" checked>
        <span class="toggle-slider"></span>
      </label>
    </div>
    <div class="config-item">
      <div class="config-item-info">
        <label>Productos por vencer</label>
        <small>Alertas de productos próximos a vencer</small>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" checked>
        <span class="toggle-slider"></span>
      </label>
    </div>
    <div class="config-item">
      <div class="config-item-info">
        <label>Notificaciones WhatsApp</label>
        <small>Enviar alertas por WhatsApp cada 8 horas</small>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" checked>
        <span class="toggle-slider"></span>
      </label>
    </div>
  </div>

  <!-- SISTEMA -->
  <div class="config-section">
    <div class="config-section-header">
      <i class="bi bi-info-circle"></i>
      <div>
        <h6>Información del sistema</h6>
        <p>Detalles técnicos de NEXSYS</p>
      </div>
    </div>
    <div class="config-item">
      <div class="config-item-info">
        <label>Versión</label>
        <small>Versión actual del sistema</small>
      </div>
      <span class="version-badge">NEXSYS v1.0.0</span>
    </div>
    <div class="config-item">
      <div class="config-item-info">
        <label>Base de datos</label>
        <small>Motor de base de datos</small>
      </div>
      <span class="version-badge">MySQL · Railway</span>
    </div>
    <div class="config-item">
      <div class="config-item-info">
        <label>Servidor</label>
        <small>Plataforma de despliegue</small>
      </div>
      <span class="version-badge">Railway · PHP 8</span>
    </div>
    <div class="config-item">
      <div class="config-item-info">
        <label>IA integrada</label>
        <small>Motor de inteligencia artificial</small>
      </div>
      <span class="version-badge">Groq · Llama 3.1</span>
    </div>
  </div>

</div>

<script>
// Sincronizar toggle con tema actual
(function(){
  const t = localStorage.getItem('mm-theme') || 'light';
  const toggle = document.getElementById('darkToggle');
  if (toggle) toggle.checked = t === 'dark';
})();
</script>

<?php include 'views/layouts/footer.php'; ?>
