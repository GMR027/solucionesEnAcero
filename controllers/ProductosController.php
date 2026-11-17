<?php

namespace Controllers;

use Model\Catalogo;
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
        $categoria = Categoria::buscar($slug);

        if (!$categoria) {
            if (!Catalogo::disponible()) {
                http_response_code(503);
                $router->render('paginas/sin-catalogo', ['titulo' => 'Catálogo no disponible']);
                return;
            }
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
