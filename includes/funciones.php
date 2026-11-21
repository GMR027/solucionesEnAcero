<?php

/** Escapa texto para imprimirlo en HTML. */
function s(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Lee una variable de entorno. La real (Kubernetes, shell) tiene prioridad;
 * includes/.env (cargado en $_ENV por phpdotenv) es solo el respaldo local.
 */
function env(string $clave, ?string $porDefecto = null): ?string
{
    $valor = getenv($clave);
    if ($valor === false || $valor === '') {
        $valor = $_ENV[$clave] ?? null;
    }
    return ($valor === null || $valor === '') ? $porDefecto : (string) $valor;
}

function debuguear(mixed $variable): never
{
    echo '<pre>';
    var_dump($variable);
    echo '</pre>';
    exit;
}

/** Datos generales del sitio (contacto, redes, cobertura). */
function sitio(?string $clave = null): mixed
{
    static $datos = null;
    $datos ??= require __DIR__ . '/../data/sitio.php';
    return $clave === null ? $datos : ($datos[$clave] ?? null);
}

/**
 * Ruta publica de un recurso compilado por gulp (public/build).
 *
 * Lleva ?v=<fecha del archivo>: Cloudflare cachea /build/ una semana, asi que
 * sin version un despliegue nuevo seguiria sirviendo el CSS/JS anterior.
 */
function asset(string $ruta): string
{
    $ruta = '/build/' . ltrim($ruta, '/');
    $archivo = __DIR__ . '/../public' . rawurldecode($ruta);
    $version = is_file($archivo) ? filemtime($archivo) : false;
    return $version ? $ruta . '?v=' . $version : $ruta;
}

/** Ruta publica de una imagen dentro de public/build/img. */
function img(string $ruta): string
{
    return asset('img/' . implode('/', array_map('rawurlencode', explode('/', ltrim($ruta, '/')))));
}

/** Enlace de WhatsApp con mensaje prellenado. */
function whatsapp(string $mensaje = ''): string
{
    $url = 'https://wa.me/' . sitio('whatsapp');
    return $mensaje === '' ? $url : $url . '?text=' . rawurlencode($mensaje);
}

/** Devuelve el SVG de un icono de views/templates/iconos.php. */
function icono(string $nombre, string $clase = 'icono'): string
{
    static $iconos = null;
    $iconos ??= require __DIR__ . '/../views/templates/iconos.php';
    $svg = $iconos[$nombre] ?? '';
    return str_replace('<svg ', '<svg class="' . s($clase) . '" aria-hidden="true" focusable="false" ', $svg);
}

function redirigir(string $url, int $codigo = 303): never
{
    header('Location: ' . $url, true, $codigo);
    exit;
}

/** Ruta interna segura para redirigir despues del login (evita //otro-sitio). */
function rutaInterna(?string $ruta, string $porDefecto = '/mi-cuenta/pedidos'): string
{
    $ruta = (string) $ruta;
    return preg_match('#^/(?!/)[A-Za-z0-9/_\-]*$#', $ruta) ? $ruta : $porDefecto;
}

function dinero(float|string|null $monto): string
{
    return '$' . number_format((float) $monto, 2, '.', ',');
}

function fecha(?string $fecha): string
{
    if (!$fecha) {
        return '';
    }
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $t = strtotime($fecha);
    return date('j', $t) . ' ' . $meses[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
}

/** URL publica de una imagen del catalogo (servida por ImagenesController). */
function imagenCatalogo(?string $archivo): ?string
{
    $archivo = basename((string) $archivo);
    return $archivo !== '' ? '/img/catalogo/' . rawurlencode($archivo) : null;
}

/**
 * Miniatura de un producto en base64 para los PDF (dompdf no sale a Internet).
 *
 * Primero busca la version _mini en la cache del proxy /img/catalogo; si no
 * existe, la pide al servicio interno de cotizaciones y la guarda. Si tampoco
 * hay miniatura, usa la imagen completa. Devuelve '' si no hay imagen valida.
 */
function miniaturaProductoBase64(?string $archivo): string
{
    $archivo = basename(trim((string) $archivo));
    if ($archivo === '' || str_contains($archivo, '..')) {
        return '';
    }

    static $sinServicio = false;

    $mini = preg_replace('/\.([a-z0-9]+)$/i', '_mini.$1', $archivo);
    $candidatos = array_values(array_unique(array_filter([(string) $mini, $archivo])));
    $dir = sys_get_temp_dir() . '/sea-img';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    foreach ($candidatos as $nombre) {
        $ruta = $dir . '/' . $nombre;
        $bytes = null;

        if (is_file($ruta)) {
            $bytes = @file_get_contents($ruta);
        } elseif (!$sinServicio) {
            $base = rtrim((string) env('COTIZACIONES_URL', ''), '/');
            if ($base === '') {
                continue;
            }

            $curl = curl_init($base . '/public/image/' . rawurlencode($nombre));
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXFILESIZE => 8 * 1024 * 1024,
            ]);
            $descarga = curl_exec($curl);
            $codigo = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            curl_close($curl);

            if (!is_string($descarga) || $codigo === 0) {
                $sinServicio = true; // El servicio interno no responde: no reintentar 60 veces.
                continue;
            }
            if ($codigo !== 200 || strlen($descarga) > 8 * 1024 * 1024) {
                continue;
            }

            $bytes = $descarga;
            $temporal = $ruta . '.' . bin2hex(random_bytes(4)) . '.tmp';
            if (@file_put_contents($temporal, $bytes) !== false) {
                @rename($temporal, $ruta);
            }
        }

        if (!is_string($bytes) || $bytes === '') {
            continue;
        }

        $tipo = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        if (!in_array($tipo, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            continue;
        }

        return 'data:' . $tipo . ';base64,' . base64_encode($bytes);
    }

    return '';
}
