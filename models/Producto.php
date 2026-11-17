<?php

namespace Model;

/**
 * Producto del catalogo publico (tabla productos de cotizaciones, columnas
 * publicas). La ficha tecnica sale de `descripcion`. Ver Catalogo.
 */
class Producto
{
    public int $id = 0;
    public string $nombre = '';
    public string $sku = '';
    public string $categoria = '';
    public string $descripcion = '';
    public ?string $imagen = null;
    public ?string $imagen_uso = null;

    public function __construct(array $fila = [])
    {
        $this->id = (int) ($fila['id'] ?? 0);
        $this->nombre = (string) ($fila['nombre'] ?? '');
        $this->sku = (string) ($fila['sku'] ?? '');
        $this->categoria = (string) ($fila['categoria'] ?? '');
        $this->descripcion = (string) ($fila['descripcion'] ?? '');
        $this->imagen = ($fila['imagen'] ?? '') ?: null;
        $this->imagen_uso = ($fila['imagen_uso'] ?? '') ?: null;
    }

    /** @return Producto[] */
    public static function all(): array
    {
        return Catalogo::productos();
    }

    /** Identificador apto para atributos HTML (RA-1 -> ra-1). */
    public function slug(): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $this->sku));
    }

    /** Primera linea de la descripcion: "Registro compacto de ángulo". */
    public function subtitulo(): string
    {
        return rtrim($this->lineas()[0] ?? '', '.');
    }

    /** Medida principal, p. ej. "40x60 cm". */
    public function dimension(): ?string
    {
        return $this->especificaciones()['Dimensión'] ?? null;
    }

    /** Texto de "Tipo de uso" sin el prefijo. */
    public function uso(): ?string
    {
        foreach ($this->lineas() as $linea) {
            if (preg_match('/^Tipo de uso:\s*(.+)$/u', $linea, $m)) {
                return self::capitalizar(rtrim($m[1], '.'));
            }
        }
        return null;
    }

    /**
     * Convierte la descripcion de la BD en pares clave => valor.
     * "Dimensión: 40x40 cm. Marco: 1 1/2\"." => ['Dimensión' => '40x40 cm', 'Marco' => '1 1/2"']
     * Las frases sin "clave:" se agregan en 'Notas'.
     */
    public function especificaciones(): array
    {
        $specs = [];
        $notas = [];

        foreach (array_slice($this->lineas(), 1) as $linea) {
            if (str_starts_with($linea, 'Tipo de uso:')) {
                continue;
            }
            // Separa frases por ". " seguido de mayuscula o por "; "
            $frases = preg_split('/(?<=\.)\s+(?=\p{Lu})|;\s+/u', $linea);
            foreach ($frases as $frase) {
                $frase = rtrim(trim($frase), '.');
                if ($frase === '') {
                    continue;
                }
                if (preg_match('/^([^:]{2,40}):\s*(.+)$/u', $frase, $m)) {
                    [$clave, $valor] = [trim($m[1]), trim($m[2])];
                    // Valor compuesto: "13.3 cm, cortos: 15 cm" -> clave "… / cortos", valor "13.3 cm / 15 cm"
                    if (preg_match('/^(.+?),\s*([^,:]{2,20}):\s*(.+)$/u', $valor, $c)) {
                        $clave .= ' / ' . trim($c[2]);
                        $valor = trim($c[1]) . ' / ' . trim($c[3]);
                    }
                    $specs[self::capitalizar($clave)] = self::capitalizar($valor);
                } else {
                    $notas[] = self::capitalizar($frase);
                }
            }
        }

        if ($notas) {
            $specs['Notas'] = implode('. ', $notas);
        }

        return $specs;
    }

    /** URL de la imagen principal (proxy /img/catalogo) o null. */
    public function imagenPrincipal(): ?string
    {
        return imagenCatalogo($this->imagen);
    }

    /** URL de la imagen de uso (producto instalado) o null. */
    public function imagenUso(): ?string
    {
        return imagenCatalogo($this->imagen_uso);
    }

    public function mensajeCotizacion(): string
    {
        return "Hola, me interesa cotizar el producto {$this->sku} - {$this->nombre}.";
    }

    private function lineas(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $this->descripcion))));
    }

    private static function capitalizar(string $texto): string
    {
        return mb_strtoupper(mb_substr($texto, 0, 1)) . mb_substr($texto, 1);
    }
}
