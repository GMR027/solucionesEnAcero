<?php

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Variables de entorno: las reales (Kubernetes) tienen prioridad sobre includes/.env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

date_default_timezone_set('America/Mazatlan');

$enDesarrollo = ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'produccion') === 'desarrollo';
ini_set('display_errors', $enDesarrollo ? '1' : '0');
error_reporting(E_ALL);

require __DIR__ . '/funciones.php';

// La conexion a la BD (includes/database.php) se activara cuando el catalogo
// se lea desde la base de datos de cotizaciones.
