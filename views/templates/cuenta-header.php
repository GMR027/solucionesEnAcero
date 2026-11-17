<?php
/** Variables: $tituloCuenta, $subtituloCuenta (opcional), $pestana ('pedidos'|'nuevo'|'contrasena'|null) */
$pestanas = [
    'pedidos' => ['/mi-cuenta/pedidos', 'Mis pedidos', 'paquete'],
    'nuevo' => ['/mi-cuenta/pedidos/nuevo', 'Nuevo pedido', 'carrito'],
    'contrasena' => ['/mi-cuenta/contrasena', 'Contraseña', 'candado'],
];
?>
<section class="pagina-header pagina-header--cuenta">
  <div class="contenedor">
    <p class="etiqueta">Hola, <?= s(cliente()['nombre'] ?? '') ?></p>
    <h1 class="pagina-header__titulo"><?= s($tituloCuenta) ?></h1>
    <?php if (!empty($subtituloCuenta)): ?><p class="pagina-header__texto"><?= $subtituloCuenta ?></p><?php endif; ?>
    <nav class="pestanas" aria-label="Mi cuenta">
      <?php foreach ($pestanas as $clave => [$url, $texto, $ico]): ?>
        <a class="pestana <?= ($pestana ?? '') === $clave ? 'is-activa' : '' ?>" href="<?= $url ?>" <?= ($pestana ?? '') === $clave ? 'aria-current="page"' : '' ?>><?= icono($ico) ?> <?= $texto ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</section>
