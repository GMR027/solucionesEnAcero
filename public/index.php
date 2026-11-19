<?php

// Servidor de desarrollo (php -S localhost:3000 -t public public/index.php):
// los archivos reales (CSS, JS, imagenes, video) los sirve el propio servidor.
if (PHP_SAPI === 'cli-server') {
    $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_string($ruta) && $ruta !== '/' && !str_contains($ruta, '..') && is_file(__DIR__ . $ruta)) {
        return false;
    }
}

require_once __DIR__ . '/../includes/app.php';

use Controllers\AuthController;
use Controllers\CuentaController;
use Controllers\FotosController;
use Controllers\ImagenesController;
use Controllers\PaginasController;
use Controllers\ProductosController;
use MVC\Router;

$router = new Router();

// Paginas publicas
$router->get('/', [PaginasController::class, 'inicio']);
$router->get('/distribuidores', [PaginasController::class, 'distribuidores']);

// Catalogo
$router->get('/productos', [ProductosController::class, 'index']);
$router->get('/productos/{slug}', [ProductosController::class, 'categoria']);
$router->get('/img/entregas/{id:[0-9]+}', [FotosController::class, 'galeria']);
$router->get('/img/catalogo/{archivo:[A-Za-z0-9_-]+\.(?:jpe?g|png|webp)}', [ImagenesController::class, 'catalogo']);

// Portal de clientes: sesion
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Portal de clientes: cuenta (todas exigen soloCliente() en el controlador)
$router->get('/mi-cuenta', [CuentaController::class, 'inicio']);
$router->get('/mi-cuenta/contrasena', [AuthController::class, 'contrasena']);
$router->post('/mi-cuenta/contrasena', [AuthController::class, 'contrasena']);
$router->get('/mi-cuenta/pedidos', [CuentaController::class, 'pedidos']);
$router->get('/mi-cuenta/pedidos/nuevo', [CuentaController::class, 'nuevo']); // antes de {id}
$router->post('/mi-cuenta/pedidos/nuevo', [CuentaController::class, 'nuevo']);
$router->get('/mi-cuenta/pedidos/{id}', [CuentaController::class, 'detalle']);
$router->post('/mi-cuenta/pedidos/{id}/cancelar', [CuentaController::class, 'cancelar']);
$router->get('/mi-cuenta/pedidos/{id}/repetir', [CuentaController::class, 'repetir']);
$router->get('/mi-cuenta/pedidos/{id}/pdf', [CuentaController::class, 'pdf']);
$router->get('/mi-cuenta/fotos/{id}', [FotosController::class, 'foto']);

$router->comprobarRutas();
