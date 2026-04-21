<?php

declare(strict_types=1);

namespace Core;

final class Session
{
    private const ID_REGEN_INTERVAL = 900;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('UGC_SOL');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => Config::baseUrl() !== '' ? Config::baseUrl() : '/',
                'secure' => Config::get('APP_ENV') === 'production',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);

            session_start();
            self::refreshSessionId();
            $_SESSION['last_activity'] = time();
        }
    }

    public static function setUser(array $user): void
    {
        self::refreshSessionId(true);
        $_SESSION['usuario'] = $user;
        $_SESSION['csrf_token'] = null;
        $_SESSION['login_at'] = time();
        $_SESSION['last_activity'] = time();
    }

    public static function getUser(): array
    {
        return $_SESSION['usuario'] ?? [];
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['usuario']);
    }

    public static function getRole(): string
    {
        return $_SESSION['usuario']['rol'] ?? '';
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => (bool) ($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Strict',
            ]);
        }
        session_destroy();
    }

    private static function refreshSessionId(bool $force = false): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $lastRegeneration = (int) ($_SESSION['last_regeneration'] ?? 0);
        $mustRegenerate = $force || $lastRegeneration === 0 || (time() - $lastRegeneration) >= self::ID_REGEN_INTERVAL;

        if ($mustRegenerate) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}
