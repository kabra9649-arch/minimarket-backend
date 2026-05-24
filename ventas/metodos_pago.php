<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();
$pageTitle = 'Métodos de Pago';
include '../views/layouts/header.php';
?>

<style>
.metodo-card {
    border-radius: 16px;
    padding: 28px;
    text-align: center;
    transition: transform .2s, box-shadow .2s;
    border: none;
    color: #fff;
}
.metodo-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,.2);
}
.metodo-icon {
    font-size: 52px;
    margin-bottom: 12px;
    display: block;
}
.metodo-nombre {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 6px;
}
.metodo-desc {
    font-size: 13px;
    opacity: .85;
    line-height: 1.5;
}
.metodo-badge {
    display: inline-block;
    background: rgba(255,255,255,.2);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-top: 12px;
}
</style>

<div class="row g-3 mb-4">
  <div class="col-12">
    <h6 class="fw-bold mb-0"><i class="bi bi-credit-card me-2"></i>Métodos de Pago Disponibles</h6>
    <small class="text-muted">Métodos aceptados en este establecimiento</small>
  </div>
</div>

<div class="row g-3">

  <!-- Efectivo -->
  <div class="col-12 col-md-4">
    <div class="metodo-card" style="background:linear-gradient(135deg,#0F6E56,#1a9e7a);">
      <i class="bi bi-cash-stack metodo-icon"></i>
      <div class="metodo-nombre">Efectivo</div>
      <div class="metodo-desc">Pago directo con billetes y monedas. El método más rápido y universal.</div>
      <span class="metodo-badge">✅ Disponible</span>
    </div>
  </div>

  <!-- Tarjeta -->
  <div class="col-12 col-md-4">
    <div class="metodo-card" style="background:linear-gradient(135deg,#1F4E79,#2E75B6);">
      <i class="bi bi-credit-card-2-front metodo-icon"></i>
      <div class="metodo-nombre">Tarjeta</div>
      <div class="metodo-desc">Débito o crédito. Visa, Mastercard y American Express aceptadas.</div>
      <span class="metodo-badge">✅ Disponible</span>
    </div>
  </div>

  <!-- Transferencia -->
  <div class="col-12 col-md-4">
    <div class="metodo-card" style="background:linear-gradient(135deg,#5B4FCF,#7a6de0);">
      <i class="bi bi-phone metodo-icon"></i>
      <div class="metodo-nombre">Transferencia</div>
      <div class="metodo-desc">Transferencia bancaria o pago móvil. Incluye apps como Azul y otros.</div>
      <span class="metodo-badge">✅ Disponible</span>
    </div>
  </div>

</div>

<!-- Info adicional -->
<div class="row g-3 mt-2">
  <div class="col-12">
    <div class="card" style="border-left:4px solid #BF5800;">
      <div class="card-body py-3">
        <div class="d-flex align-items-start gap-3">
          <i class="bi bi-info-circle-fill fs-4" style="color:#BF5800;"></i>
          <div>
            <div class="fw-bold mb-1">Información importante</div>
            <ul class="mb-0 small text-muted" style="line-height:2;">
              <li>Para pagos con <strong>tarjeta</strong>, solicita la terminal POS al supervisor.</li>
              <li>Para <strong>transferencias</strong>, verifica el comprobante antes de completar la venta.</li>
              <li>El <strong>efectivo</strong> no requiere verificación adicional.</li>
              <li>En caso de duda, consulta con el administrador antes de procesar.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
