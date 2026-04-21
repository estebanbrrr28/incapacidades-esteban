# Sistema de Solicitudes UGC - Esteban-Mateo

Sistema de gestión de solicitudes de permisos e incapacidades para la Universidad La Gran Colombia.

## Requisitos

- PHP >= 8.1
- Extensión OCI8 para Oracle (opcional en desarrollo)
- Extensión LDAP (opcional)

## Arquitectura

Este proyecto sigue una arquitectura MVC moderna con PSR-4 autoloading:

```
app/
  Controllers/   # Controladores (namespace App\Controllers)
  Models/        # Modelos (namespace App\Models)
core/
  Config.php     # Configuración via .env
  Controller.php # Controller base abstracto
  Flash.php      # Sistema de mensajes flash
  Model.php      # Model base abstracto
  Router.php     # Enrutador simple
  Security.php   # CSRF, sanitización, headers
  Session.php    # Gestión de sesiones seguras
config/
  config.php     # Constantes de negocio
  Ldap.php       # Autenticación LDAP
  Oracle.php     # Conexión Oracle
views/
  layouts/       # Layouts principales
  shared/        # Componentes reutilizables
  [roles]/      # Vistas por rol
```

## Instalación

1. Copiar la carpeta a `c:\xampp\htdocs\ugc_incapacidades`
2. Crear la tabla en Oracle ejecutando `INSTALAR_BD.sql`
3. Configurar credenciales en archivo `.env` (copiar de `.env.example`)
4. Opcional: Ejecutar `composer install` para autoloader optimizado

## Configuración .env

```
APP_ENV=development
BASE_URL=/ugc_incapacidades

ORACLE_HOST=172.28.5.101
ORACLE_PORT=1521
ORACLE_USER=iceberg
ORACLE_PASS=iceberg0
ORACLE_SERVICE=UGC

LDAP_HOST=10.238.30.115
LDAP_PORT=389
LDAP_USER=cn=ConsultaSi,ou=users,dc=ugc.edu,dc=co
LDAP_PASSWORD=****
LDAP_TREE=ou=users,dc=ugc.edu,dc=co
```

## Mejoras en v2.0

### Backend
- ✅ PSR-4 Autoloading con namespaces (App\, Core\, Config\)
- ✅ PSR-1 y PSR-12 coding standards
- ✅ Principios SOLID aplicados
- ✅ Configuración via .env (credenciales separadas del código)
- ✅ CSRF tokens en todos los formularios POST
- ✅ Sanitización de inputs
- ✅ Headers de seguridad (XSS, CSRF, Clickjacking)
- ✅ Manejo de errores mejorado
- ✅ Sesiones seguras (httponly, samesite)
- ✅ Flash messages con namespace Core\Flash

### Frontend
- ✅ Dark mode automático (prefers-color-scheme)
- ✅ Menú hamburguesa para móvil
- ✅ CSS semántico sin inline styles
- ✅ Mejor responsive design
- ✅ Variables CSS mejor organizadas
- ✅ Componentes reutilizables

### Seguridad
- ✅ CSRF protection en todos los formularios POST
- ✅ Password toggle con icono SVG (no emoji)
- ✅ Sanitización de output (htmlspecialchars)
- ✅ Sanitización de input
- ✅ Headers de seguridad HTTP
- ✅ Cookies seguras

## Usuarios de Prueba (modo desarrollo)

- `11111111` / prueba123 - Empleado
- `22222222` / prueba123 - Jefe
- `33333333` / prueba123 - Talento Humano
- `44444444` / prueba123 - Administrador
- `55555555` / prueba123 - Aprendiz

## Licencia

Proyecto interno UGC - Universidad La Gran Colombia
