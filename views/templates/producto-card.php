<?php /** @var \Model\Producto $producto */ ?>
<article class="producto" data-animar style="--retraso: <?= (int) ($retraso ?? 0) ?>ms">
  <div class="producto__imagen">
    <span class="producto__sku"><?= s($producto->sku) ?></span>
    <?php if ($producto->imagenPrincipal()): ?>
      <img src="<?= img($producto->imagenPrincipal()) ?>" alt="<?= s($producto->nombre) ?>" loading="lazy" width="800" height="800">
    <?php endif; ?>
  </div>
  <div class="producto__cuerpo">
    <h2 class="producto__titulo"><?= s($producto->nombre) ?></h2>
    <p class="producto__subtitulo"><?= s($producto->subtitulo()) ?></p>
    <?php if ($producto->dimension()): ?>
      <p class="producto__dato"><?= icono('regla') ?> <?= s($producto->dimension()) ?></p>
    <?php endif; ?>
    <button class="boton boton--contorno boton--bloque" type="button" data-abrir-modal="ficha-<?= s($producto->slug()) ?>">
      <?= icono('ficha') ?> Ficha técnica
    </button>
  </div>
</article>
