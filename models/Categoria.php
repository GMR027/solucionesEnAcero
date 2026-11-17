<?php

namespace Model;

class Categoria extends Modelo
{
    protected static string $archivo = 'categorias.php';

    public string $slug = '';
    public string $nombre = '';
    public string $resumen = '';
    public string $portada = '';

    public function url(): string
    {
        return '/productos/' . $this->slug;
    }

    /** @return Producto[] */
    public function productos(): array
    {
        return Producto::porCategoria($this->nombre);
    }

    public function totalProductos(): int
    {
        return count($this->productos());
    }
}
