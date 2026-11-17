<?php

/**
 * Conexion a MySQL (aun no se usa).
 *
 * Se activara cuando los modelos lean el catalogo desde la BD de cotizaciones,
 * mediante un usuario de solo lectura sobre una vista publica (sin precio ni stock).
 */
function conectarDB(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $db = new mysqli(
        env('DB_HOST', 'localhost'),
        env('DB_USER', 'root'),
        env('DB_PASSWORD', ''),
        env('DB_NAME', 'cotizaciones'),
        (int) env('DB_PORT', '3306')
    );
    $db->set_charset('utf8mb4');

    return $db;
}
