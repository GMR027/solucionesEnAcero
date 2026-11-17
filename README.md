# Soluciones en Acero

Sitio web de Soluciones en Acero (La Paz, B.C.S.): PHP 8.3 con MVC, SASS y JavaScript.

## Estructura

```
public/        Front controller (index.php), .htaccess, video y build/ (generado por gulp)
router/        Router con rutas GET/POST y parametros {slug}
controllers/   PaginasController, ProductosController
models/        Categoria, Producto, Distribuidor (base: Modelo)
data/          Distribuidores y datos generales del sitio (el catalogo sale de la BD)
recursos/      Imagenes para la carga inicial del catalogo en cotizaciones (no van en la imagen Docker)
views/         layout.php, templates/, paginas/, productos/
includes/      app.php (arranque), funciones.php, database.php (aun sin usar), .env.example
src/           scss/, js/, img/, icons/, logo/  (fuentes que compila gulp)
```

## Rutas

| Ruta | Pagina |
|---|---|
| `/` | Inicio |
| `/productos/{slug}` | Productos de una categoria (`registros-de-angulo`, `registros-de-solera`, `tapas-metalicas`, `bases`) |
| `/img/catalogo/{archivo}` | Imagenes de productos y categorias (proxy con cache hacia cotizaciones) |
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
npm run serve        # php -S localhost:3000 -t public public/index.php
```

## Catalogo desde la BD

El catalogo publico se lee de la BD de **cotizaciones** (migracion 053) y se administra en su panel:

- **Productos** (`/admin/productos`): salen en el sitio si estan activos, son de Soluciones en Acero y tienen
  marcada la casilla «Publicar en el sitio web». La ficha tecnica se arma de `descripcion`
  (frases «Clave: valor.» y la linea «Tipo de uso: ...»); imagen principal e imagen de uso del mismo formulario.
- **Categorias** (`/admin/productos/categorias`): nombre, direccion web (`/productos/<slug>`, no cambia al
  renombrar), descripcion, portada y orden. Sin portada se usa la imagen del primer producto.
- **Imagenes**: `/img/catalogo/<archivo>` las pide al servicio interno de cotizaciones, solo si pertenecen a
  productos activos de la empresa o a sus categorias, y las guarda en cache en el pod (cache del navegador de 1 año).
- Si la BD no responde, el inicio y distribuidores siguen funcionando y el catalogo muestra un aviso.
- Precio, costo y existencias nunca se leen en el catalogo publico (solo el portal muestra el precio de lista).

**Carga inicial de imagenes** de uso y portadas (las que tenia el sitio estatico), una vez por ambiente:
`recursos/imagenes-web/` trae las imagenes y `mapa.txt`. En el cluster:

```bash
POD=$(kubectl -n cotizaciones get pod -l app=cotizaciones -o jsonpath='{.items[0].metadata.name}')
kubectl -n cotizaciones cp recursos/imagenes-web "$POD":/tmp/imagenes-web
kubectl -n cotizaciones exec "$POD" -- php scripts/importar_imagenes_web.php /tmp/imagenes-web
```

Los distribuidores siguen en `data/distribuidores.php`.

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

## Despliegue (cluster, https://solucionesenacero.iguzman.com.mx)

Chart en `helm/solucionesenacero` (namespace `solucionesenacero`, ingress `public`, TLS `letsencrypt-prod`).
El sitio no tiene MySQL propio: usa la BD de cotizaciones por `mysql.cotizaciones.svc.cluster.local`
y pide las fotos a `cotizaciones.cotizaciones.svc.cluster.local`.

### Primera vez

1. **Generar los dos secretos** (guardalos en tu gestor de contrasenas):
   ```bash
   openssl rand -hex 24   # contrasena del usuario MySQL portal_soluciones
   openssl rand -hex 32   # secreto de fotos (compartido con cotizaciones)
   ```
2. **Cotizaciones**: en `cotizaciones/helm/cotizaciones/values.local.yaml` agregar
   `secrets.portalFotosSecreto: "<secreto de fotos>"` y desplegar la rama `portal-fotos-firmadas`
   con su `./deploy.sh`.
3. **Usuario MySQL** (desde el repo de cotizaciones; requiere las migraciones 052 y 053 ya aplicadas):
   ```bash
   PORTAL_DB_PASSWORD='<contrasena MySQL>' \
   MYSQL_CMD='kubectl -n cotizaciones exec -i deploy/mysql -- sh -c "exec mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\""' \
   scripts/crear_usuario_portal.sh cotizaciones
   ```
4. **Este sitio**:
   ```bash
   cp helm/solucionesenacero/values.local.yaml.example helm/solucionesenacero/values.local.yaml
   # llenar dbPassword y portalFotosSecreto
   docker login
   ./deploy.sh
   ```
   `deploy.sh` valida los secretos, revisa que cotizaciones exista, construye y sube
   `gmr27/solucionesenacero:<n>`, hace `helm upgrade --install` y una prueba de humo de la BD.
5. **Imagenes del catalogo**: ver «Carga inicial de imagenes» arriba.
6. **DNS**: `solucionesenacero.iguzman.com.mx` debe apuntar al cluster (igual que los demas subdominios).

### Actualizaciones

`./deploy.sh` (tag auto-incremental en `.deploy-tag`). No hace falta `npm run build`: el Dockerfile compila los assets.

Notas: una sola replica (sesiones y limite de login en el disco del pod); `CONFIAR_PROXY=1` hace que el
limite de intentos use la IP real que manda ingress-nginx (`X-Real-IP`).
La rama `master` sigue publicada en Netlify (sitio estatico): mezclar `rediseno-mvc` a `master`
solo cuando el sitio del cluster este verificado.

## Docker

```bash
docker build -t gmr27/solucionesenacero:dev .
docker run --rm -p 8080:80 gmr27/solucionesenacero:dev
```
