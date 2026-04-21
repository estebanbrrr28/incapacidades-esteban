
<?php
use Core\Config;

require_once __DIR__ . '/../shared/badge_estado.php';
$baseUrl = Config::baseUrl();
$labels = ['PENDIENTE_JEFE' => 'Pendiente Jefe', 'APROBADO_JEFE' => 'Aprobado Jefe', 'RECHAZADO_JEFE' => 'Rechazado Jefe', 'APROBADO_RRHH' => 'Aprobado RRHH', 'RECHAZADO_RRHH' => 'Rechazado RRHH'];
$rolesDisponibles = $rolesDisponibles ?? [
  ROL_ADMIN => 'Administrador',
  ROL_RRHH => 'Talento Humano',
  ROL_JEFE => 'Jefe Inmediato',
  ROL_EMPLEADO => 'Solicitante',
];
$rolLabelLookup = $rolesDisponibles;
$icons = [
  'PENDIENTE_JEFE' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'APROBADO_JEFE' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
  'RECHAZADO_JEFE' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
  'APROBADO_RRHH' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
  'RECHAZADO_RRHH' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
];

$total = array_sum($stats ?? []);
?>
<div class="page-header animate-fade-down" style="display:flex;justify-content:space-between;align-items:center;">
  
  <div>
    <h1 class="page-title">Panel de Administración</h1>
    <p style="color:var(--muted);font-size:14px;margin-top:4px">
      Vista general del sistema de solicitudes
    </p>
  </div>

  <div style="display:flex;gap:10px;">
    <a href="<?= $baseUrl ?>/dashboard/analitica" class="btn btn-outline">
      Ver analitica
    </a>

    <a href="<?= $baseUrl ?>/solicitud/crear" class="btn btn-green">
      + Nueva solicitud
    </a>

    <a href="<?= $baseUrl ?>/exportar/todas/excel" class="btn btn-green">
      Descargar reporte Excel
    </a>
  </div>

</div>

<section class="analytics-filter-card role-manager-card animate-fade-up">
  <div class="section-header role-manager-header">
    <div>
      <h2>Gestion de roles</h2>
      <p class="role-manager-copy">Busca una cédula, revisa el rol actual y cambia el acceso sin tocar Oracle.</p>
    </div>
  </div>

  <form method="get" action="<?= $baseUrl ?>/dashboard" class="analytics-filter-grid role-manager-search">
    <div class="analytics-field role-manager-field">
      <label for="cedula">Cédula</label>
      <input id="cedula" name="cedula" type="text" inputmode="numeric" placeholder="Ej: 1234567890" value="<?= htmlspecialchars($cedulaBusqueda ?? '') ?>">
    </div>
    <div class="role-manager-actions">
      <button type="submit" class="btn btn-green">Buscar usuario</button>
      <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline">Limpiar</a>
    </div>
  </form>

  <?php if (!empty($roleSearchError)): ?>
    <div class="flash flash-err role-manager-feedback">
      <?= htmlspecialchars($roleSearchError) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($usuarioRol)): ?>
    <div class="detail-card role-result-card">
      <dl class="role-detail-list">
        <div class="detail-row">
          <dt>Nombre</dt>
          <dd><?= htmlspecialchars($usuarioRol['nombre'] ?? '') ?></dd>
        </div>
        <div class="detail-row">
          <dt>Cédula</dt>
          <dd><?= htmlspecialchars($usuarioRol['cedula'] ?? '') ?></dd>
        </div>
        <div class="detail-row">
          <dt>Centro de costo</dt>
          <dd><?= htmlspecialchars($usuarioRol['centro_costo'] ?? '') ?></dd>
        </div>
        <div class="detail-row">
          <dt>Nivel</dt>
          <dd><?= htmlspecialchars((string) ($usuarioRol['nivel'] ?? 0)) ?></dd>
        </div>
        <div class="detail-row">
          <dt>Rol actual</dt>
          <dd><?= htmlspecialchars($rolLabelLookup[$usuarioRol['rol_actual'] ?? ''] ?? 'Sin rol') ?></dd>
        </div>
        <div class="detail-row">
          <dt>Rol base Oracle</dt>
          <dd><?= htmlspecialchars($rolLabelLookup[$usuarioRol['rol_base'] ?? ''] ?? 'Sin rol') ?></dd>
        </div>
        <div class="detail-row">
          <dt>Override manual</dt>
          <dd><?= htmlspecialchars(isset($usuarioRol['rol_manual']) ? ($rolLabelLookup[$usuarioRol['rol_manual']] ?? (string) $usuarioRol['rol_manual']) : 'Automatico') ?></dd>
        </div>
      </dl>

      <form method="post" action="<?= $baseUrl ?>/admin/roles" class="role-update-form">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="cedula" value="<?= htmlspecialchars($usuarioRol['cedula'] ?? '') ?>">
        <div class="analytics-field">
          <label for="rol">Nuevo rol</label>
          <select id="rol" name="rol">
            <option value="auto" <?= empty($usuarioRol['rol_manual']) ? 'selected' : '' ?>>Automatico (según Oracle)</option>
            <?php foreach ($rolesDisponibles as $roleKey => $roleLabel): ?>
              <option value="<?= htmlspecialchars($roleKey) ?>" <?= (($usuarioRol['rol_manual'] ?? '') === $roleKey) ? 'selected' : '' ?>><?= htmlspecialchars($roleLabel) ?></option>
            <?php endforeach; ?>
          </select>
          <span class="field-hint">El cambio afecta el acceso en el próximo inicio de sesión. Si cambias tu propio rol, esta sesión se ajusta al guardar.</span>
        </div>
        <div class="role-manager-actions role-manager-actions--inline">
          <button type="submit" class="btn btn-green">Guardar rol</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</section>


<div class="stats-row animate-fade-up">
<?php foreach ($labels as $key => $lbl): ?>
  <div class="stat-card">
    <div class="stat-icon"><?= $icons[$key] ?></div>
    <div class="num"><?= $stats[$key] ?? 0 ?></div>
    <div class="lbl"><?= $lbl ?></div>
  </div>
<?php endforeach; ?>
  <div class="stat-card">
    <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
    <div class="num"><?= $total ?></div>
    <div class="lbl">Total Solicitudes</div>
  </div>
</div>

<div class="section-header">
  <h2>Todas las solicitudes</h2>
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
