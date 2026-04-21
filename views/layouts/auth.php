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
  <title><?= htmlspecialchars(Config::appName()) ?> – Acceso</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $cssUrl ?>">
</head>
<body>
<header class="ugc-header">
  <img src="<?= $logoUrl ?>" alt="Logo Universidad La Gran Colombia" class="header-logo"/>
  <div class="brand">UNIVERSIDAD<small>La Gran Colombia</small></div>
</header>
<?= $content ?>
</body>
</html>
