<?php

namespace Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Model\Pedido;
use Model\ProductoVenta;
use MVC\Router;

class CuentaController
{
    public static function inicio(Router $router): void
    {
        soloCliente();
        redirigir('/mi-cuenta/pedidos');
    }

    public static function pedidos(Router $router): void
    {
        $cliente = soloCliente();
        $filtro = (string) ($_GET['filtro'] ?? 'todos');
        $lista = Pedido::delCliente($cliente->id, (int) ($_GET['pagina'] ?? 1), $filtro);

        $router->render('cuenta/pedidos', [
            'titulo' => 'Mis pedidos',
            'pagina' => 'cuenta',
            'cliente' => $cliente,
            'lista' => $lista,
        ]);
    }

    public static function nuevo(Router $router): void
    {
        $cliente = soloCliente();
        $disponibles = ProductoVenta::disponibles();
        $cantidades = [];
        $comentarios = '';
        $sucursal = '';
        $ubicacion = Pedido::UBICACION_POR_DEFECTO;
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            $cantidades = array_map('intval', (array) ($_POST['cantidad'] ?? []));
            $comentarios = (string) ($_POST['comentarios'] ?? '');
            $sucursal = (string) ($_POST['sucursal'] ?? '');
            $ubicacion = Pedido::normalizarUbicacion((string) ($_POST['ubicacion'] ?? ''));
            $lineas = Pedido::lineasDesdeFormulario($cantidades, $disponibles);

            $id = Pedido::crear($cliente, $lineas, $comentarios, $sucursal, $ubicacion);
            if ($id) {
                $pedido = Pedido::buscar($id, $cliente->id);
                flash('exito', "Recibimos tu pedido {$pedido->folio}. Te contactaremos para confirmarlo.");
                redirigir("/mi-cuenta/pedidos/{$id}");
            }
            $errores = Pedido::errores();
        } elseif (isset($_GET['desde'])) {
            $anterior = Pedido::buscar((int) $_GET['desde'], $cliente->id);
            if ($anterior) {
                $cantidades = $anterior->cantidades();
                $entrega = Pedido::desglosarEntrega($anterior->comentarios);
                $sucursal = $entrega['sucursal'] ?? '';
                $ubicacion = Pedido::normalizarUbicacion($entrega['ubicacion'] ?? '');
                $omitidos = count(array_diff_key($cantidades, $disponibles));
                if ($omitidos) {
                    $errores[] = "{$omitidos} producto(s) del pedido {$anterior->folio} ya no están disponibles y se omitieron.";
                }
            }
        } elseif (isset($_GET['producto'])) {
            $cantidades[(int) $_GET['producto']] = 1;
        }

        $router->render('cuenta/nuevo', [
            'titulo' => 'Nuevo pedido',
            'pagina' => 'cuenta',
            'grupos' => ProductoVenta::porCategoria(),
            'cantidades' => array_intersect_key($cantidades, $disponibles),
            'comentarios' => $comentarios,
            'sucursal' => $sucursal,
            'ubicacion' => $ubicacion,
            'errores' => $errores,
            'iva' => Pedido::IVA,
            'script' => ['pedido.js'],
        ]);
    }

    public static function detalle(Router $router, string $id): void
    {
        $cliente = soloCliente();
        $pedido = Pedido::buscar((int) $id, $cliente->id);
        if (!$pedido) {
            $router->noEncontrado();
            return;
        }

        $router->render('cuenta/detalle', [
            'titulo' => "Pedido {$pedido->folio}",
            'pagina' => 'cuenta',
            'pedido' => $pedido,
            'detalles' => $pedido->detalles(),
            'venta' => $pedido->venta(),
            'fotos' => $pedido->fotos(),
            'script' => ['modal.js'],
        ]);
    }

    public static function cancelar(Router $router, string $id): void
    {
        $cliente = soloCliente();
        verificarCsrf();
        $pedido = Pedido::buscar((int) $id, $cliente->id);
        if (!$pedido) {
            $router->noEncontrado();
            return;
        }

        if (Pedido::cancelar($pedido->id, $cliente->id)) {
            flash('exito', "Cancelaste el pedido {$pedido->folio}.");
        } else {
            flash('error', "El pedido {$pedido->folio} ya no se puede cancelar: ya lo estamos atendiendo. Escríbenos por WhatsApp.");
        }
        redirigir("/mi-cuenta/pedidos/{$pedido->id}");
    }

    public static function repetir(Router $router, string $id): void
    {
        soloCliente();
        redirigir('/mi-cuenta/pedidos/nuevo?desde=' . (int) $id);
    }

    public static function pdf(Router $router, string $id): void
    {
        $cliente = soloCliente();
        $pedido = Pedido::buscar((int) $id, $cliente->id);
        if (!$pedido) {
            $router->noEncontrado();
            return;
        }

        $detalles = $pedido->detalles();
        $logo = 'data:image/png;base64,' . base64_encode((string) file_get_contents(dirname(__DIR__) . '/src/logo/logo.png'));

        ob_start();
        include dirname(__DIR__) . '/views/cuenta/pdf.php';
        $html = ob_get_clean();

        $opciones = new Options();
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter');
        $dompdf->render();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $pedido->folio . '.pdf"');
        header('Cache-Control: private, no-store');
        echo $dompdf->output();
    }
}
