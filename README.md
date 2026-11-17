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
| `/login` | Inicio de sesion del portal de clientes |
| `/mi-cuenta/pedidos` | Pedidos del cliente (lista, nuevo, detalle, cancelar, PDF, repetir) |
| `/mi-cuenta/fotos/{id}` | Foto de entrega (proxy firmado hacia cotizaciones, con marca de agua) |
| `/mi-cuenta/contrasena` | Cambio de contrasena |

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

## Portal de clientes

Usa la BD de **cotizaciones** (contrato en `cotizaciones/GUIA.md`, "Pedidos y portal de clientes"):

- **Acceso:** `clientes.email` + `clientes.portal_contrasena` (bcrypt). El administrador da el acceso desde cotizaciones.
- **Pedidos:** se insertan en `pedidos`/`pedido_detalles` con `origen='portal'`, `estado='pendiente'`, precio de lista
  e importes iguales a `Cotizaciones::calcular`. El administrador los cotiza/vende desde `/admin/pedidos`.
- **Fotos de entrega:** `pedidos.venta_id → entregas → entrega_fotos`. El sitio verifica que la foto sea del cliente
  y la pide firmada (HMAC, `PORTAL_FOTOS_SECRETO`) a `COTIZACIONES_URL/portal/entregas/foto`, que la devuelve
  con la marca de agua de la entrega. El navegador nunca ve la URL de cotizaciones.

Configuracion (`includes/.env`, ver `includes/.env.example`):

1. En cotizaciones: `PORTAL_DB_PASSWORD=... scripts/crear_usuario_portal.sh` crea el usuario MySQL
   `portal_soluciones` con permisos por columna (sin costos, stock ni datos internos).
2. Generar el secreto compartido (`openssl rand -hex 32`) y ponerlo en ambos lados:
   `PORTAL_FOTOS_SECRETO` aqui y `secrets.portalFotosSecreto` en el Helm de cotizaciones.
3. `DB_*` con ese usuario, `EMPRESA_ID=1` y `COTIZACIONES_URL`
   (en el cluster: `http://cotizaciones.cotizaciones.svc.cluster.local`).

Seguridad: CSRF en todos los POST, cookie de sesion httponly/SameSite, `session_regenerate_id` al entrar,
huella de contrasena (cambiarla cierra las demas sesiones), limite de 5 intentos de login por IP y correo
cada 15 min, y toda consulta de pedidos filtra por `cliente_id` y `empresa_id`.

## Docker

```bash
docker build -t gmr27/solucionesenacero:dev .
docker run --rm -p 8080:80 gmr27/solucionesenacero:dev
```
