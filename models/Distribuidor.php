<?php

namespace Model;

/**
 * Distribuidor autorizado (tabla distribuidores de cotizaciones, migracion 056).
 *
 * Se administran en Acero 360 → Distribuidores. Aqui solo se leen los de la
 * empresa del sitio (EMPRESA_ID) marcados como publicados, en su orden. Una
 * sola consulta por peticion; si la BD no responde la lista queda vacia y
 * disponible() devuelve false (la pagina lo avisa en lugar de romperse).
 */
final class Distribuidor extends ModeloDB
{
    /** Fachadas heredadas: rutas dentro de public/build/img del sitio. */
    private const PREFIJO_HEREDADA = 'distribuidores/';

    public int $id = 0;
    public string $municipio = '';
    public string $nombre = '';
    public ?string $sucursal = null;
    public string $direccion = '';
    public ?string $telefono = null;
    public ?string $imagen = null;
    public ?string $maps = null;

    /** @var Distribuidor[]|null */
    private static ?array $lista = null;

    private static bool $disponible = true;

    public function __construct(array $fila = [])
    {
        $this->id = (int) ($fila['id'] ?? 0);
        $this->municipio = (string) ($fila['municipio'] ?? '');
        $this->nombre = (string) ($fila['nombre'] ?? '');
        $this->sucursal = ($fila['sucursal'] ?? '') ?: null;
        $this->direccion = (string) ($fila['direccion'] ?? '');
        $this->telefono = ($fila['telefono'] ?? '') ?: null;
        $this->imagen = ($fila['imagen'] ?? '') ?: null;
        $this->maps = ($fila['maps'] ?? '') ?: null;
    }

    /** @return Distribuidor[] publicados, en el orden definido en cotizaciones */
    public static function all(): array
    {
        if (self::$lista !== null) {
            return self::$lista;
        }

        self::$lista = [];
        try {
            $filas = static::filas(
                'SELECT id, municipio, nombre, sucursal, direccion, telefono, imagen, maps
                   FROM distribuidores
                  WHERE empresa_id = ? AND publicado = 1
                  ORDER BY orden, id',
                'i',
                [empresaId()]
            );
        } catch (\mysqli_sql_exception $e) {
            error_log('[Distribuidor] ' . $e->getMessage());
            self::$disponible = false;
            return self::$lista;
        }

        self::$lista = array_map(fn(array $fila) => new self($fila), $filas);
        return self::$lista;
    }

    public static function disponible(): bool
    {
        self::all();
        return self::$disponible;
    }

    /** @return array<string, Distribuidor[]> agrupados por municipio, en el orden de la lista */
    public static function porMunicipio(): array
    {
        $grupos = [];
        foreach (static::all() as $distribuidor) {
            $grupos[$distribuidor->municipio][] = $distribuidor;
        }
        return $grupos;
    }

    /**
     * URL de la fachada: las heredadas salen de la carpeta del sitio y las
     * subidas desde Acero 360 pasan por el proxy de imagenes.
     */
    public function imagenUrl(): ?string
    {
        if (!$this->imagen) {
            return null;
        }
        if (str_starts_with($this->imagen, self::PREFIJO_HEREDADA) && !str_contains($this->imagen, '..')) {
            return img($this->imagen);
        }
        return imagenCatalogo($this->imagen);
    }

    /** Enlace tel: del telefono (solo digitos y +). */
    public function telefonoEnlace(): ?string
    {
        $digitos = preg_replace('/[^0-9+]/', '', (string) $this->telefono);
        return $digitos !== '' ? 'tel:' . $digitos : null;
    }

    /** ¿El proxy de imagenes puede servir esta fachada? Solo de distribuidores publicados de la empresa. */
    public static function imagenPermitida(string $archivo): bool
    {
        return static::fila(
            'SELECT 1 FROM distribuidores WHERE empresa_id = ? AND publicado = 1 AND imagen = ? LIMIT 1',
            'is',
            [empresaId(), $archivo]
        ) !== null;
    }

    public function municipioSlug(): string
    {
        return self::slugify($this->municipio);
    }

    public static function slugify(string $texto): string
    {
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($texto)), '-');
    }
}
