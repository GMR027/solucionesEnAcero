<?php

namespace Model;

/**
 * Base de los modelos del catalogo.
 *
 * Por ahora lee arreglos de data/*.php. Cuando se conecte la BD de cotizaciones,
 * `registros()` se sustituira por consultas preparadas sin cambiar la API publica
 * de los modelos (all, porCategoria, buscar...).
 */
abstract class Modelo
{
    protected static string $archivo = '';

    /** @var array<string, array> */
    private static array $cache = [];

    public function __construct(array $atributos = [])
    {
        foreach ($atributos as $clave => $valor) {
            if (property_exists($this, $clave)) {
                $this->$clave = $valor;
            }
        }
    }

    protected static function registros(): array
    {
        return self::$cache[static::class] ??= require dirname(__DIR__) . '/data/' . static::$archivo;
    }

    /** @return static[] */
    public static function all(): array
    {
        return array_map(fn(array $fila) => new static($fila), static::registros());
    }

    /** @return static|null */
    public static function where(string $columna, mixed $valor): ?static
    {
        foreach (static::registros() as $fila) {
            if (($fila[$columna] ?? null) === $valor) {
                return new static($fila);
            }
        }
        return null;
    }
}
