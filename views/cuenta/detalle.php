<?php
/** @var \Model\Pedido $pedido */
/** @var array $detalles */
/** @var ?array $venta */
/** @var array $fotos */
$entregado = $venta && str_starts_with((string) ($venta['entrega_estado'] ?? ''), 'entregado');
$orden = ['pendiente' => 1, 'cotizado' => 2, 'vendido' => 3];
$nivel = $entregado ? 4 : ($orden[$pedido->estado] ?? 0);
$pasos = [
    1 => ['Recibido', 'Tu pedido llegó y está por revisarse.'],
    2 => ['Cotizado', 'Preparamos tu cotización.'],
    3 => ['Vendido', 'Pedido confirmado y en preparación.'],
    4 => ['Entregado', 'Tu pedido fue entregado.'],
];
$entregas = [
    'pendiente' => 'Entrega pendiente',
    'entregado' => 'Entregado',
    'entregado_con_incidencias' => 'Entregado con incidencias',
    'entregado_incidencias_resueltas' => 'Entregado (incidencias resueltas)',
];
$entrega = \Model\Pedido::desglosarEntrega($pedido->comentarios);

$tituloCuenta = "Pedido {$pedido->folio}";
$subtituloCuenta = 'Realizado el ' . s(fecha($pedido->fecha)) . ($pedido->origen === 'admin' ? ' · capturado por nuestro equipo' : '');
$pestana = null;
include __DIR__ . '/../templates/cuenta-header.php';
?>

