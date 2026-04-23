<?php

declare(strict_types=1);

use Core\Config;

// Cargar .env
Config::load(__DIR__ . '/../.env');

// Zona horaria Colombia
date_default_timezone_set('America/Bogota');

// Error reporting según entorno
if (Config::isDev()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// =============================================
// CONSTANTES DE NEGOCIO (no sensibles)
// =============================================
define('APP_VERSION', '2.0.0');

define('ROL_ADMIN',    'administrador');
define('ROL_RRHH',     'talento_humano');
define('ROL_JEFE',     'jefe_inmediato');
define('ROL_EMPLEADO', 'solicitante');

define('CC_RRHH', ['2411001']);
define('CC_APRENDICES', ['2411004']);

define('NIVEL_MIN_JEFE', 4);
define('NIVEL_MIN_ADMIN', 7);

define('ESTADO_PENDIENTE_JEFE', 'PENDIENTE_JEFE');
define('ESTADO_APROBADO_JEFE',  'APROBADO_JEFE');
define('ESTADO_RECHAZADO_JEFE', 'RECHAZADO_JEFE');
define('ESTADO_APROBADO_RRHH',  'APROBADO_RRHH');
define('ESTADO_RECHAZADO_RRHH', 'RECHAZADO_RRHH');

define('TIPOS_SOLICITUD', [
    'LICENCIA_NO_REMUNERADA'              => 'Licencia No Remunerada',
    'LICENCIA_REMUNERADA'                 => 'Licencia Remunerada',
    'REUNIONES_ESCOLARES'                 => 'Reuniones Escolares',
    'CITA_MEDICA'                         => 'Cita Médica',
    'PERMISO_SINDICAL'                    => 'Permiso Sindical',
    'CALAMIDAD_DOMESTICA'                 => 'Calamidad Doméstica',
    'PERMISO_FUNEBRE'                     => 'Permiso Fúnebre',
    'CITACIONES_JUDICIALES_ADMIN_LEGALES' => 'Citaciones Judiciales / Adm. / Legales',
    'COMPENSATORIO'                       => 'Compensatorio',
    'OTROS'                               => 'Otros',
]);

define('USUARIOS_PRUEBA', [
    '11111111' => ['cedula' => '11111111', 'nombre' => 'Juan Empleado (Prueba)', 'email' => 'empleado@ugc.edu.co', 'rol' => ROL_EMPLEADO, 'nivel' => 30, 'centro_costo' => '2312101', 'nit_jefe' => '22222222', 'nombre_jefe' => 'María Jefe (Prueba)'],
    '22222222' => ['cedula' => '22222222', 'nombre' => 'María Jefe (Prueba)', 'email' => 'jefe@ugc.edu.co', 'rol' => ROL_JEFE, 'nivel' => 5, 'centro_costo' => '2312101', 'nit_jefe' => '44444444', 'nombre_jefe' => 'Ana Admin (Prueba)'],
    '33333333' => ['cedula' => '33333333', 'nombre' => 'Carlos Talento Humano (Prueba)', 'email' => 'rrhh@ugc.edu.co', 'rol' => ROL_RRHH, 'nivel' => 60, 'centro_costo' => '2413001', 'nit_jefe' => '44444444', 'nombre_jefe' => 'Ana Admin (Prueba)'],
    '44444444' => ['cedula' => '44444444', 'nombre' => 'Ana Administrador (Prueba)', 'email' => 'admin@ugc.edu.co', 'rol' => ROL_ADMIN, 'nivel' => 7, 'centro_costo' => '1020001', 'nit_jefe' => null, 'nombre_jefe' => null],
    '55555555'   => ['cedula' => '55555555',   'nombre' => 'Pedro Aprendiz (Prueba)',          'email' => 'aprendiz@ugc.edu.co',   'rol' => ROL_EMPLEADO, 'nivel' => 10, 'centro_costo' => '2411001', 'nit_jefe' => null,       'nombre_jefe' => null],
    '1012344984' => ['cedula' => '1012344984', 'nombre' => 'Esteban Solicitante (Prueba)',      'email' => 'esteban@ugc.edu.co',    'rol' => ROL_EMPLEADO, 'nivel' => 30, 'centro_costo' => '2312101', 'nit_jefe' => '44444444', 'nombre_jefe' => 'Ana Admin (Prueba)'],
    '1012344984-admin' => ['cedula' => '1012344984-admin', 'nombre' => 'Esteban Admin (Prueba)', 'email' => 'esteban@ugc.edu.co', 'rol' => ROL_ADMIN,    'nivel' => 7,  'centro_costo' => '1020001', 'nit_jefe' => null,       'nombre_jefe' => null],
]);
