<?php
/**
 * Encabezado de paginas interiores.
 * Variables: $encabezado = ['etiqueta', 'titulo', 'texto', 'migas' => [[texto, url|null], ...]]
 */
?>
<section class="pagina-header">
  <div class="contenedor">
    <nav class="migas" aria-label="Ruta de navegación">
      <ol>
        <li><a href="/">Inicio</a></li>
        <?php foreach ($encabezado['migas'] ?? [] as [$texto, $url]): ?>
          <li><?php if ($url): ?><a href="<?= s($url) ?>"><?= s($texto) ?></a><?php else: ?><span aria-current="page"><?= s($texto) ?></span><?php endif; ?></li>
        <?php endforeach; ?>
      </ol>
    </nav>
    <p class="etiqueta"><?= s($encabezado['etiqueta'] ?? '') ?></p>
    <h1 class="pagina-header__titulo"><?= s($encabezado['titulo']) ?></h1>
    <?php if (!empty($encabezado['texto'])): ?>
      <p class="pagina-header__texto"><?= s($encabezado['texto']) ?></p>
    <?php endif; ?>
  </div>
</section>
