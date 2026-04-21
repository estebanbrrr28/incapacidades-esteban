<?php
use Core\Config;
use Core\Flash;
use Core\Session;
use Core\Security;
use Config\Oracle;

$user  = $user ?? Session::getUser();
$flash = Flash::get();
$rolesLabel = [ROL_ADMIN => 'Administrador', ROL_RRHH => 'Talento Humano', ROL_JEFE => 'Jefe Inmediato', ROL_EMPLEADO => 'Empleado'];
$rolLabel   = $rolesLabel[$user['rol'] ?? ''] ?? 'Usuario';
$baseUrl    = Config::baseUrl();
$cssUrl     = $baseUrl . '/public/css/ugc.css';
$logoUrl    = $baseUrl . '/public/img/escudo-41a28286.png';
$showPrimaryNav = ($user['rol'] ?? '') !== ROL_EMPLEADO;
$scriptNonce = Security::scriptNonceAttr();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$isRouteActive = static function (string $route) use ($baseUrl, $currentPath): bool {
  $fullRoute = $baseUrl . $route;
  return $currentPath === $fullRoute || ($route !== '/dashboard' && str_starts_with($currentPath, $fullRoute));
};
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="description" content="Portal institucional para gestionar permisos, incapacidades y trazabilidad de solicitudes."/>
  <meta name="theme-color" content="#2f6c42"/>
  <title><?= htmlspecialchars(Config::appName()) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $cssUrl ?>">
</head>
<body class="app-body">
<a class="skip-link" href="#main-content">Saltar al contenido principal</a>
<div class="app-orbs" aria-hidden="true">
  <span class="app-orb app-orb--one"></span>
  <span class="app-orb app-orb--two"></span>
  <span class="app-orb app-orb--three"></span>
</div>
<header class="ugc-header">
  <div class="header-shell">
    <div class="header-brand-group">
      <?php if ($showPrimaryNav): ?>
      <button class="menu-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" data-menu-toggle data-menu-target="primaryNav">
        <span></span><span></span><span></span>
      </button>
      <?php endif; ?>
      <a href="<?= $baseUrl ?>/dashboard" class="brand-link" aria-label="Ir al inicio del portal">
        <img src="<?= $logoUrl ?>" alt="Logo Universidad La Gran Colombia" class="header-logo"/>
        <div class="brand-stack">
          <span class="brand-kicker">Universidad La Gran Colombia</span>
          <strong>Portal de Solicitudes</strong>
          <small>Permisos, incapacidades y trazabilidad</small>
        </div>
      </a>
    </div>

    <?php if ($showPrimaryNav): ?>
    <nav id="primaryNav" class="header-nav" data-main-nav>
      <a href="<?= $baseUrl ?>/dashboard" class="nav-pill <?= $isRouteActive('/dashboard') && !$isRouteActive('/dashboard/analitica') && !$isRouteActive('/dashboard/roles') ? 'is-active' : '' ?>">Inicio</a>
      <?php if (in_array($user['rol'] ?? '', [ROL_ADMIN, ROL_RRHH, ROL_JEFE], true)): ?>
        <a href="<?= $baseUrl ?>/solicitudes" class="nav-pill <?= $isRouteActive('/solicitudes') ? 'is-active' : '' ?>">Todas las solicitudes</a>
      <?php endif; ?>
      <?php if (in_array($user['rol'] ?? '', [ROL_EMPLEADO, ROL_JEFE], true)): ?>
        <a href="<?= $baseUrl ?>/solicitud/crear" class="nav-pill nav-pill--accent <?= $isRouteActive('/solicitud/crear') ? 'is-active' : '' ?>">Nueva solicitud</a>
      <?php endif; ?>
      <?php if (($user['rol'] ?? '') === ROL_ADMIN): ?>
        <a href="<?= $baseUrl ?>/dashboard/analitica" class="nav-pill <?= $isRouteActive('/dashboard/analitica') ? 'is-active' : '' ?>">Analítica</a>
      <?php endif; ?>
    </nav>
    <?php endif; ?>

    <div class="header-tools">
      <div class="notificacion-wrap">
        <button class="notificacion-bell" id="notifBell" type="button" aria-label="Notificaciones" aria-expanded="false">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
          </svg>
          <span class="notificacion-badge" id="notifBadge" data-count="0">0</span>
        </button>
        <div class="notificacion-dropdown" id="notifDropdown">
          <div class="notificacion-header">
            <h4>Notificaciones</h4>
            <button class="mark-all" id="markAllRead" type="button">Marcar todo leído</button>
          </div>
          <div class="notificacion-list" id="notifList">
            <div class="notificacion-empty">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
              </svg>
              <p>No tienes notificaciones nuevas</p>
            </div>
          </div>
        </div>
      </div>

      <div class="user-chip">
        <span class="user-role-label">Sesión activa</span>
        <strong><?= htmlspecialchars($user['nombre'] ?? '') ?></strong>
        <span class="user-role"><?= $rolLabel ?></span>
      </div>

      <form action="<?= $baseUrl ?>/logout" method="post" class="header-logout-form">
        <?= Security::csrfField() ?>
        <button class="logout" type="submit">Salir</button>
      </form>
    </div>
  </div>
