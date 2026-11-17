<?php

namespace Controllers;

use Model\Categoria;
use MVC\Router;

class ProductosController
{
    public static function index(Router $router): void
    {
        header('Location: /#productos', true, 302);
    }

    public static function categoria(Router $router, string $slug): void
    {
        $categoria = Categoria::where('slug', $slug);

        if (!$categoria) {
            $router->noEncontrado();
            return;
        }

        $router->render('productos/categoria', [
            'titulo' => $categoria->nombre,
            'descripcion' => $categoria->resumen,
            'pagina' => 'productos',
            'categoria' => $categoria,
            'productos' => $categoria->productos(),
            'otras' => array_filter(Categoria::all(), fn(Categoria $c) => $c->slug !== $slug),
            'script' => ['modal.js'],
        ]);
    }
}
