<?php
$tituloCuenta = 'Cambiar contraseña';
$subtituloCuenta = 'Al cambiarla cerramos tus sesiones abiertas en otros dispositivos.';
$pestana = 'contrasena';
include __DIR__ . '/../templates/cuenta-header.php';
?>

<section class="seccion seccion--compacta">
  <div class="contenedor">
    <div class="tarjeta tarjeta--angosta">
      <?php foreach ($errores as $error): ?>
        <p class="alerta alerta--error" role="alert"><?= s($error) ?></p>
      <?php endforeach; ?>

      <form class="formulario" method="post" action="/mi-cuenta/contrasena" novalidate>
        <?= csrf_campo() ?>
        <div class="campo">
          <label class="campo__etiqueta" for="actual">Contraseña actual</label>
          <input class="campo__control" type="password" id="actual" name="actual" autocomplete="current-password" required>
        </div>
        <div class="campo">
          <label class="campo__etiqueta" for="nueva">Nueva contraseña</label>
          <input class="campo__control" type="password" id="nueva" name="nueva" autocomplete="new-password" minlength="8" maxlength="72" required>
          <span class="campo__ayuda">Mínimo 8 caracteres.</span>
        </div>
        <div class="campo">
          <label class="campo__etiqueta" for="confirmacion">Confirma la nueva contraseña</label>
          <input class="campo__control" type="password" id="confirmacion" name="confirmacion" autocomplete="new-password" required>
        </div>
        <button class="boton boton--primario boton--bloque" type="submit"><?= icono('candado') ?> Guardar contraseña</button>
      </form>
    </div>
  </div>
</section>
