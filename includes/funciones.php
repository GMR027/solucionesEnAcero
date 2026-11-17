<?php

/** Escapa texto para imprimirlo en HTML. */
function s(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/** Lee una variable de entorno (real o de includes/.env). */
function env(string $clave, ?string $porDefecto = null): ?string
{
    $valor = $_ENV[$clave] ?? getenv($clave);
    return ($valor === false || $valor === null || $valor === '') ? $porDefecto : (string) $valor;
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

/** Ruta publica de un recurso compilado por gulp (public/build). */
function asset(string $ruta): string
{
    return '/build/' . ltrim($ruta, '/');
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
