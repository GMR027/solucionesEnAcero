<?php

namespace Controllers;

use Model\Categoria;
use Model\Distribuidor;
use Model\Producto;
use MVC\Router;

class PaginasController
{
    public static function inicio(Router $router): void
    {
        $router->render('paginas/inicio', [
            'titulo' => 'Registros, tapas y bases de acero en Baja California Sur',
            'descripcion' => 'Registros de ángulo y solera, tapas metálicas y bases para boiler. Entregas en La Paz, Los Cabos, Todos Santos, Comondú y Loreto.',
            'pagina' => 'inicio',
            'categorias' => Categoria::all(),
            'totalProductos' => count(Producto::all()),
            'totalDistribuidores' => count(Distribuidor::all()),
            'script' => ['inicio.js'],
        ]);
    }

    public static function distribuidores(Router $router): void
    {
        $grupos = Distribuidor::porMunicipio();

        $router->render('paginas/distribuidores', [
            'titulo' => 'Distribuidores autorizados',
            'descripcion' => 'Encuentra nuestros registros, tapas y bases en ferreterías y casas de materiales de Baja California Sur.',
            'pagina' => 'distribuidores',
            'grupos' => $grupos,
            'total' => array_sum(array_map('count', $grupos)),
            'script' => ['distribuidores.js'],
        ]);
    }
}
