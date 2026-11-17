// Soluciones en Acero — comportamiento comun a todas las paginas
document.addEventListener('DOMContentLoaded', () => {
  headerAlScroll();
  menuMovil();
  animarAlAparecer();
});

/** Header solido al hacer scroll (en el inicio empieza transparente). */
function headerAlScroll() {
  const header = document.querySelector('[data-header]');
  if (!header) return;

  const actualizar = () => header.classList.toggle('is-scrolled', window.scrollY > 40);
  actualizar();
  window.addEventListener('scroll', actualizar, { passive: true });
}

/** Menu hamburguesa en movil. */
function menuMovil() {
  const header = document.querySelector('[data-header]');
  const boton = document.querySelector('[data-menu-btn]');
  const navegacion = document.querySelector('[data-navegacion]');
  if (!header || !boton || !navegacion) return;

  const alternar = (abrir) => {
    header.classList.toggle('is-abierto', abrir);
    document.body.classList.toggle('menu-abierto', abrir);
    boton.setAttribute('aria-expanded', String(abrir));
    boton.setAttribute('aria-label', abrir ? 'Cerrar menú' : 'Abrir menú');
  };

  boton.addEventListener('click', () => alternar(!header.classList.contains('is-abierto')));
  navegacion.querySelectorAll('a').forEach((enlace) => enlace.addEventListener('click', () => alternar(false)));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') alternar(false); });
  window.matchMedia('(min-width: 1024px)').addEventListener('change', () => alternar(false));
}

/** Aparicion de elementos [data-animar] al entrar en pantalla. */
function animarAlAparecer() {
  const elementos = document.querySelectorAll('[data-animar]');
  if (!('IntersectionObserver' in window)) {
    elementos.forEach((el) => el.classList.add('is-visible'));
    return;
  }

  const observador = new IntersectionObserver((entradas) => {
    entradas.forEach((entrada) => {
      if (!entrada.isIntersecting) return;
      entrada.target.classList.add('is-visible');
      observador.unobserve(entrada.target);
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

  elementos.forEach((el) => observador.observe(el));
}
