<?php
use Core\Config;
use Core\Security;

require_once __DIR__ . '/../shared/badge_estado.php';
$baseUrl = Config::baseUrl();

$aprobadas = count(array_filter($solicitudes, fn($s) => in_array($s['ESTADO'], ['APROBADO_JEFE', 'APROBADO_RRHH'])));
$pendientes = count(array_filter($solicitudes, fn($s) => $s['ESTADO'] === 'PENDIENTE_JEFE'));
$rechazadas = count(array_filter($solicitudes, fn($s) => in_array($s['ESTADO'], ['RECHAZADO_JEFE', 'RECHAZADO_RRHH'])));
?>

<div class="page-header animate-fade-down" style="display:flex;justify-content:space-between;align-items:center;">
  
  <div>
    <h1 class="page-title">Panel de Empleado</h1>
    <p style="color:var(--muted);font-size:14px;margin-top:4px">
      Vista general del sistema de solicitudes
    </p>
  </div>

  <div style="display:flex;gap:10px;">
    <a href="<?= $baseUrl ?>/solicitud/crear" class="btn btn-green">
      + Nueva solicitud
    </a>
  </div>

</div>


<?php if (!empty($solicitudes)): ?>
<div class="stats-row animate-fade-up" style="grid-template-columns:repeat(4,1fr)">
  <div class="stat-card">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
    <div class="num"><?= count($solicitudes) ?></div>
    <div class="lbl">Total</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
    <div class="num"><?= $pendientes ?></div>
    <div class="lbl">Pendientes</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
    <div class="num"><?= $aprobadas ?></div>
    <div class="lbl">Aprobadas</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
    <div class="num"><?= $rechazadas ?></div>
    <div class="lbl">Rechazadas</div>
  </div>
</div>
<?php endif; ?>

<?php if (empty($solicitudes)): ?>
  <div class="empty-state animate-fade-up"><p>Aun no tienes solicitudes.<br>Haz clic en <strong>Nueva solicitud</strong> para comenzar.</p></div>
<?php else: ?>
<div class="ugc-table-wrap animate-fade-up">
  <table class="ugc-table">
    <thead><tr><th>#</th><th>Tipo DE SOLICITUD</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($solicitudes as $s): ?>
    <tr>
      <td data-label="#"><?= $s['ID'] ?></td>
      <td data-label="Tipo DE SOLICITUD"><?= htmlspecialchars($tipos[$s['TIPO_SOLICITUD']] ?? $s['TIPO_SOLICITUD']) ?></td>
      <td data-label="Inicio"><?= substr($s['FECHA_INICIO'], 0, 10) ?></td>
      <td data-label="Fin"><?= substr($s['FECHA_FIN'], 0, 10) ?></td>
      <td data-label="Estado"><?= badgeEstado($s['ESTADO']) ?></td>
      <td class="actions-cell">
        <a href="<?= $baseUrl ?>/solicitud/<?= $s['ID'] ?>/ver" class="btn btn-outline btn-sm">Ver</a>
        <?php if (!empty($s['RUTA_COMPROBANTE'])): ?>
          <a href="<?= $baseUrl ?>/<?= htmlspecialchars($s['RUTA_COMPROBANTE']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm">Ver PDF</a>
        <?php endif; ?>
        <?php if ($s['ESTADO'] === 'PENDIENTE_JEFE'): ?>
          <a href="<?= $baseUrl ?>/solicitud/<?= $s['ID'] ?>/editar" class="btn btn-gray btn-sm">Editar</a>
          <form method="post" action="<?= $baseUrl ?>/solicitud/<?= $s['ID'] ?>/eliminar" class="inline-form" onsubmit="return confirm('¿Eliminar esta solicitud?')">
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
