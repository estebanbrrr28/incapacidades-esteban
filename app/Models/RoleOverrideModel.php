<?php

declare(strict_types=1);

namespace App\Models;

final class RoleOverrideModel
{
    private const ALLOWED_ROLES = [
        ROL_ADMIN,
        ROL_RRHH,
        ROL_JEFE,
        ROL_EMPLEADO,
    ];

    private string $filePath;

    public function __construct()
    {
        $this->filePath = dirname(__DIR__, 2) . '/config/role_overrides.json';
    }

    public function getRole(string $nit): ?string
    {
        $roles = $this->readAll();
        $role = $roles[$nit] ?? null;

        return self::isValidRole($role) ? $role : null;
    }

    public function setRole(string $nit, ?string $role): bool
    {
        $roles = $this->readAll();

        if ($role === null || $role === '') {
            unset($roles[$nit]);
            return $this->writeAll($roles);
        }

        if (!self::isValidRole($role)) {
            return false;
        }

        $roles[$nit] = $role;
        return $this->writeAll($roles);
    }

    public static function isValidRole(?string $role): bool
    {
        return is_string($role) && in_array($role, self::ALLOWED_ROLES, true);
    }

    private function readAll(): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $raw = file_get_contents($this->filePath);
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $roles = [];
        foreach ($decoded as $nit => $role) {
            $nit = preg_replace('/\D+/', '', (string) $nit);
            if ($nit === '' || !self::isValidRole($role)) {
                continue;
            }
            $roles[$nit] = $role;
        }

        return $roles;
    }

    private function writeAll(array $roles): bool
    {
        ksort($roles);

        $json = json_encode($roles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }

        return file_put_contents($this->filePath, $json . PHP_EOL, LOCK_EX) !== false;
    }
}