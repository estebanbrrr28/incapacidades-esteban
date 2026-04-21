<?php
use Core\Config;
use Core\Security;

$baseUrl = Config::baseUrl();
$cssUrl  = $baseUrl . '/public/css/ugc.css';
$logoUrl = $baseUrl . '/public/img/escudo-41a28286.png';
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="description" content="Acceso institucional al portal de solicitudes de la Universidad La Gran Colombia."/>
  <meta name="theme-color" content="#2f6c42"/>
  <title><?= htmlspecialchars(Config::appName()) ?> – Acceso</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $cssUrl ?>">
</head>
<body class="auth-body">
<div class="app-orbs" aria-hidden="true">
  <span class="app-orb app-orb--one"></span>
  <span class="app-orb app-orb--two"></span>
  <span class="app-orb app-orb--three"></span>
</div>
<header class="ugc-header ugc-header--auth">
  <div class="header-shell">
    <a href="<?= $baseUrl ?>/login" class="brand-link" aria-label="Volver al acceso">
      <img src="<?= $logoUrl ?>" alt="Logo Universidad La Gran Colombia" class="header-logo"/>
      <div class="brand-stack">
        <span class="brand-kicker">Universidad La Gran Colombia</span>
        <strong>Portal de Solicitudes</strong>
        <small>Acceso institucional</small>
      </div>
    </a>
    <div class="auth-top-pill">Seguridad reforzada · acceso institucional</div>
  </div>
</header>
<?= $content ?>
</body>
</html>
