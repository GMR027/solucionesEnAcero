<?php
/** @var array<string, \Model\ProductoVenta[]> $grupos */
$tituloCuenta = 'Nuevo pedido';
$subtituloCuenta = 'Elige productos y cantidades. Los precios son de lista; te confirmamos el pedido antes de surtirlo.';
$pestana = 'nuevo';
include __DIR__ . '/../templates/cuenta-header.php';
?>

<section class="seccion seccion--compacta">
  <div class="contenedor">
    <?php foreach ($errores as $error): ?>
      <p class="alerta alerta--error" role="alert"><?= s($error) ?></p>
    <?php endforeach; ?>

    <form class="pedido-form" method="post" action="/mi-cuenta/pedidos/nuevo" data-pedido data-iva="<?= s((string) $iva) ?>">
      <?= csrf_campo() ?>

      <div class="pedido-form__productos">
        <?php foreach ($grupos as $categoria => $productos): ?>
          <fieldset class="grupo-pedido">
            <legend class="grupo-pedido__titulo"><?= s($categoria) ?></legend>
            <?php foreach ($productos as $producto):
                $cantidad = (int) ($cantidades[$producto->id] ?? 0);
                $disponible = $producto->disponible();
                $agotado = $disponible < 1;
                if ($agotado) {
                    $cantidad = 0;
                }
                ?>
              <div class="linea-pedido <?= $cantidad > 0 ? 'is-seleccionada' : '' ?> <?= $agotado ? 'is-agotada' : '' ?>"
                   data-linea data-precio="<?= s((string) $producto->precio) ?>" data-disponible="<?= $disponible ?>" data-unidad="<?= s($producto->unidad) ?>">
                <div class="linea-pedido__imagen">
                  <?php if ($producto->imagen()): ?><img src="<?= s($producto->imagen()) ?>" alt="" loading="lazy" width="80" height="80"><?php endif; ?>
                </div>
                <div class="linea-pedido__info">
                  <span class="linea-pedido__sku"><?= s($producto->sku) ?></span>
                  <label class="linea-pedido__nombre" for="cant-<?= $producto->id ?>"><?= s($producto->nombre) ?></label>
                  <span class="linea-pedido__precio"><?= dinero($producto->precio) ?> / <?= s($producto->unidad) ?></span>
                  <?php if ($agotado): ?><span class="linea-pedido__agotado">No disponible</span><?php endif; ?>
                </div>
                <div class="cantidad">
                  <button type="button" class="cantidad__boton" data-menos aria-label="Quitar uno de <?= s($producto->nombre) ?>" <?= $agotado ? 'disabled' : '' ?>><?= icono('menos') ?></button>
                  <input class="cantidad__control" type="number" inputmode="numeric" min="0" max="<?= \Model\Pedido::MAX_CANTIDAD ?>" step="1"
                         id="cant-<?= $producto->id ?>" name="cantidad[<?= $producto->id ?>]" value="<?= $cantidad ?>" data-cantidad <?= $agotado ? 'disabled' : '' ?>>
                  <button type="button" class="cantidad__boton" data-mas aria-label="Agregar uno de <?= s($producto->nombre) ?>" <?= $agotado ? 'disabled' : '' ?>><?= icono('mas') ?></button>
                </div>
                <span class="linea-pedido__importe" data-importe><?= $cantidad ? dinero($cantidad * $producto->precio) : '' ?></span>
                <p class="linea-pedido__aviso" data-aviso role="alert" aria-live="polite"></p>
              </div>
            <?php endforeach; ?>
          </fieldset>
        <?php endforeach; ?>
      </div>

      <aside class="resumen tarjeta" id="resumen" aria-label="Resumen del pedido">
        <h2 class="tarjeta__titulo"><?= icono('carrito') ?> Tu pedido</h2>
        <dl class="resumen__importes">
          <div><dt>Productos</dt><dd data-total-piezas>0</dd></div>
          <div><dt>Subtotal</dt><dd data-subtotal>$0.00</dd></div>
          <div><dt>IVA (<?= (int) $iva ?>%)</dt><dd data-iva-monto>$0.00</dd></div>
          <div class="resumen__total"><dt>Total</dt><dd data-total>$0.00</dd></div>
        </dl>
        <div class="campo">
          <label class="campo__etiqueta" for="sucursal">Sucursal de entrega (opcional)</label>
          <input class="campo__control" type="text" id="sucursal" name="sucursal" maxlength="<?= \Model\Pedido::MAX_SUCURSAL ?>"
                 value="<?= s($sucursal) ?>" placeholder="¿Se entregará en alguna sucursal en específico? Ej. Sucursal Centro">
        </div>
        <div class="campo">
          <label class="campo__etiqueta" for="ubicacion">Ubicación de entrega</label>
          <select class="campo__control" id="ubicacion" name="ubicacion">
            <?php foreach (\Model\Pedido::UBICACIONES as $opcion): ?>
              <option value="<?= s($opcion) ?>" <?= $ubicacion === $opcion ? 'selected' : '' ?>><?= s($opcion) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="campo">
          <label class="campo__etiqueta" for="comentarios">Comentarios (opcional)</label>
          <textarea class="campo__control" id="comentarios" name="comentarios" rows="3" maxlength="<?= \Model\Pedido::MAX_COMENTARIOS ?>" placeholder="Horario, referencia de obra…"><?= s($comentarios) ?></textarea>
        </div>
        <button class="boton boton--primario boton--bloque" type="submit" data-enviar>Enviar pedido <?= icono('flecha') ?></button>
        <p class="resumen__nota">Importes estimados con precio de lista. El total final se confirma al cotizar.</p>
      </aside>
    </form>
  </div>
</section>

<a class="barra-total" href="#resumen" data-barra-total>
  <span>Cant. <span data-total-piezas>0</span> · <strong data-total>$0.00</strong></span>
  <span class="barra-total__accion">Revisar y enviar <?= icono('flecha') ?></span>
</a>
