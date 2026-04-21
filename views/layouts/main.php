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
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= htmlspecialchars(Config::appName()) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $cssUrl ?>">
</head>
<body>
<header class="ugc-header">
  <?php if ($showPrimaryNav): ?>
  <button class="menu-toggle" aria-label="Menú" onclick="document.querySelector('.ugc-header nav').classList.toggle('nav-open')">
    <span></span><span></span><span></span>
  </button>
  <?php endif; ?>
  <img src="<?= $logoUrl ?>" alt="Logo Universidad La Gran Colombia" class="header-logo"/>
  <div class="brand">UNIVERSIDAD<small>La Gran Colombia</small></div>
  <?php if ($showPrimaryNav): ?>
  <nav>
    <a href="<?= $baseUrl ?>/dashboard">Inicio</a>
    <?php if (in_array($user['rol'] ?? '', [ROL_ADMIN, ROL_RRHH, ROL_JEFE], true)): ?>
      <a href="<?= $baseUrl ?>/solicitudes">Todas las solicitudes</a>
    <?php endif; ?>
    <?php if (in_array($user['rol'] ?? '', [ROL_EMPLEADO, ROL_JEFE], true)): ?>
      <a href="<?= $baseUrl ?>/solicitud/crear">+ Nueva solicitud</a>
    <?php endif; ?>
  </nav>
  <?php endif; ?>
  <!-- Notificaciones -->
  <div class="notificacion-wrap">
    <button class="notificacion-bell" id="notifBell" aria-label="Notificaciones">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
      </svg>
      <span class="notificacion-badge" id="notifBadge" data-count="0">0</span>
    </button>
    <div class="notificacion-dropdown" id="notifDropdown">
      <div class="notificacion-header">
        <h4>Notificaciones</h4>
        <button class="mark-all" id="markAllRead">Marcar todo leído</button>
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
    <?= htmlspecialchars($user['nombre'] ?? '') ?>
    <span class="user-role"><?= $rolLabel ?></span>
  </div>
  <form action="<?= $baseUrl ?>/logout" method="post">
    <?= Security::csrfField() ?>
    <button class="logout" type="submit">Salir</button>
  </form>
</header>

<?php if (Config::isDev() && !Oracle::getInstance()->estaDisponible()): ?>
<div class="dev-banner">
  ⚠️ <strong>Modo sin BD:</strong> Oracle no disponible — la extensión OCI8 no está instalada o no hay conexión. Las operaciones usarán datos vacíos.
</div>
<?php endif; ?>

<main class="ugc-wrap">
  <?php if ($flash): ?>
    <div class="flash flash-<?= $flash['type'] === 'success' ? 'ok' : 'err' ?> animate-fade-down">
      <?= $flash['type'] === 'success' ? 'Exito:' : 'Error:' ?> <?= htmlspecialchars($flash['message']) ?>
    </div>
  <?php endif; ?>
  <?= $content ?>
</main>

<footer class="ugc-footer">
  <div class="footer-content">
    <div class="footer-brand">
      <img src="<?= $logoUrl ?>" alt="Logo Universidad La Gran Colombia" class="footer-logo-img"/>
      <span class="footer-name">Universidad La Gran Colombia</span>
    </div>
    <div class="footer-links">
      <a href="<?= $baseUrl ?>/dashboard">Inicio</a>
      <a href="<?= $baseUrl ?>/dashboard">Mis Solicitudes</a>
      <a href="<?= $baseUrl ?>/logout">Cerrar Sesion</a>
    </div>
    <div class="footer-copy">
      &copy; <?= date('Y') ?> Sistema de Solicitudes. Todos los derechos reservados.
    </div>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('.ugc-table tbody tr').forEach((r,i)=>{
    r.style.cssText='opacity:0;transform:translateY(10px);transition:opacity .3s ease,transform .3s ease';
    setTimeout(()=>{r.style.opacity='1';r.style.transform='none';},50+i*40);
  });
  document.querySelectorAll('.stat-card').forEach((c,i)=>{
    c.style.cssText='opacity:0;transform:translateY(14px);transition:opacity .35s ease,transform .35s ease';
    setTimeout(()=>{c.style.opacity='1';c.style.transform='none';},80+i*70);
  });
  // Cerrar menú móvil al hacer clic en un enlace
  document.querySelectorAll('.ugc-header nav a').forEach(a=>{
    a.addEventListener('click',()=>document.querySelector('.ugc-header nav').classList.remove('nav-open'));
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
      notifDropdown.classList.remove('active');
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
           onclick="marcarLeida(${n.ID}, event)">
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
  window.marcarLeida = async function(id, event) {
    const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value;
    if (!csrfToken) {
      mostrarToast('Error: Token de seguridad no encontrado', 'error');
      return;
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
      }
    } catch (err) {
      console.error('Error al marcar como leída:', err);
    }
  };

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
      <button class="toast-close" onclick="this.parentElement.remove()">×</button>
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
