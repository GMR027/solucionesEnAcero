<?php

namespace Model;

/**
 * Pedidos del portal (tablas `pedidos` y `pedido_detalles` de cotizaciones).
 *
 * Contrato (cotizaciones/GUIA.md, "Pedidos y portal de clientes"):
 *  - origen = 'portal', usuario_id = NULL, estado = 'pendiente'
 *  - folio PED-<año>-<NNNN> (maximo del año + 1, reintentar si choca)
 *  - precio_unitario = precio de lista; importes como Cotizaciones::calcular
 *
 * Toda consulta de un pedido filtra por cliente_id Y empresa_id.
 */
class Pedido extends ModeloDB
{
    public const IVA = 16.0;
    public const MAX_CANTIDAD = 9999;
    public const MAX_RENGLONES = 60;
    public const POR_PAGINA = 10;

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'cotizado' => 'Cotizado',
        'vendido' => 'Vendido',
        'cancelado' => 'Cancelado',
    ];

    public const FILTROS = [
        'todos' => 'Todos',
        'entregados' => 'Entregados',
        'en-proceso' => 'En proceso',
        'cancelados' => 'Cancelados',
    ];

    public int $id = 0;
    public string $folio = '';
    public int $cliente_id = 0;
    public string $origen = 'portal';
    public string $fecha = '';
    public string $estado = 'pendiente';
    public float $subtotal = 0;
    public float $descuento = 0;
    public float $iva = 0;
    public float $total = 0;
    public bool $aplica_iva = true;
    public ?string $comentarios = null;
    public ?string $condiciones_envio = null;
    public ?string $condiciones_compra = null;
    public ?int $venta_id = null;
    public ?string $cancelado_en = null;
    public ?string $creado_en = null;
    public ?string $entrega_estado = null;
    public ?string $entrega_fecha = null;
    public int $renglones = 0;

    /** @var string[] */
    private static array $errores = [];

    public function __construct(array $fila = [])
    {
        foreach ($fila as $clave => $valor) {
            if (!property_exists($this, $clave) || $valor === null) {
                continue;
            }
            $this->$clave = match (gettype($this->$clave)) {
                'integer' => (int) $valor,
                'double' => (float) $valor,
                'boolean' => (bool) $valor,
                default => $valor,
            };
        }
        $this->venta_id = isset($fila['venta_id']) ? (int) $fila['venta_id'] : null;
    }

    public static function errores(): array
    {
        return self::$errores;
    }

    /* ------------------------------------------------------------------
     * Importes: copia de Cotizaciones::calcular (cotizaciones/clases/Cotizaciones.php)
     * ---------------------------------------------------------------- */
    public static function calcular(array $lineas, float $descuentoGlobalPct = 0.0, bool $aplicaIva = true): array
    {
        $subtotal = 0.0;
        $descuentoLineas = 0.0;
        $renglones = 0;

        foreach ($lineas as $linea) {
            $cantidad = max(1, (int) ($linea['cantidad'] ?? 1));
            $precio = max(0, (float) ($linea['precio'] ?? 0));
            $pctRenglon = min(100, max(0, (float) ($linea['descuento_pct'] ?? 0)));

            $bruto = $cantidad * $precio;
            $subtotal += $bruto;
            $descuentoLineas += $bruto * $pctRenglon / 100;
            $renglones++;
        }

        $subtotal = round($subtotal, 2);
        $descuentoLineas = round($descuentoLineas, 2);
        $descuentoGlobalPct = min(100, max(0, $descuentoGlobalPct));

        $baseTrasDescuento = round($subtotal - $descuentoLineas, 2);
        $descuentoGlobal = round($baseTrasDescuento * $descuentoGlobalPct / 100, 2);

        $descuento = round($descuentoLineas + $descuentoGlobal, 2);
        $base = round($subtotal - $descuento, 2);
        $iva = $aplicaIva ? round($base * self::IVA / 100, 2) : 0.0;

        return [
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'base' => $base,
            'iva' => $iva,
            'total' => round($base + $iva, 2),
            'renglones' => $renglones,
        ];
    }

    /**
     * Normaliza lo que manda el formulario: [producto_id => cantidad].
     * Solo productos disponibles; el precio y la existencia SIEMPRE salen de la BD.
     *
     * @return array<int, array{cantidad: int, precio: float, disponible: int, producto: ProductoVenta}>
     */
    public static function lineasDesdeFormulario(array $cantidades, array $disponibles): array
    {
        $lineas = [];
        foreach ($cantidades as $productoId => $cantidad) {
            $productoId = (int) $productoId;
            $cantidad = (int) $cantidad;
            if ($cantidad < 1 || !isset($disponibles[$productoId])) {
                continue;
            }
            $lineas[$productoId] = [
                'cantidad' => min(self::MAX_CANTIDAD, $cantidad),
                'precio' => $disponibles[$productoId]->precio,
                'descuento_pct' => 0,
                'disponible' => $disponibles[$productoId]->disponible(),
                'producto' => $disponibles[$productoId],
            ];
        }
        return $lineas;
    }

    /** Crea el pedido. Devuelve el id o null (ver errores()). */
    public static function crear(Cliente $cliente, array $lineas, string $comentarios): ?int
    {
        self::$errores = [];

        if (!$lineas) {
            self::$errores[] = 'Agrega al menos un producto a tu pedido.';
        }
        if (count($lineas) > self::MAX_RENGLONES) {
            self::$errores[] = 'El pedido tiene demasiados productos.';
        }
        foreach ($lineas as $linea) {
            $cantidad = (int) $linea['cantidad'];
            $disponible = max(0, (int) ($linea['disponible'] ?? 0));
            $nombre = (string) ($linea['producto']->nombre ?? 'el producto');
            $unidad = (string) ($linea['producto']->unidad ?? 'pza');

            if ($disponible < 1) {
                self::$errores[] = "El producto «{$nombre}» no está disponible por el momento.";
            } elseif ($cantidad > $disponible) {
                self::$errores[] = "Solo puedes comprar {$disponible} {$unidad} de «{$nombre}»; pediste {$cantidad}.";
            }
        }
        $comentarios = trim(mb_substr($comentarios, 0, 1000));
        if (self::$errores) {
            return null;
        }

        $totales = self::calcular($lineas);
        $empresa = empresaId();
        $condiciones = static::fila(
            'SELECT condiciones_envio, condiciones_compra FROM empresas WHERE id = ?',
            'i',
            [$empresa]
        ) ?? [];
        $envio = self::texto($condiciones['condiciones_envio'] ?? null);
        $compra = self::texto($condiciones['condiciones_compra'] ?? null);
        $comentarios = self::texto($comentarios);
        $db = static::db();

        for ($intento = 0; $intento < 3; $intento++) {
            try {
                $db->begin_transaction();
                $folio = self::siguienteFolio();

                static::ejecutar(
                    "INSERT INTO pedidos (folio, empresa_id, cliente_id, usuario_id, origen, fecha, estado,
                                          subtotal, descuento_pct, descuento, aplica_iva, iva, total,
                                          condiciones_envio, condiciones_compra, comentarios)
                     VALUES (?, ?, ?, NULL, 'portal', CURDATE(), 'pendiente', ?, 0, ?, 1, ?, ?, ?, ?, ?)",
                    'siiddddsss',
                    [$folio, $empresa, $cliente->id, $totales['subtotal'], $totales['descuento'],
                     $totales['iva'], $totales['total'], $envio, $compra, $comentarios]
                );
                $pedidoId = (int) $db->insert_id;

                foreach ($lineas as $productoId => $linea) {
                    $subtotal = round($linea['cantidad'] * $linea['precio'], 2);
                    static::ejecutar(
                        'INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, descuento_pct, subtotal)
                         VALUES (?, ?, ?, ?, 0, ?)',
                        'iiidd',
                        [$pedidoId, (int) $productoId, $linea['cantidad'], $linea['precio'], $subtotal]
                    );
                }

                $db->commit();
                return $pedidoId;
            } catch (\mysqli_sql_exception $e) {
                $db->rollback();
                if (str_contains($e->getMessage(), 'Duplicate') && str_contains($e->getMessage(), 'folio')) {
                    continue; // folio tomado en paralelo: se intenta con el siguiente
                }
                error_log('[Pedido::crear] ' . $e->getMessage());
                break;
            }
        }

        self::$errores[] = 'No fue posible guardar el pedido. Inténtalo de nuevo.';
        return null;
    }

    private static function siguienteFolio(): string
    {
        $prefijo = 'PED-' . date('Y') . '-';
        $fila = static::fila(
            'SELECT MAX(CAST(SUBSTRING(folio, ?) AS UNSIGNED)) AS maximo FROM pedidos WHERE folio LIKE ?',
            'is',
            [strlen($prefijo) + 1, $prefijo . '%']
        );
        return $prefijo . str_pad((string) (((int) ($fila['maximo'] ?? 0)) + 1), 4, '0', STR_PAD_LEFT);
    }

    /* ------------------------------------------------------------------
     * Consultas del cliente
     * ---------------------------------------------------------------- */

    /**
     * Pedidos del cliente con el filtro de las pestañas de «Mis pedidos».
     *
     * @return array{pedidos: Pedido[], total: int, paginas: int, pagina: int, filtro: string, conteos: array<string, int>}
     */
    public static function delCliente(int $clienteId, int $pagina = 1, string $filtro = 'todos'): array
    {
        $empresa = empresaId();
        $filtro = isset(self::FILTROS[$filtro]) ? $filtro : 'todos';

        // Los conteos de las cuatro pestañas salen de una sola consulta.
        $conteosFila = static::fila(
            "SELECT COUNT(*) AS todos,
                    COALESCE(SUM(p.estado = 'cancelado'), 0) AS cancelados,
                    COALESCE(SUM(p.estado <> 'cancelado'
                                 AND v.entrega_estado LIKE 'entregado%'), 0) AS entregados,
                    COALESCE(SUM(p.estado IN ('pendiente', 'cotizado', 'vendido')
                                 AND (v.entrega_estado IS NULL OR v.entrega_estado NOT LIKE 'entregado%')), 0) AS en_proceso
               FROM pedidos p
               LEFT JOIN ventas v ON v.id = p.venta_id
              WHERE p.cliente_id = ? AND p.empresa_id = ?",
            'ii',
            [$clienteId, $empresa]
        ) ?? [];

        $conteos = [
            'todos' => (int) ($conteosFila['todos'] ?? 0),
            'entregados' => (int) ($conteosFila['entregados'] ?? 0),
            'en-proceso' => (int) ($conteosFila['en_proceso'] ?? 0),
            'cancelados' => (int) ($conteosFila['cancelados'] ?? 0),
        ];

        $condicion = match ($filtro) {
            'entregados' => "p.estado <> 'cancelado' AND v.entrega_estado LIKE 'entregado%'",
            'en-proceso' => "p.estado IN ('pendiente', 'cotizado', 'vendido')
                             AND (v.entrega_estado IS NULL OR v.entrega_estado NOT LIKE 'entregado%')",
            'cancelados' => "p.estado = 'cancelado'",
            default => '1 = 1',
        };

        $total = $conteos[$filtro];
        $paginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $pagina = min(max(1, $pagina), $paginas);
        $desde = ($pagina - 1) * self::POR_PAGINA;
        $limite = self::POR_PAGINA;

        $filas = static::filas(
            "SELECT p.*, v.entrega_estado, v.entrega_fecha,
                    (SELECT COUNT(*) FROM pedido_detalles d WHERE d.pedido_id = p.id) AS renglones
               FROM pedidos p
               LEFT JOIN ventas v ON v.id = p.venta_id
              WHERE p.cliente_id = ? AND p.empresa_id = ? AND {$condicion}
              ORDER BY p.creado_en DESC, p.id DESC
              LIMIT ? OFFSET ?",
            'iiii',
            [$clienteId, $empresa, $limite, $desde]
        );

        return [
            'pedidos' => array_map(fn(array $f) => new self($f), $filas),
            'total' => $total,
            'paginas' => $paginas,
            'pagina' => $pagina,
            'filtro' => $filtro,
            'conteos' => $conteos,
        ];
    }

    public static function buscar(int $id, int $clienteId): ?self
    {
        $fila = static::fila(
            'SELECT p.*, v.entrega_estado, v.entrega_fecha
               FROM pedidos p
               LEFT JOIN ventas v ON v.id = p.venta_id
              WHERE p.id = ? AND p.cliente_id = ? AND p.empresa_id = ?',
            'iii',
            [$id, $clienteId, empresaId()]
        );
        return $fila ? new self($fila) : null;
    }

    /** @return array<int, array<string, mixed>> renglones con nombre y SKU */
    public function detalles(): array
    {
        return static::filas(
            'SELECT d.producto_id, d.cantidad, d.precio_unitario, d.descuento_pct, d.subtotal,
                    pr.nombre, pr.sku, pr.unidad, pr.imagen
               FROM pedido_detalles d
               JOIN productos pr ON pr.id = d.producto_id
              WHERE d.pedido_id = ?
              ORDER BY d.id',
            'i',
            [$this->id]
        );
    }

    /** [producto_id => cantidad] para repetir el pedido. */
    public function cantidades(): array
    {
        $cantidades = [];
        foreach ($this->detalles() as $d) {
            $cantidades[(int) $d['producto_id']] = (int) $d['cantidad'];
        }
        return $cantidades;
    }

    public function cancelable(): bool
    {
        return $this->estado === 'pendiente';
    }

    /** Solo mientras siga pendiente (el administrador aun no lo cotiza). */
    public static function cancelar(int $id, int $clienteId): bool
    {
        return static::ejecutar(
            "UPDATE pedidos SET estado = 'cancelado', cancelado_en = NOW()
              WHERE id = ? AND cliente_id = ? AND empresa_id = ? AND estado = 'pendiente'",
            'iii',
            [$id, $clienteId, empresaId()]
        ) === 1;
    }

    /** Venta ligada (folio y estado de entrega) o null. */
    public function venta(): ?array
    {
        if (!$this->venta_id) {
            return null;
        }
        return static::fila(
            'SELECT id, folio, estado, entrega_estado, entrega_fecha FROM ventas WHERE id = ? AND cliente_id = ?',
            'ii',
            [$this->venta_id, $this->cliente_id]
        );
    }

    /** @return array<int, array{id: int, tomada_en: ?string, fecha: ?string}> */
    public function fotos(): array
    {
        if (!$this->venta_id) {
            return [];
        }
        return static::filas(
            'SELECT f.id, f.tomada_en, e.fecha
               FROM entrega_fotos f
               JOIN entregas e ON e.id = f.entrega_id
              WHERE e.venta_id = ?
              ORDER BY e.fecha, f.id',
            'i',
            [$this->venta_id]
        );
    }

    /** ¿La foto pertenece a un pedido de este cliente? */
    public static function fotoDelCliente(int $fotoId, int $clienteId): bool
    {
        return static::fila(
            'SELECT f.id
               FROM entrega_fotos f
               JOIN entregas e ON e.id = f.entrega_id
               JOIN pedidos p  ON p.venta_id = e.venta_id
              WHERE f.id = ? AND p.cliente_id = ? AND p.empresa_id = ?
              LIMIT 1',
            'iii',
            [$fotoId, $clienteId, empresaId()]
        ) !== null;
    }

    public function entregado(): bool
    {
        return str_starts_with((string) $this->entrega_estado, 'entregado');
    }

    public function etiquetaEstado(): string
    {
        if ($this->entregado()) {
            return 'Entregado';
        }
        return self::ESTADOS[$this->estado] ?? ucfirst($this->estado);
    }

    private static function texto(?string $valor): ?string
    {
        $valor = trim((string) $valor);
        return $valor === '' ? null : $valor;
    }
}
