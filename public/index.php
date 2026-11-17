<?php

require_once __DIR__ . '/../includes/app.php';

use Controllers\AuthController;
use Controllers\CuentaController;
use Controllers\FotosController;
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
