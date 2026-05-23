  </div><!-- end content-area -->
</div><!-- end main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── SWEETALERT2 TEMA NEXSYS ─────────────────────────────
const SwalNexsys = Swal.mixin({
  customClass: {
    popup:         'swal-nexsys-popup',
    confirmButton: 'swal-nexsys-confirm',
    cancelButton:  'swal-nexsys-cancel',
    title:         'swal-nexsys-title',
    htmlContainer: 'swal-nexsys-html',
  },
  buttonsStyling: false,
  background: 'var(--surface)',
  color: 'var(--text)',
});

// ── FUNCIÓN GLOBAL DE CONFIRMACIÓN ──────────────────────
function confirmar(e, url, msg, tipo = 'eliminar') {
  e.preventDefault();
  const isDanger = tipo === 'eliminar';
  SwalNexsys.fire({
    icon: isDanger ? 'warning' : 'question',
    title: isDanger ? '¿Confirmar eliminación?' : '¿Confirmar acción?',
    html: `<span style="font-size:14px;color:var(--text-muted)">${msg}</span>`,
    showCancelButton: true,
    confirmButtonText: isDanger ? '<i class="bi bi-trash me-1"></i>Sí, eliminar' : '<i class="bi bi-check me-1"></i>Confirmar',
    cancelButtonText: '<i class="bi bi-x me-1"></i>Cancelar',
    reverseButtons: true,
    focusCancel: true,
  }).then(r => { if (r.isConfirmed) window.location.href = url; });
}

// ── FUNCIÓN GLOBAL TOAST ────────────────────────────────
function showToast(tipo, msg) {
  const iconos = { success:'#10B981', error:'#EF4444', warning:'#F59E0B', info:'#2E75B6' };
  const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false,
    timer: 3500, timerProgressBar: true,
    background: 'var(--surface)',
    color: 'var(--text)',
    didOpen: t => { t.addEventListener('mouseenter', Swal.stopTimer); t.addEventListener('mouseleave', Swal.resumeTimer); }
  });
  Toast.fire({ icon: tipo, title: msg });
}

// ── BÚSQUEDA EN TIEMPO REAL ──────────────────────────────
function filtrar(q) {
  q = q.toLowerCase().trim();
  const tablas = document.querySelectorAll('table tbody');
  tablas.forEach(tbody => {
    let visible = 0;
    tbody.querySelectorAll('tr').forEach(tr => {
      const txt = (tr.getAttribute('data-buscar') || tr.textContent).toLowerCase();
      const show = !q || txt.includes(q);
      tr.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    // Mostrar mensaje si no hay resultados
    let noRes = tbody.parentElement.querySelector('.no-resultados');
    if (!noRes) {
      noRes = document.createElement('tr');
      noRes.className = 'no-resultados';
      noRes.innerHTML = '<td colspan="20" class="text-center py-4 text-muted"><i class="bi bi-search me-2"></i>No se encontraron resultados</td>';
      tbody.appendChild(noRes);
    }
    noRes.style.display = visible === 0 ? '' : 'none';
  });
}

// ── ESTILOS SWEETALERT2 ─────────────────────────────────
const swalStyles = document.createElement('style');
swalStyles.textContent = `
  .swal-nexsys-popup {
    border-radius: 18px !important;
    border: 1px solid var(--border) !important;
    box-shadow: 0 24px 80px rgba(0,0,0,0.25) !important;
    font-family: 'Segoe UI', sans-serif !important;
  }
  .swal-nexsys-title {
    font-size: 17px !important;
    font-weight: 700 !important;
    color: var(--text) !important;
  }
  .swal-nexsys-confirm {
    background: linear-gradient(135deg, #b91c1c, #ef4444) !important;
    color: #fff !important;
    border: none !important;
    padding: 10px 22px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    cursor: pointer !important;
    transition: opacity .2s !important;
  }
  .swal-nexsys-confirm:hover { opacity: .88 !important; }
  .swal-nexsys-cancel {
    background: var(--surface-2) !important;
    color: var(--text-muted) !important;
    border: 1.5px solid var(--border) !important;
    padding: 10px 22px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    cursor: pointer !important;
    transition: background .2s !important;
  }
  .swal-nexsys-cancel:hover { background: var(--border) !important; }
  .swal2-timer-progress-bar { background: #2E75B6 !important; }
`;
document.head.appendChild(swalStyles);

// ── LEER TOASTS DE PHP ──────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.php-toast').forEach(el => {
    showToast(el.dataset.tipo, el.dataset.msg);
    el.remove();
  });
});
</script>

<?php if (isset($extraJS)) echo $extraJS; ?>
</body>
</html>
