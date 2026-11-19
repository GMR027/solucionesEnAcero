// Pagina de inicio: contadores y enlace activo segun la seccion visible
document.addEventListener('DOMContentLoaded', () => {
  contadores();
  resaltarSeccion();
  galeria();
});

// Galeria de entregas: en pantallas tactiles el primer toque expande el panel
// y el segundo abre la foto. Con cursor, el hover ya lo expande.
function galeria() {
  const lista = document.querySelector('[data-galeria]');
  if (!lista || window.matchMedia('(hover: hover)').matches) return;

  lista.addEventListener('click', (evento) => {
    const panel = evento.target.closest('.entregas-galeria__panel');
    if (!panel || panel.classList.contains('entregas-galeria__panel--activo')) return;
    evento.preventDefault();
    lista.querySelector('.entregas-galeria__panel--activo')?.classList.remove('entregas-galeria__panel--activo');
    panel.classList.add('entregas-galeria__panel--activo');
  });
}

function contadores() {
  const numeros = document.querySelectorAll('[data-contador]');
  const sinMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (sinMovimiento || !('IntersectionObserver' in window)) return;

  const animar = (el) => {
    const final = Number(el.dataset.contador);
    const prefijo = el.textContent.trim().startsWith('+') ? '+' : '';
    const duracion = 1400;
    const inicio = performance.now();

    const paso = (ahora) => {
      const t = Math.min((ahora - inicio) / duracion, 1);
      const suavizado = 1 - Math.pow(1 - t, 3);
      el.textContent = prefijo + Math.round(final * suavizado);
      if (t < 1) requestAnimationFrame(paso);
    };
    requestAnimationFrame(paso);
  };

  const observador = new IntersectionObserver((entradas) => {
    entradas.forEach((entrada) => {
      if (!entrada.isIntersecting) return;
      animar(entrada.target);
      observador.unobserve(entrada.target);
    });
  }, { threshold: 0.6 });

  numeros.forEach((n) => observador.observe(n));
}

function resaltarSeccion() {
  const enlaces = [...document.querySelectorAll('.navegacion__enlace[href^="/#"]')];
  const secciones = enlaces
    .map((enlace) => document.querySelector(enlace.getAttribute('href').slice(1)))
    .filter(Boolean);
  if (!secciones.length || !('IntersectionObserver' in window)) return;

  const observador = new IntersectionObserver((entradas) => {
    entradas.forEach((entrada) => {
      if (!entrada.isIntersecting) return;
      enlaces.forEach((enlace) => {
        enlace.classList.toggle('is-activo', enlace.getAttribute('href') === `/#${entrada.target.id}`);
      });
    });
  }, { rootMargin: '-45% 0px -50% 0px' });

  secciones.forEach((seccion) => observador.observe(seccion));

  // En el hero no hay seccion activa
  window.addEventListener('scroll', () => {
    if (window.scrollY < window.innerHeight * 0.5) enlaces.forEach((e) => e.classList.remove('is-activo'));
  }, { passive: true });
}
