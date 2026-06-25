<?php

namespace App\Support;

use App\Models\EstadoLoteTipo;
use Illuminate\Support\Collection;

class EstadoLoteCatalogo
{
    /** @var array<string, array{label: string, descripcion: string, orden: int}> */
    public const ESTADOS = [
        'planificado' => [
            'label' => 'Planificación',
            'descripcion' => 'El lote fue creado; aún no inicia el ciclo productivo en campo.',
            'orden' => 1,
        ],
        'sembrado' => [
            'label' => 'Sembrado',
            'descripcion' => 'La siembra ya fue realizada.',
            'orden' => 2,
        ],
        'en_crecimiento' => [
            'label' => 'En crecimiento',
            'descripcion' => 'El cultivo se desarrolla con actividades de manejo en campo.',
            'orden' => 3,
        ],
        'listo_para_cosecha' => [
            'label' => 'Listo para cosecha',
            'descripcion' => 'Se completó al menos una actividad de cada tipo requerida; listo para cosechar.',
            'orden' => 4,
        ],
        'cosechado' => [
            'label' => 'Cosechado',
            'descripcion' => 'La producción fue recolectada; pendiente de certificación.',
            'orden' => 5,
        ],
        'certificado' => [
            'label' => 'Certificado',
            'descripcion' => 'El lote fue certificado en campo después de la cosecha.',
            'orden' => 6,
        ],
        'finalizado' => [
            'label' => 'Finalizado',
            'descripcion' => 'El producto ya fue enviado al almacén; ciclo cerrado.',
            'orden' => 7,
        ],
    ];

    /** @var array<string, string> legacy nombre (slug) → slug canónico */
    private const LEGACY_MAP = [
        'disponible' => 'planificado',
        'en preparación' => 'planificado',
        'en preparacion' => 'planificado',
        'planificado' => 'planificado',
        'planificación' => 'planificado',
        'planificacion' => 'planificado',
        'sembrado' => 'sembrado',
        'en producción' => 'en_crecimiento',
        'en produccion' => 'en_crecimiento',
        'en certificación' => 'en_crecimiento',
        'en certificacion' => 'en_crecimiento',
        'certificado' => 'certificado',
        'listo para cosecha' => 'listo_para_cosecha',
        'cosechado' => 'cosechado',
        'en descanso' => 'finalizado',
        'archivado' => 'finalizado',
        'suspendido' => 'finalizado',
        'finalizado' => 'finalizado',
    ];

    public static function slugFromNombre(?string $nombre): ?string
    {
        if ($nombre === null || trim($nombre) === '') {
            return null;
        }

        $key = mb_strtolower(trim($nombre));

        if (isset(self::LEGACY_MAP[$key])) {
            return self::LEGACY_MAP[$key];
        }

        foreach (self::ESTADOS as $slug => $meta) {
            if (mb_strtolower($meta['label']) === $key) {
                return $slug;
            }
        }

        return null;
    }

    public static function mapLegacyNombre(?string $nombre): string
    {
        return self::slugFromNombre($nombre) ?? 'planificado';
    }

    public static function label(string $slug): string
    {
        return self::ESTADOS[$slug]['label'] ?? ucfirst(str_replace('_', ' ', $slug));
    }

    public static function descripcion(string $slug): string
    {
        return self::ESTADOS[$slug]['descripcion'] ?? '';
    }

