<footer class="footer">
  <div class="contenedor footer__grid">
    <div class="footer__marca">
      <a class="marca" href="/">
        <img class="marca__logo" src="<?= img('logo/logo.png') ?>" alt="" width="44" height="44" loading="lazy">
        <span class="marca__texto">Soluciones <strong>en Acero</strong></span>
      </a>
      <p><?= s(sitio("lema")) ?>. Desde <?= (int) sitio("desde") ?>.</p>
    </div>

    <div>
      <h2 class="footer__titulo">Productos</h2>
      <ul class="footer__lista">
        <?php foreach (\Model\Categoria::all() as $cat): ?>
          <li><a href="<?= s($cat->url()) ?>"><?= s($cat->nombre) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h2 class="footer__titulo">Sitio</h2>
      <ul class="footer__lista">
        <li><a href="/#como-comprar">Cómo comprar</a></li>
        <li><a href="/#cobertura">Cobertura</a></li>
        <li><a href="/distribuidores">Distribuidores</a></li>
      </ul>
    </div>

    <div>
      <h2 class="footer__titulo">Contacto</h2>
      <ul class="footer__lista footer__contacto">
        <li><a href="<?= s(whatsapp()) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> <?= s(sitio('telefono')) ?></a></li>
        <li><a href="<?= s(sitio('facebook')) ?>" target="_blank" rel="noopener"><?= icono('facebook') ?> Facebook</a></li>
        <li><span><?= icono('ubicacion') ?> <?= s(sitio('ciudad')) ?></span></li>
      </ul>
    </div>
  </div>

  <div class="contenedor footer__legal">
    <p>&copy; <?= date('Y') ?> <?= s(sitio('nombre')) ?>. Todos los derechos reservados.</p>
  </div>
</footer>
