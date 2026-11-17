<?php

namespace Model;

class Distribuidor extends Modelo
{
    protected static string $archivo = 'distribuidores.php';

    public string $municipio = '';
    public string $nombre = '';
    public ?string $sucursal = null;
    public string $direccion = '';
    public ?string $imagen = null;
    public ?string $maps = null;

    /** @return array<string, Distribuidor[]> agrupados por municipio, en el orden del archivo */
    public static function porMunicipio(): array
    {
        $grupos = [];
        foreach (static::all() as $distribuidor) {
            $grupos[$distribuidor->municipio][] = $distribuidor;
        }
        return $grupos;
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
