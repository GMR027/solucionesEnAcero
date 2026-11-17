<?php
$nombreSitio = sitio('nombre');
$tituloPagina = isset($titulo) ? "{$titulo} | {$nombreSitio}" : $nombreSitio;
$pagina ??= '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= s($tituloPagina) ?></title>
  <meta name="description" content="<?= s($descripcion ?? sitio('lema')) ?>">
  <meta name="theme-color" content="#121417">
  <meta property="og:title" content="<?= s($tituloPagina) ?>">
  <meta property="og:description" content="<?= s($descripcion ?? sitio('lema')) ?>">
  <meta property="og:type" content="website">
  <link rel="icon" type="image/png" href="<?= img('logo/logo.png') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <script>document.documentElement.classList.add("js")</script>
</head>
<body class="pagina-<?= s($pagina) ?>">
  <a class="saltar-contenido" href="#contenido">Saltar al contenido</a>

  <?php include __DIR__ . '/templates/header.php'; ?>

  <main id="contenido">
    <?= $contenido ?>
  </main>

  <?php include __DIR__ . '/templates/footer.php'; ?>

  <a class="whatsapp-flotante" href="<?= s(whatsapp('Hola, quiero información sobre sus productos.')) ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp">
    <?= icono('whatsapp') ?>
  </a>

  <script src="<?= asset('js/app.js') ?>" defer></script>
  <?php foreach ($script ?? [] as $archivo): ?>
    <script src="<?= asset('js/' . $archivo) ?>" defer></script>
  <?php endforeach; ?>
</body>
</html>
