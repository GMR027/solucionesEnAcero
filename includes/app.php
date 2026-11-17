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
require __DIR__ . '/database.php';
require __DIR__ . '/sesion.php';
require __DIR__ . '/limitador.php';

iniciarSesion();
