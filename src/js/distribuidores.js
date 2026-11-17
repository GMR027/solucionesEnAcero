// Filtro por ciudad y busqueda en la pagina de distribuidores
document.addEventListener('DOMContentLoaded', () => {
  const contenedor = document.querySelector('[data-distribuidores]');
  if (!contenedor) return;

  const filtros = contenedor.querySelectorAll('[data-filtro]');
  const buscador = contenedor.querySelector('[data-buscador]');
  const grupos = contenedor.querySelectorAll('[data-grupo]');
  const resultados = contenedor.querySelector('[data-resultados]');
  const vacio = contenedor.querySelector('[data-vacio]');

  let filtroActual = 'todos';

  const normalizar = (texto) => texto.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');

  const aplicar = () => {
    const termino = normalizar(buscador.value.trim());
    let visibles = 0;

    grupos.forEach((grupo) => {
      const coincideGrupo = filtroActual === 'todos' || grupo.dataset.grupo === filtroActual;
      let visiblesGrupo = 0;

      grupo.querySelectorAll('[data-distribuidor]').forEach((tarjeta) => {
        const mostrar = coincideGrupo && (!termino || normalizar(tarjeta.dataset.texto).includes(termino));
        tarjeta.hidden = !mostrar;
        if (mostrar) visiblesGrupo++;
      });

      grupo.hidden = visiblesGrupo === 0;
      visibles += visiblesGrupo;
    });

    vacio.hidden = visibles !== 0;
    resultados.textContent = termino || filtroActual !== 'todos'
      ? `${visibles} ${visibles === 1 ? 'distribuidor encontrado' : 'distribuidores encontrados'}`
      : '';
  };

  filtros.forEach((boton) => {
    boton.addEventListener('click', () => {
      filtroActual = boton.dataset.filtro;
      filtros.forEach((b) => {
        const activo = b === boton;
        b.classList.toggle('is-activo', activo);
        b.setAttribute('aria-pressed', String(activo));
      });
      aplicar();
    });
  });

  buscador.addEventListener('input', aplicar);
});
