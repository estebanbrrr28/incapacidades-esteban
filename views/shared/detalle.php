<?php
use Core\Config;
use Core\Security;

require_once __DIR__ . '/badge_estado.php';
$baseUrl = Config::baseUrl();
?>
<div class="page-header animate-fade-down">
  <h1 class="page-title">Solicitud #<?= $solicitud['ID'] ?></h1>
  <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline btn-sm">Volver</a>
</div>

<div class="detail-card animate-fade-up">
  <dl>
    <div class="detail-row"><dt>Estado</dt><dd><?= badgeEstado($solicitud['ESTADO']) ?></dd></div>
    <div class="detail-row"><dt>Empleado (NIT)</dt><dd><?= htmlspecialchars($solicitud['NIT_EMPLEADO']) ?></dd></div>
    <div class="detail-row"><dt>Jefe (NIT)</dt><dd><?= htmlspecialchars($solicitud['NIT_JEFE']) ?></dd></div>
    <div class="detail-row"><dt>Tipo</dt><dd><?= htmlspecialchars($tipos[$solicitud['TIPO_SOLICITUD']] ?? $solicitud['TIPO_SOLICITUD']) ?></dd></div>
    <div class="detail-row"><dt>Fecha inicio</dt><dd><?= substr($solicitud['FECHA_INICIO'], 0, 10) ?></dd></div>
    <div class="detail-row"><dt>Fecha fin</dt><dd><?= substr($solicitud['FECHA_FIN'], 0, 10) ?></dd></div>
    <div class="detail-row"><dt>Horas</dt><dd><?= $solicitud['DURACION_HORAS'] ?? '—' ?></dd></div>
    <div class="detail-row"><dt>Días</dt><dd><?= $solicitud['DURACION_DIAS'] ?? '—' ?></dd></div>
    <div class="detail-row"><dt>Observaciones</dt><dd><?= nl2br(htmlspecialchars($solicitud['OBSERVACIONES'] ?? '')) ?: '—' ?></dd></div>
    <?php if (!empty($solicitud['RUTA_COMPROBANTE'])): ?>
    <div class="detail-row"><dt>Documento adjunto</dt>
      <dd>
        <div class="archivo-adjunto" style="margin:0;max-width:400px;">
          <span class="archivo-icono">📑</span>
          <div class="archivo-info">
            <div class="archivo-nombre"><?php
              $rawName = pathinfo(basename($solicitud['RUTA_COMPROBANTE']), PATHINFO_FILENAME);
              $parts = explode('_', $rawName);
              $label = count($parts) >= 4 ? implode('_', array_slice($parts, 3)) : $rawName;
              echo htmlspecialchars($label ?: 'Documento') . '.pdf';
            ?></div>
          </div>
          <a href="<?= $baseUrl ?>/<?= htmlspecialchars($solicitud['RUTA_COMPROBANTE']) ?>" target="_blank" class="archivo-ver">Ver PDF</a>
        </div>
      </dd>
    </div>
    <?php endif; ?>
    <?php if (!empty($solicitud['OBSERVACION_JEFE'])): ?>
    <div class="detail-row"><dt>Obs. Jefe</dt><dd><?= nl2br(htmlspecialchars($solicitud['OBSERVACION_JEFE'])) ?></dd></div>
    <?php endif; ?>
    <?php if (!empty($solicitud['OBSERVACION_RRHH'])): ?>
    <div class="detail-row"><dt>Obs. RRHH</dt><dd><?= nl2br(htmlspecialchars($solicitud['OBSERVACION_RRHH'])) ?></dd></div>
    <?php endif; ?>
  </dl>

  <?php if ($solicitud['ESTADO'] === 'PENDIENTE_JEFE' && in_array($user['rol'], [ROL_JEFE, ROL_ADMIN], true) && $solicitud['NIT_JEFE'] === $user['cedula']): ?>
  <div class="gestion-card">
    <h3>Gestión de Jefe</h3>
    <form method="post" action="<?= $baseUrl ?>/solicitud/<?= $solicitud['ID'] ?>/jefe">
      <?= Security::csrfField() ?>
      <div class="form-group"><label>Observación</label>
        <textarea name="observacion_jefe" rows="3" placeholder="Opcional..."></textarea>
      </div>
      <div class="form-actions">
        <button name="accion" value="aprobar" class="btn btn-green">Aprobar</button>
        <button name="accion" value="rechazar" class="btn btn-red">Rechazar</button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <?php if ($solicitud['ESTADO'] === 'APROBADO_JEFE' && in_array($user['rol'], [ROL_RRHH, ROL_ADMIN], true)): ?>
  <div class="gestion-card">
    <h3>Gestión de Talento Humano</h3>
    <form method="post" action="<?= $baseUrl ?>/solicitud/<?= $solicitud['ID'] ?>/rrhh">
      <?= Security::csrfField() ?>
      <div class="form-group"><label>Observación</label>
        <textarea name="observacion_rrhh" rows="3" placeholder="Opcional..."></textarea>
      </div>
      <div class="form-actions">
        <button name="accion" value="aprobar" class="btn btn-green">Aprobar</button>
        <button name="accion" value="rechazar" class="btn btn-red">Rechazar</button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
