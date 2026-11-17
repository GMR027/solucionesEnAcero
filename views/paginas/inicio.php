<?php
/** @var \Model\Categoria[] $categorias */
$anios = (int) date('Y') - (int) sitio('desde');
$pasos = [
    ['icono' => 'seleccionar', 'titulo' => 'Selecciona', 'texto' => 'Elige en el catálogo el registro, la tapa o la base que necesita tu obra. Cada producto trae su ficha técnica.'],
    ['icono' => 'pagar', 'titulo' => 'Paga y envía', 'texto' => 'Manda tu orden de compra y tu comprobante de pago por WhatsApp o correo electrónico.'],
    ['icono' => 'recibir', 'titulo' => 'Recibe', 'texto' => 'Entregamos a través de nuestros distribuidores en los municipios de Baja California Sur.'],
];
?>

<section class="hero" aria-labelledby="hero-titulo">
  <div class="hero__media" style="--portada: url('<?= img('portada.jpg') ?>')">
    <video class="hero__video" autoplay muted loop playsinline preload="metadata" poster="<?= img('portada.jpg') ?>" aria-hidden="true">
      <source src="/video/Video2-Sol-V1.webm" type="video/webm">
      <source src="/video/Video2-Sol-V1.mp4" type="video/mp4">
    </video>
  </div>
  <div class="hero__overlay"></div>

  <div class="contenedor hero__contenido">
    <p class="etiqueta etiqueta--hero" data-animar>Desde <?= (int) sitio('desde') ?> · <?= s(sitio('ciudad')) ?></p>
    <h1 class="hero__titulo" id="hero-titulo" data-animar>
      Acero que <span class="texto-acento">soporta</span> tu obra
    </h1>
    <p class="hero__texto" data-animar>
      Registros de ángulo y solera, tapas metálicas y bases para boiler.
      Medidas listas para instalar, con entrega en todo el sur de la península.
    </p>
    <div class="hero__acciones" data-animar>
      <a class="boton boton--primario" href="#productos">Ver catálogo <?= icono('flecha') ?></a>
      <a class="boton boton--contorno" href="<?= s(whatsapp('Hola, quiero cotizar.')) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> Cotizar por WhatsApp</a>
    </div>
  </div>

  <a class="hero__scroll" href="#nosotros" aria-label="Bajar a la siguiente sección"><span></span></a>
</section>

<section class="cifras" aria-label="Soluciones en Acero en números">
  <div class="contenedor cifras__grid">
    <div class="cifra" data-animar>
      <span class="cifra__numero" data-contador="<?= $anios ?>">+<?= $anios ?></span>
      <span class="cifra__texto">años en el mercado</span>
    </div>
    <div class="cifra" data-animar>
      <span class="cifra__numero" data-contador="<?= (int) $totalProductos ?>"><?= (int) $totalProductos ?></span>
      <span class="cifra__texto">productos en catálogo</span>
    </div>
    <div class="cifra" data-animar>
      <span class="cifra__numero" data-contador="<?= (int) $totalDistribuidores ?>"><?= (int) $totalDistribuidores ?></span>
      <span class="cifra__texto">puntos de venta</span>
    </div>
    <div class="cifra" data-animar>
      <span class="cifra__numero" data-contador="<?= count(sitio('cobertura')) ?>"><?= count(sitio('cobertura')) ?></span>
      <span class="cifra__texto">ciudades con entrega</span>
    </div>
  </div>
</section>

<section class="seccion nosotros" id="nosotros" aria-labelledby="nosotros-titulo">
  <div class="contenedor nosotros__grid">
    <div class="nosotros__imagen" data-animar>
      <img src="<?= img('nosotros.png') ?>" alt="Equipo de Soluciones en Acero trabajando" width="671" height="574" loading="lazy">
      <div class="nosotros__sello">
        <span class="nosotros__sello-numero"><?= (int) sitio('desde') ?></span>
        <span class="nosotros__sello-texto">en Baja California Sur</span>
      </div>
    </div>

    <div class="nosotros__texto" data-animar>
      <p class="etiqueta">Sobre nosotros</p>
      <h2 class="titulo-seccion" id="nosotros-titulo">Tu aliado estratégico en acero</h2>
      <p class="texto-grande">
        Somos un equipo comprometido con la mejora continua, enfocado en ofrecer soluciones integrales para nuestro mercado.
        Desde <?= (int) sitio('desde') ?> nos hemos posicionado gracias a la calidad, la puntualidad y el trabajo en equipo, construyendo relaciones sólidas con nuestros clientes.
      </p>
      <ul class="valores">
        <li class="valor"><?= icono('escudo') ?><div><strong>Calidad</strong><span>Marcos, contramarcos y armados con medidas fijas y acabado con primer.</span></div></li>
        <li class="valor"><?= icono('camion') ?><div><strong>Puntualidad</strong><span>Entregas a través de nuestra red de distribuidores en el estado.</span></div></li>
        <li class="valor"><?= icono('herramienta') ?><div><strong>Trabajo en equipo</strong><span>Te asesoramos para elegir el producto correcto para tu instalación.</span></div></li>
      </ul>
    </div>
  </div>
