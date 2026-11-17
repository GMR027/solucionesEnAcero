<?php
/** @var array<string, \Model\Distribuidor[]> $grupos */
use Model\Distribuidor;

$encabezado = [
    'etiqueta' => "{$total} puntos de venta",
    'titulo' => 'Distribuidores',
    'texto' => 'Encuentra nuestros productos en ferreterías y casas de materiales de Baja California Sur.',
    'migas' => [['Distribuidores', null]],
];
include __DIR__ . '/../templates/pagina-header.php';
?>

<section class="seccion seccion--compacta distribuidores" data-distribuidores>
  <div class="contenedor">
    <div class="filtros">
      <div class="filtros__tabs" role="group" aria-label="Filtrar por ciudad">
        <button class="filtro is-activo" type="button" data-filtro="todos" aria-pressed="true">Todos <span><?= (int) $total ?></span></button>
        <?php foreach ($grupos as $municipio => $lista): ?>
          <button class="filtro" type="button" data-filtro="<?= s(Distribuidor::slugify($municipio)) ?>" aria-pressed="false"><?= s($municipio) ?> <span><?= count($lista) ?></span></button>
        <?php endforeach; ?>
      </div>
      <label class="buscador">
        <?= icono('buscar') ?>
        <span class="sr-only">Buscar distribuidor</span>
        <input type="search" placeholder="Buscar por nombre, colonia o calle…" data-buscador autocomplete="off">
      </label>
    </div>

    <p class="resultados" aria-live="polite" data-resultados></p>

    <?php foreach ($grupos as $municipio => $lista): ?>
      <div class="grupo" data-grupo="<?= s(Distribuidor::slugify($municipio)) ?>">
        <h2 class="grupo__titulo"><?= icono('ubicacion') ?> <?= s($municipio) ?></h2>
        <div class="distribuidores-grid">
          <?php foreach ($lista as $d): ?>
            <article class="distribuidor" data-distribuidor data-texto="<?= s(mb_strtolower("{$d->nombre} {$d->sucursal} {$d->direccion} {$d->municipio}")) ?>">
              <div class="distribuidor__imagen">
                <?php if ($d->imagen): ?>
                  <img src="<?= img($d->imagen) ?>" alt="Fachada de <?= s($d->nombre) ?>" loading="lazy" width="400" height="260">
                <?php endif; ?>
              </div>
              <div class="distribuidor__cuerpo">
                <h3 class="distribuidor__nombre"><?= s($d->nombre) ?></h3>
                <?php if ($d->sucursal): ?><p class="distribuidor__sucursal">Sucursal <?= s($d->sucursal) ?></p><?php endif; ?>
                <p class="distribuidor__direccion"><?= s($d->direccion) ?></p>
                <?php if ($d->maps): ?>
                  <a class="boton boton--contorno boton--chico" href="<?= s($d->maps) ?>" target="_blank" rel="noopener"><?= icono('ubicacion') ?> Cómo llegar</a>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <p class="vacio" data-vacio hidden>No encontramos distribuidores con esa búsqueda.</p>
  </div>
</section>

<section class="seccion seccion--compacta">
  <div class="contenedor">
    <div class="cta-banda">
      <div>
        <h2 class="titulo-seccion">¿No hay distribuidor cerca?</h2>
        <p>Escríbenos y te decimos cómo hacerte llegar tu pedido.</p>
      </div>
      <a class="boton boton--primario" href="<?= s(whatsapp('Hola, quiero saber cómo recibir un pedido en mi ciudad.')) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> Escríbenos</a>
    </div>
  </div>
</section>
