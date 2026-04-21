<?php
use Core\Config;
use Core\Security;

$baseUrl = Config::baseUrl();
$logoUrl = $baseUrl . '/public/img/escudo-41a28286.png';
?>
<div class="login-container">
  <div class="login-bg-pattern"></div>
  <div class="login-card login-card--split animate-fade-up">
    <section class="login-hero-panel">
      <div class="login-brand">
        <div class="brand-icon">
          <img src="<?= $logoUrl ?>" alt="Logo Universidad La Gran Colombia" class="login-logo"/>
        </div>
        <h1 class="brand-title">Portal de Solicitudes</h1>
        <p class="brand-subtitle">Una experiencia más clara, segura y profesional para gestionar permisos e incapacidades.</p>
      </div>

      <div class="login-badges">
        <span class="login-badge">LDAP institucional</span>
        <span class="login-badge">CSRF activo</span>
        <span class="login-badge">Sesión segura</span>
      </div>

      <div class="login-feature-list">
        <div class="login-feature">
          <strong>Seguimiento por rol</strong>
          <span>Empleado, jefe, RRHH y administrador con trazabilidad centralizada.</span>
        </div>
        <div class="login-feature">
          <strong>Adjuntos controlados</strong>
          <span>PDF validado, carga limitada y visualización directa desde el portal.</span>
        </div>
        <div class="login-feature">
          <strong>Cabeceras reforzadas</strong>
          <span>Protecciones activas de sesión, navegación y contenido.</span>
        </div>
      </div>
    </section>

    <section class="login-form-panel">
      <?php if (!empty($error)): ?>
        <div class="alert alert-error animate-shake">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= $baseUrl ?>/login" id="loginForm" class="login-form" novalidate>
        <?= Security::csrfField() ?>
        <div class="input-group">
          <label class="input-label" for="cedula">Número de Documento</label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <input type="text" id="cedula" name="cedula" class="input-field" placeholder="Ej: 11111111" required autocomplete="username"/>
          </div>
        </div>

        <div class="input-group">
          <label class="input-label" for="password">Contraseña</label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input type="password" id="password" name="password" class="input-field" placeholder="Tu contraseña" required autocomplete="current-password"/>
            <button type="button" class="input-toggle" data-password-toggle aria-label="Mostrar contraseña">
              <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-login" id="btnLogin">
          <span class="btn-text">Ingresar al Sistema</span>
          <span class="btn-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </span>
        </button>
      </form>

      <?php if (!empty($devUsuarios)): ?>
      <div class="dev-section">
        <p class="dev-title">Usuarios de prueba <span class="dev-hint">pass: prueba123</span></p>
        <div class="dev-chips">
          <?php foreach ($devUsuarios as $ced => $u): ?>
          <button type="button" class="dev-chip" data-dev-cedula="<?= htmlspecialchars($ced) ?>">
            <span class="chip-role <?= $u['rol'] ?>"><?= strtoupper(substr($u['rol'], 0, 3)) ?></span>
            <span class="chip-name"><?= htmlspecialchars(explode(' ', $u['nombre'])[0]) ?></span>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <p class="login-footnote">Acceso protegido por validación institucional, sesión reforzada y políticas activas de seguridad en cada solicitud.</p>
    </section>
  </div>
</div>

<script <?= Security::scriptNonceAttr() ?>>
(function () {
  const loginForm = document.getElementById('loginForm');
  const togglePasswordBtn = document.querySelector('[data-password-toggle]');
  const passwordInput = document.getElementById('password');
  const cedulaInput = document.getElementById('cedula');

  if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener('click', function () {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      togglePasswordBtn.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
  }

  document.querySelectorAll('[data-dev-cedula]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (cedulaInput && passwordInput) {
        cedulaInput.value = this.getAttribute('data-dev-cedula') || '';
        passwordInput.value = 'prueba123';
        cedulaInput.focus();
      }
    });
  });

  if (loginForm) {
    loginForm.addEventListener('submit', function() {
      const btn = document.getElementById('btnLogin');
      btn.classList.add('loading');
      btn.querySelector('.btn-text').textContent = 'Ingresando...';
    });
  }
})();
</script>
