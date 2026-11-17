<?php

namespace Controllers;

use Model\Categoria;
use Model\ProductoVenta;
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

        $productos = $categoria->productos();

        // Con sesion de cliente: id pedible de cada SKU para "Agregar a mi pedido"
        $idsPedibles = [];
        if (cliente()) {
            try {
                foreach ($productos as $producto) {
                    $idsPedibles[$producto->sku] = ProductoVenta::idPorSku($producto->sku);
                }
            } catch (\mysqli_sql_exception $e) {
                error_log('[ProductosController] ' . $e->getMessage());
            }
        }

        $router->render('productos/categoria', [
            'titulo' => $categoria->nombre,
            'descripcion' => $categoria->resumen,
            'pagina' => 'productos',
            'categoria' => $categoria,
            'productos' => $productos,
            'idsPedibles' => $idsPedibles,
            'otras' => array_filter(Categoria::all(), fn(Categoria $c) => $c->slug !== $slug),
            'script' => ['modal.js'],
        ]);
    }
}
