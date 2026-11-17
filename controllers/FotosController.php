<?php

namespace Controllers;

use Model\Pedido;
use MVC\Router;

/**
 * Proxy de fotos de entrega.
 *
 * 1. Verifica que la foto pertenezca a un pedido del cliente con sesion.
 * 2. La pide firmada (HMAC) a cotizaciones: /portal/entregas/foto, que la
 *    entrega con la marca de agua de la entrega.
 * El navegador nunca ve la URL de cotizaciones ni el nombre del archivo.
 */
class FotosController
{
    private const ANCHOS = [400, 1600];
    private const VIGENCIA = 300;

    public static function foto(Router $router, string $id): void
    {
        $cliente = soloCliente();
        $fotoId = (int) $id;
        $ancho = in_array((int) ($_GET['w'] ?? 0), self::ANCHOS, true) ? (int) $_GET['w'] : 1600;

        $secreto = (string) env('PORTAL_FOTOS_SECRETO', '');
        $base = rtrim((string) env('COTIZACIONES_URL', ''), '/');

        if (strlen($secreto) < 32 || $base === '' || !Pedido::fotoDelCliente($fotoId, $cliente->id)) {
            self::noDisponible();
        }

        $expira = time() + self::VIGENCIA;
        $firma = hash_hmac('sha256', "{$fotoId}|{$ancho}|{$expira}", $secreto);
        $url = $base . '/portal/entregas/foto?' . http_build_query([
            'id' => $fotoId, 'w' => $ancho, 'exp' => $expira, 'sig' => $firma,
        ]);

        $curl = curl_init($url);
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

        if ($contenido === false || $codigo !== 200 || !preg_match('#^image/(jpeg|png|webp)#', $tipo)) {
            error_log("[FotosController] foto {$fotoId}: HTTP {$codigo} {$tipo}");
            self::noDisponible();
        }

        header('Content-Type: ' . $tipo);
        header('Content-Length: ' . strlen($contenido));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=86400');
        echo $contenido;
    }

    private static function noDisponible(): never
    {
        http_response_code(404);
        header('Cache-Control: no-store');
        exit;
    }
}
