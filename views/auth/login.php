<section class="auth">
  <div class="auth__fondo" aria-hidden="true"></div>
  <div class="contenedor auth__grid">
    <div class="auth__texto">
      <p class="etiqueta">Portal de clientes</p>
      <h1 class="auth__titulo">Pide en línea y sigue cada entrega</h1>
      <ul class="auth__beneficios">
        <li><?= icono('carrito') ?> Arma tu pedido con precio de lista e IVA calculado.</li>
        <li><?= icono('paquete') ?> Consulta el estado de cada pedido en tiempo real.</li>
        <li><?= icono('camara') ?> Ve las fotos de la entrega de tus pedidos vendidos.</li>
      </ul>
    </div>

    <div class="tarjeta auth__tarjeta">
      <h2 class="tarjeta__titulo"><?= icono('candado') ?> Iniciar sesión</h2>

      <?php if ($error): ?>
        <p class="alerta alerta--error" role="alert"><?= s($error) ?></p>
      <?php endif; ?>

      <form class="formulario" method="post" action="/login" novalidate>
        <?= csrf_campo() ?>
        <input type="hidden" name="r" value="<?= s($regreso) ?>">

        <div class="campo">
          <label class="campo__etiqueta" for="email">Correo electrónico</label>
          <input class="campo__control" type="email" id="email" name="email" value="<?= s($email) ?>" autocomplete="username" required autofocus>
        </div>

        <div class="campo">
          <label class="campo__etiqueta" for="contrasena">Contraseña</label>
          <input class="campo__control" type="password" id="contrasena" name="contrasena" autocomplete="current-password" required>
        </div>

        <button class="boton boton--primario boton--bloque" type="submit">Entrar <?= icono('flecha') ?></button>
      </form>

      <p class="auth__ayuda">
        ¿No tienes acceso o olvidaste tu contraseña?
        <a href="<?= s(whatsapp('Hola, quiero acceso al portal de clientes de Soluciones en Acero.')) ?>" target="_blank" rel="noopener">Escríbenos por WhatsApp</a>.
      </p>
    </div>
  </div>
</section>