    /** @return Collection<int, EstadoLoteTipo> */
    public static function paraSelect(): Collection
    {
        $porSlug = EstadoLoteTipo::all()->keyBy(
            fn (EstadoLoteTipo $e) => self::slugFromNombre($e->nombre) ?? 'zzz_'.$e->estadolotetipoid
        );

        return collect(self::ESTADOS)
            ->sortBy('orden')
            ->map(function (array $meta, string $slug) use ($porSlug) {
                return $porSlug->get($slug) ?? EstadoLoteTipo::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($meta['label'])])->first();
            })
            ->filter()
            ->values();
    }

    public static function idPorSlug(string $slug): ?int
    {
        $label = self::label($slug);

        $id = EstadoLoteTipo::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($label)])->value('estadolotetipoid');
        if ($id) {
            return (int) $id;
        }

        foreach (self::LEGACY_MAP as $nombre => $mapped) {
            if ($mapped !== $slug) {
                continue;
            }
            $legacyId = EstadoLoteTipo::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombre)])->value('estadolotetipoid');
            if ($legacyId) {
                return (int) $legacyId;
            }
        }

        $slugAsNombre = str_replace('_', ' ', $slug);

        return (int) (EstadoLoteTipo::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($slugAsNombre)])->value('estadolotetipoid') ?: 0) ?: null;
    }

    /** @return array<int> */
    public static function idsPorSlugs(array $slugs): array
    {
        return collect($slugs)
            ->map(fn (string $slug) => self::idPorSlug($slug))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** Lotes con ciclo de cosecha completado (KPI mapa / listados). */
    /** @return array<int> */
    public static function idsKpiCosechados(): array
    {
        return self::idsPorSlugs(['cosechado', 'certificado', 'finalizado']);
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Lote>  $query */
    public static function scopeKpiCosechados($query)
    {
        $ids = self::idsKpiCosechados();

        return $ids !== []
            ? $query->whereIn('estadolotetipoid', $ids)
            : $query->whereRaw('0 = 1');
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Lote>  $query */
    public static function scopeKpiEnProduccion($query)
    {
        $ids = self::idsPorSlugs(['en_crecimiento']);

        return $ids !== []
            ? $query->whereIn('estadolotetipoid', $ids)
            : $query->whereRaw('0 = 1');
    }

    public static function loteEnSlug(?string $nombreEstado, string $slugEsperado): bool
    {
        return self::slugFromNombre($nombreEstado) === $slugEsperado;
    }

    /** Lote que ya completó cosecha (y fases posteriores): no editable como parcela activa. */
    public static function loteEsCerrado(?string $nombreEstado): bool
    {
        if ($nombreEstado === null || trim($nombreEstado) === '') {
            return false;
        }

        $slug = self::slugFromNombre($nombreEstado);
        if (in_array($slug, ['cosechado', 'finalizado'], true)) {
            return true;
        }

        return in_array(mb_strtolower(trim($nombreEstado)), ['certificado', 'no conforme'], true);
    }

    /** Solo lotes en planificación (incluye legacy «Disponible») pueden eliminarse. */
    public static function loteSePuedeEliminar(?string $nombreEstado): bool
    {
        return self::slugFromNombre($nombreEstado) === 'planificado';
    }

    /** @return array<int> */
    public static function idsLoteSoloCosechado(): array
    {
        $id = self::idPorSlug('cosechado');

        return $id ? [$id] : [];
    }

    /** @return array<int> */
    public static function idsLotePostCosecha(): array
    {
        $porNombre = \App\Models\EstadoLoteTipo::query()
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(nombre))'), [
                'cosechado', 'finalizado', 'certificado', 'no conforme',
            ])
            ->pluck('estadolotetipoid')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge(
            self::idsPorSlugs(['cosechado', 'finalizado']),
            $porNombre
        )));
    }

    public static function filtrosPanelAbierto(\Illuminate\Http\Request $request, bool $tieneFiltrosActivos): bool
    {
        return $request->boolean('filtros_abiertos') || $tieneFiltrosActivos;
    }

    /** @return array<string, string> slug => color hex para marcadores del mapa */
    public static function coloresMapa(): array
    {
        return [
            'planificado' => '#6366f1',
            'sembrado' => '#0ea5e9',
            'en_crecimiento' => '#22c55e',
            'listo_para_cosecha' => '#14b8a6',
            'cosechado' => '#f59e0b',
            'certificado' => '#7c3aed',
            'finalizado' => '#475569',
        ];
    }

    public static function colorMapaPorSlug(?string $slug): string
    {
        if ($slug === null || $slug === '') {
            return '#6c757d';
        }

        return self::coloresMapa()[$slug] ?? '#6c757d';
    }

    /** @return list<array{slug: string, label: string, color: string}> */
    public static function leyendaMapa(): array
    {
        $slugsLeyenda = [
            'planificado',
            'en_crecimiento',
            'listo_para_cosecha',
            'cosechado',
            'certificado',
            'finalizado',
        ];

        return collect($slugsLeyenda)
            ->map(function (string $slug) {
                return [
                    'slug' => $slug,
                    'label' => self::label($slug),
                    'color' => self::colorMapaPorSlug($slug),
                ];
            })
            ->values()
            ->all();
    }

    /** Clave JS normalizada (sin tildes) → slug, para lotes sin estado_slug. */
    /** @return array<string, string> */
    public static function mapaSlugPorNombreJs(): array
    {
        $map = [];

        $agregar = function (string $nombre, string $slug) use (&$map): void {
            $map[mb_strtolower(trim($nombre))] = $slug;
            $norm = self::normalizarClaveJs($nombre);
            if ($norm !== '') {
                $map[$norm] = $slug;
            }
        };

        foreach (self::LEGACY_MAP as $nombre => $slug) {
            $agregar($nombre, $slug);
        }

        foreach (self::ESTADOS as $slug => $meta) {
            $agregar($meta['label'], $slug);
            $agregar(str_replace('_', ' ', $slug), $slug);
        }

        return $map;
    }

    private static function normalizarClaveJs(string $texto): string
    {
        $t = mb_strtolower(trim($texto));
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        $t = is_string($ascii) && $ascii !== '' ? $ascii : $t;

        return preg_replace('/\s+/', ' ', $t) ?? $t;
    }

    public static function urlCambioEstado(\App\Models\Lote $lote, string $slug): string
    {
        $return = route('lotes.trazabilidad', $lote).'#historial-eventos';

        return match ($slug) {
            'sembrado' => route('lotes.siembra.create', [
                'lote' => $lote->loteid,
                'return' => $return,
            ]),
            'en_crecimiento' => route('actividades.create', [
                'loteid' => $lote->loteid,
                'tipo' => 'Riego',
                'return' => $return,
            ]),
            'cosechado' => route('producciones.create', [
                'loteid' => $lote->loteid,
                'return' => $return,
            ]),
            default => route('lotes.cambiar-estado', ['lote' => $lote, 'estado' => $slug]),
        };
    }
}
