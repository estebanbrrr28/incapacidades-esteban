<?php
use Core\Config;
use Core\Security;

require_once __DIR__ . '/../shared/badge_estado.php';
$baseUrl = Config::baseUrl();
?>
<div class="page-header animate-fade-down">
  <div>
    <h1 class="page-title">Panel de Jefe Inmediato</h1>
    <p style="color:var(--muted);font-size:14px;margin-top:4px">Gestiona las solicitudes de tu equipo</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="<?= $baseUrl ?>/solicitud/crear" class="btn btn-green">+ Nueva solicitud</a>
    <a href="<?= $baseUrl ?>/exportar/todas/excel" class="btn btn-outline">Descargar reporte Excel</a>
  </div>
</div>

<div class="stats-row animate-fade-up" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
    <div class="num"><?= count($pendientes) ?></div>
    <div class="lbl">Pendientes de Aprobación</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
    <div class="num"><?= count($misSolicitudes) ?></div>
    <div class="lbl">Mis Solicitudes</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg></div>
    <div class="num"><?= count($pendientes) + count($misSolicitudes) ?></div>
    <div class="lbl">Total a Gestión</div>
  </div>
</div>

<div class="section-header"><h2>Solicitudes pendientes de tu aprobacion</h2></div>
<?php if (empty($pendientes)): ?>
  <div class="empty-state animate-fade-up"><p>No tienes solicitudes pendientes de gestionar.</p></div>
<?php else: ?>
<div class="ugc-table-wrap animate-fade-up">
  <table class="ugc-table">
    <thead><tr><th>#</th><th>Empleado (NIT)</th><th>Tipo</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($pendientes as $s): ?>
    <tr>
      <td data-label="#"><?= $s['ID'] ?></td>
      <td data-label="Empleado"><?= htmlspecialchars($s['NIT_EMPLEADO']) ?></td>
      <td data-label="Tipo"><?= htmlspecialchars($tipos[$s['TIPO_SOLICITUD']] ?? $s['TIPO_SOLICITUD']) ?> <?= !empty($s['RUTA_COMPROBANTE']) ? '<span title="Tiene PDF adjunto">📎</span>' : '' ?></td>
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

<div class="section-header section-header--spaced"><h2>Historial de solicitudes gestionadas</h2></div>
<?php if (empty($gestionadas)): ?>
  <div class="empty-state"><p>📋 Aún no has gestionado ninguna solicitud.</p></div>
<?php else: ?>
<div class="ugc-table-wrap">
  <table class="ugc-table">
    <thead><tr><th>#</th><th>Empleado (NIT)</th><th>Tipo</th><th>Mi Decisión</th><th>Estado Final</th><th>Fecha Gestión</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($gestionadas as $s): ?>
    <tr>
      <td data-label="#"><?= $s['ID'] ?></td>
      <td data-label="Empleado"><?= htmlspecialchars($s['NIT_EMPLEADO']) ?></td>
      <td data-label="Tipo"><?= htmlspecialchars($tipos[$s['TIPO_SOLICITUD']] ?? $s['TIPO_SOLICITUD']) ?></td>
      <td data-label="Mi Decisión">
        <?php if (in_array($s['ESTADO'], ['APROBADO_JEFE', 'APROBADO_RRHH'])): ?>
          <span style="color:var(--green2);font-weight:600">✅ Aprobada</span>
        <?php else: ?>
          <span style="color:var(--red);font-weight:600">❌ Rechazada</span>
        <?php endif; ?>
      </td>
      <td data-label="Estado Final"><?= badgeEstado($s['ESTADO']) ?></td>
      <td data-label="Fecha Gestión"><?= substr($s['FECHA_GESTION_JEFE'], 0, 10) ?></td>
      <td><a href="<?= $baseUrl ?>/solicitud/<?= $s['ID'] ?>/ver" class="btn btn-outline btn-sm">Ver</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<div class="section-header section-header--spaced"><h2>Mis solicitudes personales</h2></div>
<?php if (empty($misSolicitudes)): ?>
  <div class="empty-state"><p>📋 No tienes solicitudes propias.</p></div>
<?php else: ?>
<div class="ugc-table-wrap">
  <table class="ugc-table">
    <thead><tr><th>#</th><th>Tipo</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($misSolicitudes as $s): ?>
    <tr>
      <td data-label="#"><?= $s['ID'] ?></td>
      <td data-label="Tipo"><?= htmlspecialchars($tipos[$s['TIPO_SOLICITUD']] ?? $s['TIPO_SOLICITUD']) ?> <?= !empty($s['RUTA_COMPROBANTE']) ? '<span title="Tiene PDF adjunto">📎</span>' : '' ?></td>
      <td data-label="Inicio"><?= substr($s['FECHA_INICIO'], 0, 10) ?></td>
      <td data-label="Fin"><?= substr($s['FECHA_FIN'], 0, 10) ?></td>
      <td data-label="Estado"><?= badgeEstado($s['ESTADO']) ?></td>
      <td class="actions-cell">
        <a href="<?= $baseUrl ?>/solicitud/<?= $s['ID'] ?>/ver" class="btn btn-outline btn-sm">Ver</a>
        <?php if ($s['ESTADO'] === 'PENDIENTE_JEFE'): ?>
          <a href="<?= $baseUrl ?>/solicitud/<?= $s['ID'] ?>/editar" class="btn btn-gray btn-sm">Editar</a>
          <form method="post" action="<?= $baseUrl ?>/solicitud/<?= $s['ID'] ?>/eliminar" class="inline-form" onsubmit="return confirm('¿Eliminar?')">
            <?= Security::csrfField() ?>
            <button type="submit" class="btn btn-red btn-sm">Eliminar</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
