<?php

namespace Model;

/**
 * Producto que el cliente puede pedir: activo y de la empresa (EMPRESA_ID),
 * ya sea como negocio principal o vinculado en `producto_empresa`.
 * Precio = precio de lista (`productos.precio`).
 */
class ProductoVenta extends ModeloDB
{
    public int $id = 0;
    public string $nombre = '';
    public ?string $sku = null;
    public ?string $categoria = null;
    public string $unidad = 'pza';
    public float $precio = 0;

    public function __construct(array $fila = [])
    {
        $this->id = (int) ($fila['id'] ?? 0);
        $this->nombre = (string) ($fila['nombre'] ?? '');
        $this->sku = $fila['sku'] ?? null;
        $this->categoria = $fila['categoria'] ?? null;
        $this->unidad = (string) ($fila['unidad'] ?? 'pza');
        $this->precio = (float) ($fila['precio'] ?? 0);
    }

    private const SQL = 'SELECT p.id, p.nombre, p.sku, p.categoria, p.unidad, p.precio
                           FROM productos p
                          WHERE p.activo = 1
                            AND (p.empresa_id = ? OR EXISTS (
                                 SELECT 1 FROM producto_empresa pe WHERE pe.producto_id = p.id AND pe.empresa_id = ?))';

    /** @return ProductoVenta[] indexados por id */
    public static function disponibles(): array
    {
        $empresa = empresaId();
        $productos = [];
        foreach (static::filas(self::SQL . ' ORDER BY p.categoria, p.sku, p.nombre', 'ii', [$empresa, $empresa]) as $fila) {
            $productos[(int) $fila['id']] = new self($fila);
        }
        return $productos;
    }

    /** @return array<string, ProductoVenta[]> agrupados por categoria */
    public static function porCategoria(): array
    {
        $grupos = [];
        foreach (static::disponibles() as $producto) {
            $grupos[$producto->categoria ?: 'Otros'][] = $producto;
        }
        return $grupos;
    }

    /** Id del producto pedible con ese SKU (para el boton de la ficha tecnica). */
    public static function idPorSku(string $sku): ?int
    {
        $empresa = empresaId();
        $fila = static::fila(self::SQL . ' AND p.sku = ? LIMIT 1', 'iis', [$empresa, $empresa, $sku]);
        return $fila ? (int) $fila['id'] : null;
    }

    /** Imagen local (data/imagenes.php) por SKU. */
    public function imagen(): ?string
    {
        static $imagenes = null;
        $imagenes ??= require dirname(__DIR__) . '/data/imagenes.php';
        return $imagenes[$this->sku ?? '']['principal'] ?? null;
    }
}
