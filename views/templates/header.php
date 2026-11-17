<?php
$enInicio = ($pagina ?? '') === 'inicio';
$enlaces = [
    ['url' => '/#productos', 'texto' => 'Productos', 'activo' => ($pagina ?? '') === 'productos'],
    ['url' => '/#como-comprar', 'texto' => 'Cómo comprar', 'activo' => false],
    ['url' => '/#cobertura', 'texto' => 'Cobertura', 'activo' => false],
    ['url' => '/distribuidores', 'texto' => 'Distribuidores', 'activo' => ($pagina ?? '') === 'distribuidores'],
];
?>
<header class="header <?= $enInicio ? 'header--transparente' : '' ?>" data-header>
  <div class="contenedor header__barra">
    <a class="marca" href="/" aria-label="<?= s(sitio('nombre')) ?>, ir al inicio">
      <img class="marca__logo" src="<?= img('logo/logo.png') ?>" alt="" width="44" height="44">
      <span class="marca__texto">Soluciones <strong>en Acero</strong></span>
    </a>

    <button class="header__menu-btn" type="button" aria-expanded="false" aria-controls="navegacion" aria-label="Abrir menú" data-menu-btn>
      <?= icono('menu', 'icono icono--abrir') ?>
      <?= icono('cerrar', 'icono icono--cerrar') ?>
    </button>

    <nav class="navegacion" id="navegacion" aria-label="Principal" data-navegacion>
      <?php foreach ($enlaces as $enlace): ?>
        <a class="navegacion__enlace <?= $enlace['activo'] ? 'is-activo' : '' ?>" href="<?= s($enlace['url']) ?>" <?= $enlace['activo'] ? 'aria-current="page"' : '' ?>><?= s($enlace['texto']) ?></a>
      <?php endforeach; ?>
      <a class="boton boton--primario boton--chico navegacion__cta" href="<?= s(whatsapp('Hola, quiero cotizar.')) ?>" target="_blank" rel="noopener">
        <?= icono('whatsapp') ?> Cotizar
      </a>
    </nav>
  </div>
</header>