</header>

<?php if (Config::isDev() && !Oracle::getInstance()->estaDisponible()): ?>
<div class="dev-banner">
  ⚠️ <strong>Modo sin BD:</strong> Oracle no disponible — la extensión OCI8 no está instalada o no hay conexión. Las operaciones usarán datos vacíos.
</div>
<?php endif; ?>

<main class="ugc-wrap" id="main-content" tabindex="-1">
  <div class="page-shell">
  <?php if ($flash): ?>
    <div class="flash flash-<?= $flash['type'] === 'success' ? 'ok' : 'err' ?> animate-fade-down" role="status" aria-live="polite">
      <?= $flash['type'] === 'success' ? 'Exito:' : 'Error:' ?> <?= htmlspecialchars($flash['message']) ?>
    </div>
  <?php endif; ?>
  <?= $content ?>
  </div>
</main>

<footer class="ugc-footer">
  <div class="footer-content">
    <div class="footer-brand">
      <img src="<?= $logoUrl ?>" alt="Logo Universidad La Gran Colombia" class="footer-logo-img"/>
      <div class="footer-brand-copy">
        <strong class="footer-title">Sistema institucional de solicitudes</strong>
        <span class="footer-name">Universidad La Gran Colombia</span>
        <p class="footer-description">Diseñado para trazabilidad, control documental y aprobación segura por roles.</p>
      </div>
    </div>
    <div class="footer-links">
      <a href="<?= $baseUrl ?>/dashboard">Inicio</a>
      <?php if (in_array($user['rol'] ?? '', [ROL_ADMIN, ROL_RRHH, ROL_JEFE], true)): ?>
        <a href="<?= $baseUrl ?>/solicitudes">Solicitudes</a>
      <?php endif; ?>
      <?php if (in_array($user['rol'] ?? '', [ROL_EMPLEADO, ROL_JEFE], true)): ?>
        <a href="<?= $baseUrl ?>/solicitud/crear">Nueva solicitud</a>
      <?php endif; ?>
    </div>
    <div class="footer-status">
      <span class="footer-pill">CSRF activo</span>
      <span class="footer-pill">Sesión regenerada</span>
      <span class="footer-pill">Cabeceras reforzadas</span>
    </div>
    <form action="<?= $baseUrl ?>/logout" method="post" class="footer-logout-form">
      <?= Security::csrfField() ?>
      <button type="submit" class="btn btn-outline">Cerrar sesión</button>
    </form>
    <div class="footer-copy">
      &copy; <?= date('Y') ?> Sistema de Solicitudes. Todos los derechos reservados.
    </div>
  </div>
</footer>

