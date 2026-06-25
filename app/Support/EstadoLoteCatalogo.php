<?php

namespace App\Support;

use App\Models\EstadoLoteTipo;
use Illuminate\Support\Collection;

class EstadoLoteCatalogo
{
    /** @var array<string, array{label: string, descripcion: string, orden: int}> */
    public const ESTADOS = [
        'planificado' => [
            'label' => 'Planificado',
            'descripcion' => 'El lote fue creado pero aún no se ha sembrado.',
            'orden' => 1,
        ],
        'sembrado' => [
            'label' => 'Sembrado',
            'descripcion' => 'La siembra ya fue realizada.',
            'orden' => 2,
        ],
        'en_crecimiento' => [
            'label' => 'En crecimiento',
            'descripcion' => 'El cultivo está desarrollándose (germinación, crecimiento vegetativo, floración y maduración).',
            'orden' => 3,
        ],
        'listo_para_cosecha' => [
            'label' => 'Listo para cosecha',
            'descripcion' => 'El cultivo alcanzó las condiciones para ser cosechado.',
            'orden' => 4,
        ],
        'certificado' => [
            'label' => 'Certificado',
            'descripcion' => 'El lote fue certificado en campo.',
            'orden' => 5,
        ],
        'cosechado' => [
            'label' => 'Cosechado',
            'descripcion' => 'La producción fue recolectada.',
            'orden' => 6,
        ],
        'finalizado' => [
            'label' => 'Finalizado',
            'descripcion' => 'El ciclo del lote terminó y ya no se realizarán más actividades.',
            'orden' => 7,
        ],
    ];

    /** @var array<string, string> legacy nombre (slug) → slug canónico */
    private const LEGACY_MAP = [
        'disponible' => 'planificado',
        'en preparación' => 'planificado',
        'en preparacion' => 'planificado',
        'planificado' => 'planificado',
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

        return $id ? (int) $id : null;
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
