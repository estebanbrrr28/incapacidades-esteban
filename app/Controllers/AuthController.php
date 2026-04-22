<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Config;
use Core\Flash;
use Core\Session;
use Core\Security;
use Config\Ldap;
use App\Models\EmpleadoModel;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $error       = $_SESSION['login_error'] ?? null;
        $devUsuarios = Config::isDev() ? USUARIOS_PRUEBA : [];
        unset($_SESSION['login_error']);

        $this->render('auth/login', compact('error', 'devUsuarios'), 'auth');
    }

    public function loginPost(): void
    {
        $cedula   = Security::sanitizeString($_POST['cedula'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$cedula || !$password) {
            $_SESSION['login_error'] = 'Ingresa tu cédula y contraseña.';
            $this->redirect('/login');
        }

        // Modo desarrollo: usuarios de prueba
        if (Config::isDev() && isset(USUARIOS_PRUEBA[$cedula]) && $password === 'prueba123') {
            Session::setUser(USUARIOS_PRUEBA[$cedula]);
            $this->redirect('/dashboard');
            return;
        }

        // Autenticación LDAP
        $ldap = Ldap::authenticate($cedula, $password);
        if (!$ldap) {
            $_SESSION['login_error'] = 'Credenciales incorrectas.';
            $this->redirect('/login');
            return;
        }

        // Obtener datos del empleado desde Oracle
        $empleadoModel = new EmpleadoModel();
        $empleado = $empleadoModel->getByNit($cedula);
        $rol = $empleadoModel->getRol($cedula);

        // Obtener jefe inmediato (no aplica para admin/RRHH)
        $jefe = (!in_array($rol, [ROL_ADMIN, ROL_RRHH], true))
            ? $empleadoModel->getJefeInmediato($cedula)
            : null;

        // Si LDAP no devolvió nombre (cayó al fallback de la cédula), usar el de Oracle
        $nombreFinal = $ldap['nombre'];
        if ($nombreFinal === $cedula && !empty($empleado['NOMBRE_COMPLETO'])) {
            $nombreFinal = $empleado['NOMBRE_COMPLETO'];
        }

        Session::setUser([
            'cedula'        => $cedula,
            'nombre'        => $nombreFinal,
            'email'         => $ldap['email'],
            'nivel'         => (int) ($empleado['NIVEL'] ?? 0),
            'centro_costo'  => $empleado['CENTRO_COSTO'] ?? '',
            'rol'           => $rol,
            'nit_jefe'      => $jefe['NIT_JEFE'] ?? null,
            'nombre_jefe'   => $jefe['NOMBRE_JEFE'] ?? null,
        ]);

        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('/login');
    }
}
