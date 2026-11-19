<?php

namespace Model;

/**
 * Galeria de entregas de la pagina de inicio.
 *
 * Las ultimas fotos de entrega (tabla entrega_fotos de cotizaciones) de las
 * ventas vigentes de la empresa del sitio (EMPRESA_ID). Solo se leen id y
 * fecha: la imagen se pide firmada a cotizaciones en modo "galeria", que la
 * devuelve con el logo y sin la fecha ni la ubicacion del cliente.
 *
 * Si la BD no responde o no hay fotos, la lista queda vacia y la seccion no
 * se muestra.
 */
final class Galeria extends ModeloDB
{
    public const LIMITE = 10;

    /** @var array<int, array{id: int, fecha: string}>|null */
    private static ?array $fotos = null;

    /** @return array<int, array{id: int, fecha: string}> de la mas reciente a la mas antigua */
    public static function fotos(): array
    {
        if (self::$fotos !== null) {
            return self::$fotos;
        }

        try {
            $filas = static::filas(
                "SELECT f.id, e.fecha
                   FROM entrega_fotos f
                   JOIN entregas e ON e.id = f.entrega_id
                   JOIN ventas v   ON v.id = e.venta_id
                  WHERE v.empresa_id = ? AND v.estado = 'registrada'
                  ORDER BY e.fecha DESC, f.id DESC
                  LIMIT " . self::LIMITE,
                'i',
                [empresaId()]
            );
        } catch (\mysqli_sql_exception $e) {
            error_log('[Galeria] ' . $e->getMessage());
            $filas = [];
        }

        return self::$fotos = array_map(
            static fn (array $f): array => ['id' => (int) $f['id'], 'fecha' => (string) $f['fecha']],
            $filas
        );
    }

    /** Solo las fotos que hoy estan en la galeria se pueden pedir sin sesion. */
    public static function fotoPermitida(int $id): bool
    {
        return in_array($id, array_column(self::fotos(), 'id'), true);
    }

    /** "noviembre de 2026" */
    public static function mes(string $fecha): string
    {
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
            'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $tiempo = strtotime($fecha);
        return $tiempo ? $meses[(int) date('n', $tiempo) - 1] . ' de ' . date('Y', $tiempo) : '';
    }
}