<section class="seccion seccion--compacta">
  <div class="contenedor detalle-pedido">

    <div class="detalle-pedido__barra">
      <a class="enlace-volver" href="/mi-cuenta/pedidos"><?= icono('flecha-izq') ?> Mis pedidos</a>
      <div class="detalle-pedido__acciones">
        <a class="boton boton--contorno boton--chico" href="/mi-cuenta/pedidos/<?= $pedido->id ?>/pdf"><?= icono('descargar') ?> PDF</a>
        <a class="boton boton--contorno boton--chico" href="/mi-cuenta/pedidos/<?= $pedido->id ?>/repetir"><?= icono('repetir') ?> Repetir</a>
        <?php if ($pedido->cancelable()): ?>
          <button class="boton boton--peligro boton--chico" type="button" data-abrir-modal="confirmar-cancelar"><?= icono('cancelar') ?> Cancelar</button>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($pedido->estado === 'cancelado'): ?>
      <div class="estado-cancelado tarjeta">
        <?php include __DIR__ . '/../templates/insignia-estado.php'; ?>
        <p>Este pedido se canceló<?= $pedido->cancelado_en ? ' el ' . s(fecha($pedido->cancelado_en)) : '' ?>. Puedes repetirlo cuando quieras.</p>
      </div>
    <?php else: ?>
      <ol class="linea-tiempo" aria-label="Estado del pedido">
        <?php foreach ($pasos as $n => [$nombre, $texto]): ?>
          <li class="linea-tiempo__paso <?= $n <= $nivel ? 'is-hecho' : '' ?> <?= $n === $nivel ? 'is-actual' : '' ?>" <?= $n === $nivel ? 'aria-current="step"' : '' ?>>
            <span class="linea-tiempo__punto"><?= $n < $nivel || ($n === $nivel && $n === 4) ? '✓' : $n ?></span>
            <strong><?= $nombre ?></strong>
            <span><?= $texto ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>

    <div class="detalle-pedido__grid">
      <div class="tarjeta">
        <h2 class="tarjeta__titulo"><?= icono('paquete') ?> Productos</h2>
        <div class="renglones">
          <?php foreach ($detalles as $d): ?>
            <div class="renglon">
              <div>
                <span class="linea-pedido__sku"><?= s($d['sku']) ?></span>
                <p class="renglon__nombre"><?= s($d['nombre']) ?></p>
                <p class="renglon__precio"><?= (int) $d['cantidad'] ?> × <?= dinero($d['precio_unitario']) ?><?= (float) $d['descuento_pct'] > 0 ? ' · −' . (float) $d['descuento_pct'] . '%' : '' ?></p>
              </div>
              <span class="renglon__importe"><?= dinero($d['subtotal']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <dl class="resumen__importes">
          <div><dt>Subtotal</dt><dd><?= dinero($pedido->subtotal) ?></dd></div>
          <?php if ($pedido->descuento > 0): ?><div><dt>Descuento</dt><dd>−<?= dinero($pedido->descuento) ?></dd></div><?php endif; ?>
          <div><dt>IVA</dt><dd><?= dinero($pedido->iva) ?></dd></div>
          <div class="resumen__total"><dt>Total</dt><dd><?= dinero($pedido->total) ?></dd></div>
        </dl>
      </div>

      <div class="detalle-pedido__lateral">
        <div class="tarjeta">
          <h2 class="tarjeta__titulo">Resumen</h2>
          <dl class="datos">
            <div><dt>Estado</dt><dd><?php include __DIR__ . '/../templates/insignia-estado.php'; ?></dd></div>
            <div><dt>Folio</dt><dd><?= s($pedido->folio) ?></dd></div>
            <div><dt>Fecha</dt><dd><?= s(fecha($pedido->fecha)) ?></dd></div>
            <?php if ($entrega['ubicacion']): ?><div><dt>Ubicación de entrega</dt><dd><?= s($entrega['ubicacion']) ?></dd></div><?php endif; ?>
            <?php if ($entrega['sucursal']): ?><div><dt>Sucursal de entrega</dt><dd><?= s($entrega['sucursal']) ?></dd></div><?php endif; ?>
            <?php if ($venta): ?>
              <div><dt>Venta</dt><dd><?= s($venta['folio']) ?></dd></div>
              <div><dt>Entrega</dt><dd><?= s($entregas[$venta['entrega_estado'] ?? ''] ?? 'Por programar') ?><?= !empty($venta['entrega_fecha']) ? ' · ' . s(fecha($venta['entrega_fecha'])) : '' ?></dd></div>
            <?php endif; ?>
          </dl>
          <?php if ($entrega['comentarios']): ?>
            <p class="detalle-pedido__comentarios"><strong>Tus comentarios:</strong><br><?= nl2br(s($entrega['comentarios'])) ?></p>
          <?php endif; ?>
        </div>

        <div class="tarjeta ayuda-pedido">
          <p>¿Dudas sobre este pedido?</p>
          <a class="boton boton--contorno boton--bloque boton--chico" href="<?= s(whatsapp("Hola, tengo una duda sobre mi pedido {$pedido->folio}.")) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> Escríbenos</a>
        </div>
      </div>
    </div>

    <?php if ($fotos): ?>
      <section class="tarjeta fotos-entrega" aria-labelledby="fotos-titulo">
        <h2 class="tarjeta__titulo" id="fotos-titulo"><?= icono('camara') ?> Fotos de la entrega</h2>
        <div class="galeria">
          <?php foreach ($fotos as $i => $foto): ?>
            <button class="galeria__item" type="button" data-abrir-modal="foto-<?= (int) $foto['id'] ?>" aria-label="Ampliar foto <?= $i + 1 ?>">
              <img src="/mi-cuenta/fotos/<?= (int) $foto['id'] ?>?w=400" alt="Foto <?= $i + 1 ?> de la entrega" loading="lazy">
              <span class="galeria__fecha"><?= s(fecha($foto['tomada_en'] ?: $foto['fecha'])) ?></span>
            </button>
          <?php endforeach; ?>
        </div>
      </section>

      <?php foreach ($fotos as $i => $foto): ?>
        <dialog class="modal modal--foto" id="foto-<?= (int) $foto['id'] ?>" aria-label="Foto <?= $i + 1 ?> de la entrega">
          <button class="modal__cerrar" type="button" data-cerrar-modal aria-label="Cerrar"><?= icono('cerrar') ?></button>
          <img src="/mi-cuenta/fotos/<?= (int) $foto['id'] ?>?w=1600" alt="Foto <?= $i + 1 ?> de la entrega" loading="lazy">
        </dialog>
      <?php endforeach; ?>
    <?php elseif ($venta): ?>
      <p class="nota">Las fotos de la entrega aparecerán aquí cuando registremos la entrega.</p>
    <?php endif; ?>
  </div>
</section>

<?php if ($pedido->cancelable()): ?>
  <dialog class="modal modal--confirmar" id="confirmar-cancelar" aria-labelledby="confirmar-titulo">
    <div class="modal__info">
      <h2 class="modal__titulo" id="confirmar-titulo">¿Cancelar el pedido <?= s($pedido->folio) ?>?</h2>
      <p class="modal__subtitulo">Podrás repetirlo después si lo necesitas.</p>
      <form method="post" action="/mi-cuenta/pedidos/<?= $pedido->id ?>/cancelar" class="modal__acciones">
        <?= csrf_campo() ?>
        <button class="boton boton--contorno" type="button" data-cerrar-modal>No, conservar</button>
        <button class="boton boton--peligro" type="submit"><?= icono('cancelar') ?> Sí, cancelar</button>
      </form>
    </div>
  </dialog>
<?php endif; ?>
