# Soluciones en Acero

Sitio web de Soluciones en Acero (La Paz, B.C.S.): PHP 8.3 con MVC, SASS y JavaScript.

## Estructura

```
public/        Front controller (index.php), .htaccess, video y build/ (generado por gulp)
router/        Router con rutas GET/POST y parametros {slug}
controllers/   PaginasController, ProductosController
models/        Categoria, Producto, Distribuidor (base: Modelo)
data/          Datos del catalogo (temporal hasta conectar la BD de cotizaciones)
views/         layout.php, templates/, paginas/, productos/
includes/      app.php (arranque), funciones.php, database.php (aun sin usar), .env.example
src/           scss/, js/, img/, icons/, logo/  (fuentes que compila gulp)
```

## Rutas

| Ruta | Pagina |
|---|---|
| `/` | Inicio |
| `/productos/{slug}` | Productos de una categoria (`registros-angulo`, `registros-solera`, `tapas-metalicas`, `bases`) |
| `/distribuidores` | Distribuidores con filtro por ciudad y buscador |

Las URLs del sitio estatico anterior (`/src/pages/*.html`) redirigen con 301 desde `public/.htaccess`.

## Desarrollo

```bash
composer install
npm install
npm run build        # compila SASS, copia JS e imagenes a public/build
npm run dev          # build + watch
npm run serve        # php -S localhost:3000 -t public
```

## Datos del catalogo

Los productos de `data/productos.php` son una copia de la tabla `productos` de la BD de cotizaciones
(`empresa_id = 1`), **solo con columnas publicas** (sin precio, costo ni stock).
`Producto::especificaciones()` convierte la `descripcion` de la BD en la tabla de la ficha tecnica.

Pendiente para la fase del CRUD / BD:
- Tabla de categorias (hoy `productos.categoria` es texto libre; slug y portada viven en `data/categorias.php`).
- Campo para la imagen de uso/detalle (hoy en `data/imagenes.php`).
- Leer el catalogo desde la BD con un usuario de solo lectura sobre una vista publica.

## Docker

```bash
docker build -t gmr27/solucionesenacero:dev .
docker run --rm -p 8080:80 gmr27/solucionesenacero:dev
```
