<?php
use Core\Config;

require_once __DIR__ . '/../shared/badge_estado.php';
$baseUrl = Config::baseUrl();
$pendientesRrhh = is_array($pendientes ?? null) ? $pendientes : [];
$todasRows      = is_array($todas ?? null) ? $todas : [];
$cntRevisionJefe = count(array_filter($todasRows, fn($s) => ($s['ESTADO'] ?? '') === 'PENDIENTE_JEFE'));
?>
<div class="page-header animate-fade-down">
  <div>
    <h1 class="page-title">Talento Humano</h1>
    <p style="color:var(--muted);font-size:14px;margin-top:4px">Gestión de aprobaciones finales</p>
  </div>
  <a href="<?= $baseUrl ?>/exportar/todas/excel" class="btn btn-outline">Descargar reporte Excel</a>
</div>

<div class="stats-row animate-fade-up" style="grid-template-columns:repeat(3,1fr)" id="rrhh-stat-cards">
  <a href="#rrhh-pendientes" class="stat-card stat-card-link is-active" data-filter="todo">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
    <div class="num"><?= count($todasRows) ?></div>
    <div class="lbl">Todo</div>
  </a>
  <a href="#rrhh-pendientes" class="stat-card stat-card-link" data-filter="pendientes_rrhh">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
    <div class="num"><?= count($pendientesRrhh) ?></div>
    <div class="lbl">Pendientes RRHH</div>
  </a>
  <a href="#rrhh-historico" class="stat-card stat-card-link" data-filter="revision_jefe">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
    <div class="num"><?= $cntRevisionJefe ?></div>
    <div class="lbl">En Revisión Jefe</div>
  </a>
</div>

<div id="rrhh-section-pendientes">
<div id="rrhh-pendientes" class="section-header"><h2>Aprobadas por jefe · pendientes de RRHH</h2></div>
<?php if (empty($pendientesRrhh)): ?>
  <div class="empty-state animate-fade-up"><p>No hay solicitudes pendientes de aprobación por RRHH.</p></div>
<?php else: ?>
<div class="ugc-table-wrap animate-fade-up">
  <table class="ugc-table">
    <thead><tr><th>#</th><th>Empleado</th><th>Tipo</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($pendientesRrhh as $s): ?>
    <tr>
      <td data-label="#"><?= $s['ID'] ?></td>
      <td data-label="Empleado"><?= htmlspecialchars($s['NIT_EMPLEADO']) ?></td>
        <td data-label="Tipo"><?= htmlspecialchars($tipos[$s['TIPO_SOLICITUD']] ?? $s['TIPO_SOLICITUD']) ?></td>
      <td data-label="Inicio"><?= substr($s['FECHA_INICIO'], 0, 10) ?></td>
      <td data-label="Fin"><?= substr($s['FECHA_FIN'], 0, 10) ?></td>
      <td data-label="Estado"><?= badgeEstado($s['ESTADO']) ?></td>
      <td><a href="<?= $baseUrl ?>/solicitud/<?= $s['ID'] ?>/ver" class="btn btn-green btn-sm">Gestionar</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
</div>

<div id="rrhh-section-historico">
<div id="rrhh-historico" class="section-header section-header--spaced"><h2 id="rrhh-historico-titulo">Historial completo</h2></div>
<?php $filas = $todasRows; require __DIR__ . '/../shared/tabla_solicitudes.php'; ?>
</div>

<div id="rrhh-section-revision" style="display:none">
<div class="section-header section-header--spaced"><h2>Solicitudes en revisión de jefe</h2></div>
<?php
$filas = array_values(array_filter($todasRows, fn($s) => ($s['ESTADO'] ?? '') === 'PENDIENTE_JEFE'));
require __DIR__ . '/../shared/tabla_solicitudes.php';
?>
</div>

<script <?= \Core\Security::scriptNonceAttr() ?>>
(function () {
  var tarjetas = document.querySelectorAll('#rrhh-stat-cards [data-filter]');
  var secPendientes = document.getElementById('rrhh-section-pendientes');
  var secHistorico  = document.getElementById('rrhh-section-historico');
  var secRevision   = document.getElementById('rrhh-section-revision');

  function filtrarRrhh(f) {
    tarjetas.forEach(function (c) {
      c.classList.toggle('is-active', c.dataset.filter === f);
    });
    if (f === 'todo') {
      secPendientes.style.display = '';
      secHistorico.style.display  = '';
      secRevision.style.display   = 'none';
      var sec = document.getElementById('rrhh-pendientes');
      if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else if (f === 'pendientes_rrhh') {
      secPendientes.style.display = '';
      secHistorico.style.display  = 'none';
      secRevision.style.display   = 'none';
      var sec = document.getElementById('rrhh-pendientes');
      if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else if (f === 'revision_jefe') {
      secPendientes.style.display = 'none';
      secHistorico.style.display  = 'none';
      secRevision.style.display   = '';
      secRevision.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  tarjetas.forEach(function (t) {
    t.addEventListener('click', function (e) {
      e.preventDefault();
      filtrarRrhh(this.dataset.filter);
    });
  });
})();
</script>
