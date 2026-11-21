<?php
/** @var \Model\Pedido $pedido */
/** @var array $detalles */
/** @var string $logo */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 28px 34px; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1d2228; }
  .franja { height: 5px; background: #f37736; margin-bottom: 14px; }
  .cabecera { width: 100%; border-collapse: collapse; }
  .cabecera td { vertical-align: top; }
  .marca { font-size: 18px; font-weight: bold; letter-spacing: 1px; }
  .marca span { color: #f37736; }
  .gris { color: #66707a; }
  .folio { text-align: right; }
  .folio h1 { margin: 0; font-size: 20px; letter-spacing: 1px; }
  .estado { display: inline-block; padding: 3px 8px; background: #1d2228; color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; }
  .bloque { margin-top: 18px; }
  .bloque h2 { font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #f37736; margin: 0 0 6px; }
  table.renglones { width: 100%; border-collapse: collapse; margin-top: 6px; }
  .renglones th { background: #1d2228; color: #fff; text-align: left; padding: 7px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; }
  .renglones td { padding: 7px 8px; border-bottom: 1px solid #e3e6ea; }
  .renglones td.miniatura { width: 34px; padding: 4px 6px; text-align: center; }
  .renglones img { width: 28px; height: 28px; }
  .num { text-align: right; white-space: nowrap; }
  table.totales { width: 42%; margin-left: 58%; border-collapse: collapse; margin-top: 10px; }
  .totales td { padding: 5px 8px; }
  .totales .total td { font-size: 13px; font-weight: bold; border-top: 2px solid #1d2228; }
  .nota { margin-top: 22px; padding: 10px 12px; background: #f4f5f7; border-left: 3px solid #f37736; }
  .pie { position: fixed; bottom: -10px; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #8a929b; }
</style>
</head>
<body>
  <div class="franja"></div>
  <table class="cabecera">
    <tr>
      <td style="width: 52px"><img src="<?= $logo ?>" width="44" height="44" alt=""></td>
      <td>
        <div class="marca">SOLUCIONES <span>EN ACERO</span></div>
        <div class="gris"><?= s(sitio('ciudad')) ?> · WhatsApp <?= s(sitio('telefono')) ?></div>
      </td>
      <td class="folio">
        <h1>PEDIDO <?= s($pedido->folio) ?></h1>
        <div class="gris"><?= s(fecha($pedido->fecha)) ?></div>
        <div style="margin-top: 5px"><span class="estado"><?= s($pedido->etiquetaEstado()) ?></span></div>
      </td>
    </tr>
  </table>

  <div class="bloque">
    <h2>Cliente</h2>
    <div><strong><?= s(cliente()['nombre'] ?? '') ?></strong></div>
  </div>

  <div class="bloque">
    <h2>Productos</h2>
    <table class="renglones">
      <thead>
        <tr><th>Imagen</th><th>Código</th><th>Producto</th><th class="num">Cant.</th><th class="num">Precio</th><th class="num">Importe</th></tr>
      </thead>
      <tbody>
        <?php foreach ($detalles as $d): $miniatura = miniaturaProductoBase64($d['imagen'] ?? null); ?>
          <tr>
            <td class="miniatura"><?php if ($miniatura !== ''): ?><img src="<?= $miniatura ?>" alt=""><?php else: ?>—<?php endif; ?></td>
            <td><?= s($d['sku']) ?></td>
            <td><?= s($d['nombre']) ?></td>
            <td class="num"><?= (int) $d['cantidad'] ?> <?= s($d['unidad']) ?></td>
            <td class="num"><?= dinero($d['precio_unitario']) ?></td>
            <td class="num"><?= dinero($d['subtotal']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <table class="totales">
      <tr><td>Subtotal</td><td class="num"><?= dinero($pedido->subtotal) ?></td></tr>
      <?php if ($pedido->descuento > 0): ?><tr><td>Descuento</td><td class="num">−<?= dinero($pedido->descuento) ?></td></tr><?php endif; ?>
      <tr><td>IVA</td><td class="num"><?= dinero($pedido->iva) ?></td></tr>
      <tr class="total"><td>Total</td><td class="num"><?= dinero($pedido->total) ?></td></tr>
    </table>
  </div>

  <?php if ($pedido->comentarios): ?>
    <div class="nota"><strong>Comentarios:</strong> <?= nl2br(s($pedido->comentarios)) ?></div>
  <?php endif; ?>
  <?php if ($pedido->condiciones_envio): ?>
    <div class="bloque"><h2>Condiciones de envío</h2><div><?= nl2br(s($pedido->condiciones_envio)) ?></div></div>
  <?php endif; ?>
  <?php if ($pedido->condiciones_compra): ?>
    <div class="bloque"><h2>Condiciones de compra</h2><div><?= nl2br(s($pedido->condiciones_compra)) ?></div></div>
  <?php endif; ?>

  <div class="pie">Importes con precio de lista. Este documento no es una cotización ni un comprobante fiscal. · <?= s(sitio('nombre')) ?></div>
</body>
</html>
