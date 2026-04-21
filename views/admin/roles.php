<?php
use Core\Config;

$baseUrl = Config::baseUrl();
$rolesDisponibles = $rolesDisponibles ?? [
  ROL_ADMIN => 'Administrador',
  ROL_RRHH => 'Talento Humano',
  ROL_JEFE => 'Jefe Inmediato',
  ROL_EMPLEADO => 'Solicitante',
];
$rolLabelLookup = $rolesDisponibles;
?>

<div class="analytics-toolbar animate-fade-down">
  <div>
    <h1 class="page-title">Gestion de roles</h1>
    <p class="analytics-subtitle">
      Busca una cédula, revisa el rol actual y ajusta el acceso sin tocar Oracle.
    </p>
  </div>
  <div class="analytics-actions">
    <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline">Volver al panel</a>
    <a href="<?= $baseUrl ?>/dashboard/analitica" class="btn btn-green">Ver analitica</a>
  </div>
</div>

<form method="get" action="<?= $baseUrl ?>/dashboard/roles" class="analytics-filter-card animate-fade-up">
  <div class="analytics-filter-grid role-manager-search">
    <div class="analytics-field role-manager-field">
      <label for="cedula">Cédula</label>
      <input id="cedula" name="cedula" type="text" inputmode="numeric" placeholder="Ej: 1234567890" value="<?= htmlspecialchars($cedulaBusqueda ?? '') ?>">
    </div>
  </div>

  <div class="analytics-filter-actions" style="margin-top:16px;">
    <button type="submit" class="btn btn-green">Buscar usuario</button>
    <a href="<?= $baseUrl ?>/dashboard/roles" class="btn btn-gray">Limpiar</a>
  </div>
</form>

<?php if (!empty($roleSearchError)): ?>
  <div class="flash flash-err role-manager-feedback animate-fade-up">
    <?= htmlspecialchars($roleSearchError) ?>
  </div>
<?php endif; ?>

<?php if (!empty($usuarioRol)): ?>
  <section class="analytics-filter-card role-manager-card animate-fade-up">
    <div class="section-header role-manager-header">
      <div>
        <h2>Resultado de la búsqueda</h2>
        <p class="role-manager-copy">Puedes forzar un rol manual o devolver el usuario al cálculo automático.</p>
      </div>
    </div>

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
  </section>
<?php endif; ?>