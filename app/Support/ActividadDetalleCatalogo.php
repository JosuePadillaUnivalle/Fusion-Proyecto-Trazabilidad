<?php

namespace App\Support;

final class ActividadDetalleCatalogo
{
    /** @var array<string, string> slug insumo => palabras clave en nombre tipo actividad */
    private const TIPO_ACTIVIDAD_A_INSUMO = [
        'fertilizantes' => 'fertiliz',
        'pesticidas' => 'plaga',
        'material_siembra' => 'siembra',
    ];

    /** @var array<int, array{key: string, label: string, descripcion: string}> */
    public const TIPOS_RIEGO = [
        [
            'key' => 'goteo',
            'label' => 'Riego por goteo',
            'descripcion' => 'Gota a gota en la raíz. Ahorra agua y controla humedad con precisión.',
        ],
        [
            'key' => 'aspersion',
            'label' => 'Riego por aspersión',
            'descripcion' => 'Simula lluvia sobre el cultivo. Cubre áreas amplias de forma uniforme.',
        ],
        [
            'key' => 'surco',
            'label' => 'Riego por surco / gravedad',
            'descripcion' => 'El agua avanza por canales entre hileras. Útil en terrenos con pendiente suave.',
        ],
        [
            'key' => 'pivote',
            'label' => 'Pivote central',
            'descripcion' => 'Aspersión giratoria para parcelas grandes y cultivos extensivos.',
        ],
    ];

    public static function slugInsumoParaTipoActividad(?string $tipoNombre): ?string
    {
        if ($tipoNombre === null || trim($tipoNombre) === '') {
            return null;
        }

        $nombre = mb_strtolower(trim($tipoNombre));

        foreach (self::TIPO_ACTIVIDAD_A_INSUMO as $slug => $fragmento) {
            if (str_contains($nombre, $fragmento)) {
                return $slug;
            }
        }

        return null;
    }

    public static function requiereInsumos(?string $tipoNombre): bool
    {
        return self::slugInsumoParaTipoActividad($tipoNombre) !== null;
    }

    public static function esRiego(?string $tipoNombre): bool
    {
        $nombre = mb_strtolower(trim((string) $tipoNombre));

        return str_contains($nombre, 'riego') || str_contains($nombre, 'regad');
    }

    /** Riego, fertilización, control de plagas o siembra exigen detalle en el modal. */
    public static function requiereDetalleOperativo(?string $tipoNombre): bool
    {
        return self::requiereInsumos($tipoNombre) || self::esRiego($tipoNombre);
    }

    public static function maxInsumosPorTipo(?string $tipoNombre): int
    {
        $slug = self::slugInsumoParaTipoActividad($tipoNombre);

        return $slug === 'material_siembra' ? 1 : 10;
    }

    /**
     * @param  array<string, mixed>  $detalle
     */
    public static function textoResumenDesdeDetalle(?string $tipoNombre, ?array $detalle): ?string
    {
        if ($detalle === null || $detalle === []) {
            return null;
        }

        $modo = (string) ($detalle['modo'] ?? '');

        if ($modo === 'riego') {
            $riego = $detalle['riego'] ?? [];
            $label = trim((string) ($riego['label'] ?? ''));

            return $label !== '' ? $label : null;
        }

        if ($modo === 'insumos') {
            $partes = [];
            foreach ($detalle['insumos'] ?? [] as $fila) {
                $nombre = trim((string) ($fila['nombre'] ?? ''));
                $cant = (float) ($fila['cantidad'] ?? 0);
                $unidad = trim((string) ($fila['unidad'] ?? 'ud'));
                if ($nombre !== '' && $cant > 0) {
                    $partes[] = $nombre.' ('.number_format($cant, 2, '.', '').' '.$unidad.')';
                }
            }

            if ($partes === []) {
                return null;
            }

            $tipo = ucfirst(trim((string) $tipoNombre));

            return $tipo.' — '.implode(', ', $partes);
        }

        return null;
    }
}
