<?php

/**
 * Limite de intentos de inicio de sesion (por IP y por correo).
 * Guarda marcas de tiempo en archivos del directorio temporal del sistema;
 * suficiente para una sola replica. Si el sitio escala a varias replicas,
 * mover esto a una tabla o a Redis.
 */
final class Limitador
{
    public const MAX_INTENTOS = 5;
    public const VENTANA = 900; // 15 minutos

    private static function archivo(string $clave): string
    {
        $dir = sys_get_temp_dir() . '/sea-login';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir . '/' . hash('sha256', $clave) . '.json';
    }

    /** @return int[] marcas de tiempo vigentes */
    private static function intentos(string $clave): array
    {
        $ruta = self::archivo($clave);
        $datos = is_file($ruta) ? json_decode((string) @file_get_contents($ruta), true) : [];
        $limite = time() - self::VENTANA;
        return array_values(array_filter(is_array($datos) ? $datos : [], fn($t) => (int) $t > $limite));
    }

    /** Claves que se revisan para una solicitud de login. */
    public static function claves(string $email): array
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'sin-ip';
        return ['ip:' . $ip, 'correo:' . mb_strtolower(trim($email))];
    }

    /** Segundos de espera si alguna clave esta bloqueada; 0 si puede intentar. */
    public static function espera(array $claves): int
    {
        $espera = 0;
        foreach ($claves as $clave) {
            $intentos = self::intentos($clave);
            if (count($intentos) >= self::MAX_INTENTOS) {
                $espera = max($espera, min($intentos) + self::VENTANA - time());
            }
        }
        return max(0, $espera);
    }

    public static function fallo(array $claves): void
    {
        foreach ($claves as $clave) {
            $intentos = self::intentos($clave);
            $intentos[] = time();
            @file_put_contents(self::archivo($clave), json_encode($intentos), LOCK_EX);
        }
    }

    public static function limpiar(array $claves): void
    {
        foreach ($claves as $clave) {
            @unlink(self::archivo($clave));
        }
    }
}
