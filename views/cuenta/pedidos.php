<?php
/** @var array $lista */
$tituloCuenta = 'Mis pedidos';
$totalTodos = (int) ($lista['conteos']['todos'] ?? 0);
$subtituloCuenta = $totalTodos === 0 ? '' : $totalTodos . ' pedido' . ($totalTodos === 1 ? '' : 's') . ' con Soluciones en Acero';
$pestana = 'pedidos';
$filtroActivo = (string) ($lista['filtro'] ?? 'todos');
$conteos = $lista['conteos'] ?? [];

$urlFiltro = static function (string $clave) use ($filtroActivo): string {
    return '/mi-cuenta/pedidos' . ($clave === 'todos' ? '' : '?filtro=' . rawurlencode($clave));
};
$urlPagina = static function (int $pagina) use ($filtroActivo): string {
    return '/mi-cuenta/pedidos?' . http_build_query(array_filter([
        'filtro' => $filtroActivo !== 'todos' ? $filtroActivo : null,
        'pagina' => $pagina,
    ]));
};
$textosVacio = [
    'todos' => ['No tienes pedidos', 'Arma tu primer pedido con nuestro catálogo. Lo revisamos y te confirmamos por WhatsApp.'],
    'entregados' => ['Aún no tienes pedidos entregados', 'Cuando recibas un pedido aparecerá en esta pestaña.'],
    'en-proceso' => ['No tienes pedidos en proceso', 'Aquí verás los pedidos que estamos revisando, cotizando o preparando.'],
    'cancelados' => ['No tienes pedidos cancelados', 'Los pedidos que canceles aparecerán en esta pestaña.'],
];
[$vacioTitulo, $vacioTexto] = $textosVacio[$filtroActivo] ?? $textosVacio['todos'];
include __DIR__ . '/../templates/cuenta-header.php';
?>

<section class="seccion seccion--compacta">
  <div class="contenedor">
    <?php if ($totalTodos === 0): ?>
      <div class="vacio-cuenta tarjeta">
        <span class="vacio-cuenta__icono"><?= icono('carrito') ?></span>
        <h2 class="titulo-seccion">Aún no tienes pedidos</h2>
        <p>Arma tu primer pedido con nuestro catálogo. Lo revisamos y te confirmamos por WhatsApp.</p>
        <a class="boton boton--primario" href="/mi-cuenta/pedidos/nuevo"><?= icono('mas') ?> Hacer un pedido</a>
      </div>
    <?php else: ?>
      <nav class="filtros-pedidos" aria-label="Filtrar pedidos">
        <?php foreach (\Model\Pedido::FILTROS as $clave => $texto): $activo = $filtroActivo === $clave; ?>
          <a class="filtro-pedido <?= $activo ? 'is-activo' : '' ?>" href="<?= s($urlFiltro($clave)) ?>" <?= $activo ? 'aria-current="page"' : '' ?>>
            <?= s($texto) ?><span><?= (int) ($conteos[$clave] ?? 0) ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <div class="barra-acciones">
        <a class="boton boton--primario" href="/mi-cuenta/pedidos/nuevo"><?= icono('mas') ?> Nuevo pedido</a>
      </div>

      <?php if (!$lista['pedidos']): ?>
        <div class="vacio-cuenta tarjeta">
          <span class="vacio-cuenta__icono"><?= icono('carrito') ?></span>
          <h2 class="titulo-seccion"><?= s($vacioTitulo) ?></h2>
          <p><?= s($vacioTexto) ?></p>
          <?php if ($filtroActivo !== 'todos'): ?>
            <a class="boton boton--contorno" href="/mi-cuenta/pedidos">Ver todos los pedidos</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="tabla-pedidos" role="table" aria-label="Pedidos">
          <div class="tabla-pedidos__fila tabla-pedidos__cabecera" role="row">
            <span role="columnheader">Folio</span>
            <span role="columnheader">Fecha</span>
            <span role="columnheader">Productos</span>
            <span role="columnheader">Estado</span>
            <span role="columnheader" class="a-la-derecha">Total</span>
            <span role="columnheader"><span class="sr-only">Acciones</span></span>
          </div>
          <?php foreach ($lista['pedidos'] as $pedido): ?>
            <a class="tabla-pedidos__fila" role="row" href="/mi-cuenta/pedidos/<?= $pedido->id ?>">
              <span role="cell" class="tabla-pedidos__folio"><?= s($pedido->folio) ?></span>
              <span role="cell" data-etiqueta="Fecha"><?= s(fecha($pedido->fecha)) ?></span>
              <span role="cell" data-etiqueta="Productos"><?= (int) $pedido->renglones ?></span>
              <span role="cell"><?php include __DIR__ . '/../templates/insignia-estado.php'; ?></span>
              <span role="cell" class="a-la-derecha tabla-pedidos__total"><?= dinero($pedido->total) ?></span>
              <span role="cell" class="tabla-pedidos__ir"><?= icono('flecha') ?></span>
            </a>
          <?php endforeach; ?>
        </div>

        <?php if ($lista['paginas'] > 1): ?>
          <nav class="paginacion" aria-label="Páginas">
            <?php for ($p = 1; $p <= $lista['paginas']; $p++): ?>
              <a class="paginacion__enlace <?= $p === $lista['pagina'] ? 'is-activa' : '' ?>" href="<?= s($urlPagina($p)) ?>" <?= $p === $lista['pagina'] ? 'aria-current="page"' : '' ?>><?= $p ?></a>
            <?php endfor; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