<script <?= $scriptNonce ?>>
document.addEventListener('DOMContentLoaded',()=>{
  const menuToggle = document.querySelector('[data-menu-toggle]');
  const mainNav = document.querySelector('[data-main-nav]');

  if (menuToggle && mainNav) {
    menuToggle.addEventListener('click', () => {
      const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
      menuToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      mainNav.classList.toggle('nav-open', !expanded);
    });
  }

  document.querySelectorAll('.ugc-header nav a').forEach(a=>{
    a.addEventListener('click',()=>{
      if (mainNav) {
        mainNav.classList.remove('nav-open');
      }
      if (menuToggle) {
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    });
  });

  // ============================================
  // SISTEMA DE NOTIFICACIONES
  // ============================================
  const notifBell = document.getElementById('notifBell');
  const notifDropdown = document.getElementById('notifDropdown');
  const notifBadge = document.getElementById('notifBadge');
  const notifList = document.getElementById('notifList');
  const markAllBtn = document.getElementById('markAllRead');
  const baseUrl = '<?= $baseUrl ?>';

  let notificaciones = [];
  let dropdownOpen = false;

  // Toggle dropdown
  if (notifBell) {
    notifBell.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdownOpen = !dropdownOpen;
      notifBell.setAttribute('aria-expanded', dropdownOpen ? 'true' : 'false');
      notifDropdown.classList.toggle('active', dropdownOpen);
      if (dropdownOpen) {
        cargarNotificaciones();
      }
    });
  }

  // Cerrar dropdown al hacer clic fuera
  document.addEventListener('click', (e) => {
    if (dropdownOpen && !notifDropdown.contains(e.target) && !notifBell.contains(e.target)) {
      dropdownOpen = false;
      notifBell.setAttribute('aria-expanded', 'false');
      notifDropdown.classList.remove('active');
    }

    const toastClose = e.target.closest('[data-toast-close]');
    if (toastClose) {
      toastClose.parentElement?.remove();
      return;
    }

    const notificationLink = e.target.closest('[data-notification-id]');
    if (notificationLink) {
      e.preventDefault();
      const targetUrl = notificationLink.getAttribute('href');
      const notificationId = notificationLink.getAttribute('data-notification-id');
      Promise.resolve(marcarLeida(notificationId)).finally(() => {
        if (targetUrl) {
          window.location.href = targetUrl;
        }
      });
      return;
    }
  });

  document.addEventListener('submit', (e) => {
    const form = e.target.closest('form[data-confirm]');
    if (form && !window.confirm(form.getAttribute('data-confirm') || '¿Confirmar esta acción?')) {
      e.preventDefault();
    }
  });

  // Cargar contador inicial
  actualizarContador();
  // Refrescar cada 60 segundos
  setInterval(actualizarContador, 60000);

  // Marcar todas como leídas
  if (markAllBtn) {
    markAllBtn.addEventListener('click', async () => {
      const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value;
      if (!csrfToken) {
        mostrarToast('Error: No se encontró el token de seguridad. Recarga la página.', 'error');
        return;
      }

      // Deshabilitar botón durante la petición
      markAllBtn.disabled = true;
      markAllBtn.textContent = 'Procesando...';

      try {
        const response = await fetch(`${baseUrl}/api/notificaciones/leer-todas`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        const data = await response.json();
        if (data.success) {
          actualizarContador();
          cargarNotificaciones();
          mostrarToast('Todas las notificaciones marcadas como leídas', 'success');
        } else {
          mostrarToast(data.error || 'Error al marcar notificaciones', 'error');
        }
      } catch (err) {
        console.error('Error al marcar notificaciones:', err);
        mostrarToast('Error de conexión. Inténtalo de nuevo.', 'error');
      } finally {
        markAllBtn.disabled = false;
        markAllBtn.textContent = 'Marcar todo leído';
      }
    });
  }

  // Función para actualizar el contador
  async function actualizarContador() {
    try {
      const response = await fetch(`${baseUrl}/api/notificaciones/contador`);
      const data = await response.json();
      const count = data.contador || 0;

      if (notifBadge) {
        notifBadge.textContent = count > 99 ? '99+' : count;
        notifBadge.setAttribute('data-count', count);

        if (count > 0) {
          notifBell.classList.add('has-new');
          setTimeout(() => notifBell.classList.remove('has-new'), 1000);
        }
      }
    } catch (err) {
      console.error('Error al cargar contador:', err);
    }
  }

  // Función para cargar notificaciones
  async function cargarNotificaciones() {
    try {
      const response = await fetch(`${baseUrl}/api/notificaciones`);
      const data = await response.json();
      notificaciones = data.notificaciones || [];
      renderizarNotificaciones();
    } catch (err) {
      console.error('Error al cargar notificaciones:', err);
    }
  }

  // Renderizar notificaciones
  function renderizarNotificaciones() {
    if (!notifList) return;

    if (notificaciones.length === 0) {
      notifList.innerHTML = `
        <div class="notificacion-empty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
          </svg>
          <p>No tienes notificaciones nuevas</p>
        </div>
      `;
      return;
    }

    notifList.innerHTML = notificaciones.map(n => {
      const icono = getIconoNotificacion(n.TIPO);
      const claseIcono = getClaseIcono(n.TIPO);
      const fecha = formatearFecha(n.FECHA_CREACION);

      return `
        <a href="${baseUrl}/solicitud/${n.ID_SOLICITUD}/ver"
           class="notificacion-item unread"
           data-id="${n.ID}"
           data-notification-id="${n.ID}">
          <div class="notificacion-icon ${claseIcono}">${icono}</div>
          <div class="notificacion-content">
            <p>${escapeHtml(n.MENSAJE)}</p>
            <span class="notificacion-time">${fecha}</span>
          </div>
        </a>
      `;
    }).join('');
  }

  // Marcar una notificación como leída
  async function marcarLeida(id) {
    const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value;
    if (!csrfToken) {
      mostrarToast('Error: Token de seguridad no encontrado', 'error');
      return false;
    }

    try {
      const response = await fetch(`${baseUrl}/api/notificaciones/${id}/leer`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken,
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      const data = await response.json();
      if (data.success) {
        actualizarContador();
        return true;
      }
    } catch (err) {
      console.error('Error al marcar como leída:', err);
    }

    return false;
  }

  // Helpers
  function getIconoNotificacion(tipo) {
    const iconos = {
      'NUEVA_SOLICITUD': '🔔',
      'SOLICITUD_EDITADA': '✏️',
      'SOLICITUD_APROBADA_JEFE': '✅',
      'SOLICITUD_RECHAZADA_JEFE': '❌',
      'REVISION_RRHH': '👁️',
      'SOLICITUD_APROBADA_RRHH': '🎉',
      'SOLICITUD_RECHAZADA_RRHH': '🚫'
    };
    return iconos[tipo] || '📋';
  }

  function getClaseIcono(tipo) {
    if (tipo.includes('RECHAZADA')) return 'rechazada';
    if (tipo.includes('APROBADA')) return 'aprobada';
    if (tipo.includes('REVISION')) return 'revision';
    return 'nueva';
  }

  function formatearFecha(fecha) {
    if (!fecha) return '';
    const date = new Date(fecha);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Ahora mismo';
    if (diffMins < 60) return `Hace ${diffMins} min`;
    if (diffHours < 24) return `Hace ${diffHours} h`;
    if (diffDays === 1) return 'Ayer';
    if (diffDays < 7) return `Hace ${diffDays} días`;

    return date.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
  }

  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Mostrar mensaje toast al usuario
  function mostrarToast(mensaje, tipo = 'info') {
    // Crear elemento toast
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${tipo}`;
    toast.innerHTML = `
      <span class="toast-message">${escapeHtml(mensaje)}</span>
      <button class="toast-close" type="button" data-toast-close>×</button>
    `;

    // Agregar al body
    document.body.appendChild(toast);

    // Animar entrada
    requestAnimationFrame(() => {
      toast.style.opacity = '1';
      toast.style.transform = 'translateX(0)';
    });

    // Auto-cerrar después de 3 segundos
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }
});
</script>
</body>
</html>
