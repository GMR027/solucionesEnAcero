// Nuevo pedido: botones +/- y resumen en vivo (el servidor recalcula todo al guardar)
document.addEventListener('DOMContentLoaded', () => {
  const formulario = document.querySelector('[data-pedido]');
  if (!formulario) return;

  const iva = Number(formulario.dataset.iva || 16);
  const lineas = [...formulario.querySelectorAll('[data-linea]')];
  const moneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
  const barra = document.querySelector('[data-barra-total]');
  const salida = (sel) => formulario.querySelector(sel);
  const escribir = (sel, texto) => document.querySelectorAll(sel).forEach((el) => { el.textContent = texto; });
  const redondear = (n) => Math.round((n + Number.EPSILON) * 100) / 100;

  const actualizar = () => {
    let subtotal = 0;
    let piezas = 0;

    lineas.forEach((linea) => {
      const entrada = linea.querySelector('[data-cantidad]');
      const cantidad = Math.max(0, Math.min(Number(entrada.max) || 9999, parseInt(entrada.value, 10) || 0));
      const importe = cantidad * Number(linea.dataset.precio);
      subtotal += importe;
      piezas += cantidad;
      linea.classList.toggle('is-seleccionada', cantidad > 0);
      linea.querySelector('[data-importe]').textContent = cantidad > 0 ? moneda.format(importe) : '';
    });

    subtotal = redondear(subtotal);
    const impuesto = redondear(subtotal * iva / 100);
    escribir('[data-total-piezas]', piezas);
    salida('[data-subtotal]').textContent = moneda.format(subtotal);
    salida('[data-iva-monto]').textContent = moneda.format(impuesto);
    escribir('[data-total]', moneda.format(redondear(subtotal + impuesto)));
    if (barra) barra.classList.toggle('is-visible', piezas > 0);
    salida('[data-enviar]').disabled = piezas === 0;
  };

  lineas.forEach((linea) => {
    const entrada = linea.querySelector('[data-cantidad]');
    const cambiar = (delta) => {
      entrada.value = Math.max(0, (parseInt(entrada.value, 10) || 0) + delta);
      actualizar();
    };
    linea.querySelector('[data-mas]').addEventListener('click', () => cambiar(1));
    linea.querySelector('[data-menos]').addEventListener('click', () => cambiar(-1));
    entrada.addEventListener('input', actualizar);
  });

  // Evita doble envio
  formulario.addEventListener('submit', () => {
    const boton = salida('[data-enviar]');
    setTimeout(() => { boton.disabled = true; }, 0);
  });

  actualizar();

  const primera = formulario.querySelector('.linea-pedido.is-seleccionada');
  if (primera && new URLSearchParams(location.search).has('producto')) {
    primera.scrollIntoView({ block: 'center' });
  }
});
