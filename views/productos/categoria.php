<?php
/** @var \Model\Categoria $categoria */
/** @var \Model\Producto[] $productos */
/** @var \Model\Categoria[] $otras */
$encabezado = [
    'etiqueta' => count($productos) . (count($productos) === 1 ? ' modelo' : ' modelos'),
    'titulo' => $categoria->nombre,
    'texto' => $categoria->resumen,
    'migas' => [['Productos', '/#productos'], [$categoria->nombre, null]],
];
include __DIR__ . '/../templates/pagina-header.php';
?>

<section class="seccion seccion--compacta">
  <div class="contenedor">
    <div class="productos-grid">
      <?php foreach ($productos as $i => $producto): ?>
        <?php $retraso = $i * 70; include __DIR__ . '/../templates/producto-card.php'; ?>
      <?php endforeach; ?>
    </div>

    <?php if (!$productos): ?>
      <p class="vacio">Pronto agregaremos productos a esta línea.</p>
    <?php endif; ?>
  </div>
</section>

<section class="seccion seccion--placa">
  <div class="contenedor">
    <header class="encabezado-seccion">
      <p class="etiqueta">Sigue explorando</p>
      <h2 class="titulo-seccion">Otras líneas</h2>
    </header>
    <div class="otras-lineas">
      <?php foreach ($otras as $otra): ?>
        <a class="otra-linea" href="<?= s($otra->url()) ?>">
          <img src="<?= img($otra->portada) ?>" alt="" loading="lazy" width="120" height="120">
          <span><?= s($otra->nombre) ?></span>
          <?= icono('flecha') ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php foreach ($productos as $producto): ?>
  <?php include __DIR__ . '/../templates/modal-producto.php'; ?>
<?php endforeach; ?>
