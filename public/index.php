<?php

require_once __DIR__ . '/../includes/app.php';

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

$router->comprobarRutas();
