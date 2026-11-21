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

  const disponibleDe = (linea) => {
    const valor = Number.parseInt(linea.dataset.disponible, 10);
    return Number.isFinite(valor) ? Math.max(0, valor) : 0;
  };

  const avisar = (linea, texto) => {
    const aviso = linea.querySelector('[data-aviso]');
    if (aviso) aviso.textContent = texto;
  };

  const avisoCantidad = (linea, disponible) => {
    const unidad = linea.dataset.unidad || 'pza';
    return `Solo puedes comprar ${disponible} ${unidad} de este producto.`;
  };

  const ajustarCantidad = (linea, cantidad, mostrarAviso) => {
    const entrada = linea.querySelector('[data-cantidad]');
    const disponible = disponibleDe(linea);
    const pedida = Math.max(0, Number.parseInt(cantidad, 10) || 0);
    const final = Math.min(pedida, disponible);

    if (pedida > disponible && mostrarAviso) {
      avisar(linea, disponible > 0 ? avisoCantidad(linea, disponible) : 'Este producto no está disponible por el momento.');
    } else if (pedida <= disponible) {
      avisar(linea, '');
    }

    entrada.value = String(final);
    return final;
  };

  const actualizar = () => {
    let subtotal = 0;
    let piezas = 0;

    lineas.forEach((linea) => {
      const entrada = linea.querySelector('[data-cantidad]');
      const disponible = disponibleDe(linea);
      const cantidad = Math.max(0, Math.min(disponible, Number.parseInt(entrada.value, 10) || 0));
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
    const disponible = disponibleDe(linea);

    if (disponible < 1) {
      entrada.value = '0';
      entrada.disabled = true;
      linea.querySelectorAll('[data-mas], [data-menos]').forEach((boton) => { boton.disabled = true; });
      return;
    }

    // Un pedido repetido puede traer cantidades mayores a la existencia actual.
    if ((Number.parseInt(entrada.value, 10) || 0) > disponible) {
      ajustarCantidad(linea, disponible, true);
    }

    const cambiar = (delta) => {
      const actual = Number.parseInt(entrada.value, 10) || 0;
      if (delta > 0 && actual >= disponible) {
        avisar(linea, avisoCantidad(linea, disponible));
        return;
      }
      avisar(linea, '');
      entrada.value = String(Math.max(0, Math.min(disponible, actual + delta)));
      actualizar();
    };

    linea.querySelector('[data-mas]').addEventListener('click', () => cambiar(1));
    linea.querySelector('[data-menos]').addEventListener('click', () => cambiar(-1));

    entrada.addEventListener('input', () => {
      const pedida = Number.parseInt(entrada.value, 10) || 0;
      if (pedida > disponible) {
        ajustarCantidad(linea, disponible, true);
      } else {
        avisar(linea, '');
      }
      actualizar();
    });

    entrada.addEventListener('change', () => {
      ajustarCantidad(linea, entrada.value, true);
      actualizar();
    });
  });

  // Nunca enviar una cantidad por encima de la existencia.
  formulario.addEventListener('submit', (evento) => {
    const invalida = lineas.some((linea) => {
      const entrada = linea.querySelector('[data-cantidad]');
      return (Number.parseInt(entrada.value, 10) || 0) > disponibleDe(linea);
    });

    if (invalida) {
      evento.preventDefault();
      lineas.forEach((linea) => ajustarCantidad(linea, linea.querySelector('[data-cantidad]').value, true));
      actualizar();
      return;
    }

    // Evita doble envio
    const boton = salida('[data-enviar]');
    setTimeout(() => { boton.disabled = true; }, 0);
  });

  actualizar();

  const primera = formulario.querySelector('.linea-pedido.is-seleccionada');
  if (primera && new URLSearchParams(location.search).has('producto')) {
    primera.scrollIntoView({ block: 'center' });
  }
});
