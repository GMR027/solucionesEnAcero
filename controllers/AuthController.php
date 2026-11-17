<?php

namespace Controllers;

use Limitador;
use Model\Cliente;
use MVC\Router;

class AuthController
{
    public static function login(Router $router): void
    {
        if (cliente()) {
            redirigir(rutaInterna($_GET['r'] ?? null));
        }

        $email = '';
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            $email = trim((string) ($_POST['email'] ?? ''));
            $contrasena = (string) ($_POST['contrasena'] ?? '');
            $claves = Limitador::claves($email);
            $espera = Limitador::espera($claves);

            if ($espera > 0) {
                http_response_code(429);
                $error = 'Demasiados intentos. Espera ' . (int) ceil($espera / 60) . ' min e inténtalo de nuevo.';
            } else {
                try {
                    $cliente = Cliente::autenticar($email, $contrasena);
                } catch (\mysqli_sql_exception $e) {
                    error_log('[login] ' . $e->getMessage());
                    $cliente = null;
                    $error = 'No pudimos verificar tu cuenta en este momento. Inténtalo más tarde.';
                }

                if ($cliente) {
                    Limitador::limpiar($claves);
                    entrarComoCliente($cliente);
                    redirigir(rutaInterna($_POST['r'] ?? null));
                }

                if ($error === null) {
                    Limitador::fallo($claves);
                    $error = 'Correo o contraseña incorrectos.';
                }
            }
        }

        $router->render('auth/login', [
            'titulo' => 'Iniciar sesión',
            'pagina' => 'login',
            'email' => $email,
            'error' => $error,
            'regreso' => rutaInterna($_GET['r'] ?? $_POST['r'] ?? null),
        ]);
    }

    public static function logout(Router $router): void
    {
        verificarCsrf();
        salir();
        iniciarSesion();
        flash('exito', 'Cerraste sesión.');
        redirigir('/');
    }

    public static function contrasena(Router $router): void
    {
        $cliente = soloCliente();
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            $actual = (string) ($_POST['actual'] ?? '');
            $nueva = (string) ($_POST['nueva'] ?? '');
            $confirmacion = (string) ($_POST['confirmacion'] ?? '');

            if (!$cliente->verificar($actual)) {
                $errores[] = 'Tu contraseña actual no es correcta.';
            }
            $errores = array_merge($errores, Cliente::validarNueva($nueva, $confirmacion));
            if (!$errores && $cliente->verificar($nueva)) {
                $errores[] = 'La nueva contraseña debe ser distinta de la actual.';
            }

            if (!$errores) {
                $cliente->cambiarContrasena($nueva);
                entrarComoCliente($cliente); // nueva huella: las otras sesiones se cierran
                flash('exito', 'Tu contraseña se actualizó. Cerramos tus otras sesiones abiertas.');
                redirigir('/mi-cuenta/pedidos');
            }
        }

        $router->render('cuenta/contrasena', [
            'titulo' => 'Cambiar contraseña',
            'pagina' => 'cuenta',
            'errores' => $errores,
        ]);
    }
}
