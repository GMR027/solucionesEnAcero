<?php /** @var \Model\Producto $producto */ ?>
<dialog class="modal" id="ficha-<?= s($producto->slug()) ?>" aria-labelledby="ficha-<?= s($producto->slug()) ?>-titulo">
  <div class="modal__contenido">
    <button class="modal__cerrar" type="button" data-cerrar-modal aria-label="Cerrar ficha técnica"><?= icono('cerrar') ?></button>

    <div class="modal__media">
      <?php if ($producto->imagenUso()): ?>
        <img src="<?= s($producto->imagenUso()) ?>" alt="<?= s($producto->nombre) ?> instalado" loading="lazy">
      <?php elseif ($producto->imagenPrincipal()): ?>
        <img src="<?= s($producto->imagenPrincipal()) ?>" alt="<?= s($producto->nombre) ?>" loading="lazy">
      <?php endif; ?>
    </div>

    <div class="modal__info">
      <p class="etiqueta"><?= s($producto->categoria) ?> · <?= s($producto->sku) ?></p>
      <h2 class="modal__titulo" id="ficha-<?= s($producto->slug()) ?>-titulo"><?= s($producto->nombre) ?></h2>
      <p class="modal__subtitulo"><?= s($producto->subtitulo()) ?></p>

      <dl class="especificaciones">
        <div><dt>Código</dt><dd><?= s($producto->sku) ?></dd></div>
        <?php foreach ($producto->especificaciones() as $clave => $valor): ?>
          <div><dt><?= s($clave) ?></dt><dd><?= s($valor) ?></dd></div>
        <?php endforeach; ?>
      </dl>

      <?php if ($producto->uso()): ?>
        <div class="uso">
          <span class="uso__titulo">Tipo de uso</span>
          <p><?= s($producto->uso()) ?></p>
        </div>
      <?php endif; ?>

      <div class="modal__botones">
        <?php if (cliente()): ?>
          <a class="boton boton--primario boton--bloque" href="/mi-cuenta/pedidos/nuevo?producto=<?= (int) $producto->id ?>">
            <?= icono('carrito') ?> Agregar a mi pedido
          </a>
        <?php endif; ?>
        <a class="boton <?= cliente() ? 'boton--contorno' : 'boton--primario' ?> boton--bloque" href="<?= s(whatsapp($producto->mensajeCotizacion())) ?>" target="_blank" rel="noopener">
          <?= icono('whatsapp') ?> Cotizar <?= s($producto->sku) ?> por WhatsApp
        </a>
      </div>
    </div>
  </div>
</dialog>
