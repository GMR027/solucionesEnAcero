<?php

/**
 * Conexion a la BD de cotizaciones (perezosa).
 *
 * Solo la usan el portal de clientes y sus modelos; las paginas publicas no
 * abren conexion, asi que siguen funcionando aunque MySQL no responda.
 *
 * El usuario (DB_USER) debe ser el de solo-lo-necesario que crea
 * cotizaciones/scripts/crear_usuario_portal.sh.
 */
function db(): mysqli
{
    static $db = null;

    if ($db === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $db = new mysqli(
            env('DB_HOST', 'localhost'),
            env('DB_USER', ''),
            env('DB_PASSWORD', ''),
            env('DB_NAME', 'cotizaciones'),
            (int) env('DB_PORT', '3306')
        );
        $db->set_charset('utf8mb4');
        $db->query("SET time_zone = '" . (new DateTime())->format('P') . "'");
    }

    return $db;
}

/** Id de la empresa (Soluciones en Acero) en la tabla `empresas`. */
function empresaId(): int
{
    return (int) env('EMPRESA_ID', '1');
}
