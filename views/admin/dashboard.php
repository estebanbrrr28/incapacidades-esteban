
<?php
use Core\Config;

require_once __DIR__ . '/../shared/badge_estado.php';
$baseUrl = Config::baseUrl();
$labels = ['PENDIENTE_JEFE' => 'Pendiente Jefe', 'APROBADO_JEFE' => 'Aprobado Jefe', 'RECHAZADO_JEFE' => 'Rechazado Jefe', 'APROBADO_RRHH' => 'Aprobado RRHH', 'RECHAZADO_RRHH' => 'Rechazado RRHH'];
$icons = [
  'PENDIENTE_JEFE' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'APROBADO_JEFE' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
  'RECHAZADO_JEFE' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
  'APROBADO_RRHH' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
  'RECHAZADO_RRHH' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
];

$total = array_sum($stats ?? []);
$panelTitle = (($user['rol'] ?? '') === ROL_RRHH) ? 'Panel de Talento Humano' : 'Panel de Administrador';
?>
<div class="page-header animate-fade-down" style="display:flex;justify-content:space-between;align-items:center;">
  
  <div>
    <h1 class="page-title"><?= $panelTitle ?></h1>
    <p style="color:var(--muted);font-size:14px;margin-top:4px">
      Vista general del sistema de solicitudes
    </p>
  </div>

  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <?php if ($esAdminReal ?? false): ?>
    <a href="<?= $baseUrl ?>/dashboard/analitica" class="btn btn-outline">
      Ver analítica
    </a>
    <?php endif; ?>

    <a href="<?= $baseUrl ?>/solicitud/crear" class="btn btn-green">
      + Nueva solicitud
    </a>

    <a href="<?= $baseUrl ?>/exportar/todas/excel" class="btn btn-green">
      Descargar reporte Excel
    </a>
  </div>

</div>


<div class="stats-row animate-fade-up">
<?php foreach ($labels as $key => $lbl): ?>
  <a href="<?= $baseUrl ?>/solicitudes?estado=<?= urlencode($key) ?>#admin-requests-section" class="stat-card stat-card-link <?= (($filtros['estado'] ?? '') === $key) ? 'is-active' : '' ?>">
    <div class="stat-icon"><?= $icons[$key] ?></div>
    <div class="num"><?= $stats[$key] ?? 0 ?></div>
    <div class="lbl"><?= $lbl ?></div>
  </a>
<?php endforeach; ?>
  <a href="<?= $baseUrl ?>/solicitudes#admin-requests-section" class="stat-card stat-card-link <?= empty($filtros['estado']) ? 'is-active' : '' ?>">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
    <div class="num"><?= $total ?></div>
    <div class="lbl">Total Solicitudes</div>
  </a>
</div>

<div id="admin-requests-section" class="section-header">
  <h2><?= !empty($filtros['estado']) ? 'Solicitudes filtradas por estado' : 'Todas las solicitudes' ?></h2>
  <form method="get" action="<?= $baseUrl ?>/solicitudes" class="filter-form">
    <select name="estado" class="filter-select">
      <option value="">Todos los estados</option>
      <?php foreach ($labels as $k => $v): ?><option value="<?= $k ?>" <?= (($filtros['estado'] ?? '') === $k) ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
    </select>
    <select name="tipo" class="filter-select">
      <option value="">Todos los tipos</option>
      <?php foreach ($tipos as $k => $v): ?><option value="<?= $k ?>" <?= (($filtros['tipo'] ?? '') === $k) ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
    <a href="<?= $baseUrl ?>/solicitudes" class="btn btn-gray btn-sm">Limpiar</a>
  </form>
</div>
<?php $filas = $todas; require __DIR__ . '/../shared/tabla_solicitudes.php'; ?>