</section>

<section class="seccion seccion--placa productos" id="productos" aria-labelledby="productos-titulo">
  <div class="contenedor">
    <header class="encabezado-seccion" data-animar>
      <p class="etiqueta">Catálogo</p>
      <h2 class="titulo-seccion" id="productos-titulo">Líneas de producto</h2>
      <p class="encabezado-seccion__texto">Cada producto incluye ficha técnica con dimensiones, marco, contramarco, armado y tipo de uso recomendado.</p>
    </header>

    <div class="categorias">
      <?php foreach ($categorias as $i => $categoria): ?>
        <a class="categoria" href="<?= s($categoria->url()) ?>" data-animar style="--retraso: <?= $i * 80 ?>ms">
          <div class="categoria__imagen">
            <img src="<?= img($categoria->portada) ?>" alt="" loading="lazy" width="600" height="600">
          </div>
          <div class="categoria__cuerpo">
            <span class="categoria__numero"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <h3 class="categoria__titulo"><?= s($categoria->nombre) ?></h3>
            <p class="categoria__texto"><?= s($categoria->resumen) ?></p>
            <span class="categoria__enlace">
              <?= $categoria->totalProductos() ?> <?= $categoria->totalProductos() === 1 ? 'modelo' : 'modelos' ?>
              <?= icono('flecha') ?>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="seccion como-comprar" id="como-comprar" aria-labelledby="comprar-titulo">
  <div class="contenedor">
    <header class="encabezado-seccion" data-animar>
      <p class="etiqueta">Cómo comprar</p>
      <h2 class="titulo-seccion" id="comprar-titulo">Tu pedido en tres pasos</h2>
    </header>

    <ol class="pasos">
      <?php foreach ($pasos as $i => $paso): ?>
        <li class="paso" data-animar style="--retraso: <?= $i * 120 ?>ms">
          <span class="paso__numero"><?= $i + 1 ?></span>
          <span class="paso__icono"><?= icono($paso['icono']) ?></span>
          <h3 class="paso__titulo"><?= s($paso['titulo']) ?></h3>
          <p class="paso__texto"><?= s($paso['texto']) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
    <p class="nota">*Consulta las restricciones de entrega y la disponibilidad según tu solicitud.</p>
  </div>
</section>

<section class="seccion cobertura" id="cobertura" aria-labelledby="cobertura-titulo">
  <div class="contenedor cobertura__grid">
    <div class="cobertura__texto" data-animar>
      <p class="etiqueta">Cobertura y entrega</p>
      <h2 class="titulo-seccion" id="cobertura-titulo">De Loreto a Cabo San Lucas</h2>
      <p class="texto-grande">Entregamos nuestros productos a través de distribuidores en los municipios de Baja California Sur.</p>
      <ul class="chips">
        <?php foreach (sitio('cobertura') as $ciudad): ?>
          <li class="chip"><?= icono('ubicacion') ?> <?= s($ciudad) ?></li>
        <?php endforeach; ?>
      </ul>
      <a class="boton boton--primario" href="/distribuidores"><?= icono('tienda') ?> Ver <?= (int) $totalDistribuidores ?> distribuidores</a>
    </div>
    <div class="cobertura__imagen" data-animar>
      <img src="<?= img('cobertura.jpg') ?>" alt="Entrega de productos de acero" loading="lazy">
    </div>
  </div>
</section>

<section class="seccion novedades" aria-labelledby="novedades-titulo">
  <div class="contenedor">
    <div class="novedades__panel" style="--imagen: url('<?= img('blog.jpg') ?>')" data-animar>
      <div class="novedades__texto">
        <p class="etiqueta">Blog y novedades</p>
        <h2 class="titulo-seccion" id="novedades-titulo">Entérate de todo</h2>
        <p>Mantente al día con lo último sobre nuestros productos, promociones exclusivas y lanzamientos futuros en nuestras redes sociales.</p>
      </div>
      <a class="boton boton--claro" href="<?= s(sitio('facebook')) ?>" target="_blank" rel="noopener"><?= icono('facebook') ?> Ver novedades</a>
    </div>
  </div>
</section>
