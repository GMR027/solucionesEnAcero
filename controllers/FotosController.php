<?php

namespace Controllers;

use Model\Galeria;
use Model\Pedido;
use MVC\Router;

/**
 * Proxy de fotos de entrega.
 *
 * 1. Verifica que la foto pertenezca a un pedido del cliente con sesion.
 * 2. La pide firmada (HMAC) a cotizaciones: /portal/entregas/foto, que la
 *    entrega con la marca de agua de la entrega.
 * El navegador nunca ve la URL de cotizaciones ni el nombre del archivo.
 *
 * Tambien sirve, sin sesion, las fotos de la galeria de inicio (ver galeria()).
 */
class FotosController
{
    private const ANCHOS = [400, 1600];
    private const VIGENCIA = 300;
    private const ANCHO_GALERIA = 1200;

    public static function foto(Router $router, string $id): void
    {
        $cliente = soloCliente();
        $fotoId = (int) $id;
        $ancho = in_array((int) ($_GET['w'] ?? 0), self::ANCHOS, true) ? (int) $_GET['w'] : 1600;

        if (!Pedido::fotoDelCliente($fotoId, $cliente->id)) {
            self::noDisponible();
        }

        [$contenido, $tipo] = self::pedirFirmada($fotoId, $ancho) ?? self::noDisponible();

        header('Content-Type: ' . $tipo);
        header('Content-Length: ' . strlen($contenido));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=86400');
        echo $contenido;
    }

    /**
     * Foto de la galeria publica de inicio (sin sesion).
     *
     * Solo sirve fotos que hoy estan entre las ultimas de la empresa
     * (Galeria::fotoPermitida) y las pide en modo "galeria": con el logo y sin
     * fecha ni ubicacion del cliente. Se guardan en cache en el disco del pod.
     */
    public static function galeria(Router $router, string $id): void
    {
        $fotoId = (int) $id;
        if (!Galeria::fotoPermitida($fotoId)) {
            self::noDisponible();
        }

        $cache = self::rutaCache("galeria-{$fotoId}-" . self::ANCHO_GALERIA . '.jpg');
        $contenido = is_file($cache) ? (string) file_get_contents($cache) : '';
        if ($contenido === '') {
            [$contenido] = self::pedirFirmada($fotoId, self::ANCHO_GALERIA, 'galeria') ?? self::noDisponible();
            // Cache con escritura atomica; si falla, la foto se sirve igual.
            $temporal = $cache . '.' . bin2hex(random_bytes(4)) . '.tmp';
            if (@file_put_contents($temporal, $contenido) === false || !@rename($temporal, $cache)) {
                @unlink($temporal);
            }
        }

        header('Content-Type: image/jpeg');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        echo $contenido;
    }

    /**
     * Pide la foto a cotizaciones con una URL firmada (HMAC con vigencia corta).
     *
     * @return array{0: string, 1: string}|null [bytes, tipo]
     */
    private static function pedirFirmada(int $fotoId, int $ancho, string $modo = ''): ?array
    {
        $secreto = (string) env('PORTAL_FOTOS_SECRETO', '');
        $base = rtrim((string) env('COTIZACIONES_URL', ''), '/');
        if (strlen($secreto) < 32 || $base === '') {
            return null;
        }

        $expira = time() + self::VIGENCIA;
        $datos = "{$fotoId}|{$ancho}|{$expira}" . ($modo !== '' ? "|{$modo}" : '');
        $parametros = ['id' => $fotoId, 'w' => $ancho, 'exp' => $expira, 'sig' => hash_hmac('sha256', $datos, $secreto)];
        if ($modo !== '') {
            $parametros['modo'] = $modo;
        }

        $curl = curl_init($base . '/portal/entregas/foto?' . http_build_query($parametros));
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXFILESIZE => 15 * 1024 * 1024,
        ]);
        $contenido = curl_exec($curl);
        $codigo = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $tipo = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        if (!is_string($contenido) || $codigo !== 200 || !preg_match('#^image/(jpeg|png|webp)#', $tipo)) {
            error_log("[FotosController] foto {$fotoId}" . ($modo !== '' ? " ({$modo})" : '') . ": HTTP {$codigo} {$tipo}");
            return null;
        }
        return [$contenido, $tipo];
    }

    private static function rutaCache(string $archivo): string
    {
        $dir = sys_get_temp_dir() . '/sea-img';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir . '/' . $archivo;
    }

    private static function noDisponible(): never
    {
        http_response_code(404);
        header('Cache-Control: no-store');
        exit;
    }
}
