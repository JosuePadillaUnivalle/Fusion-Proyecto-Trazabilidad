<?php

namespace App\Support;

use App\Models\LoteProduccionPedido;
use App\Models\ProcesoPlanta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProcesoPlantaCatalogo
{
    /** @var list<string> Catálogo único (referencia PLANTA - IDEA). */
    public const CANONICOS = [
        'Preparación de Materias Primas',
        'Mezclado',
        'Extrusión',
        'Moldeo',
        'Secado',
        'Tratamiento Térmico',
        'Envasado',
        'Etiquetado',
        'Empaquetado',
        'Control de Calidad',
    ];

    /** Proceso que cierra la fase de transformación antes de certificar. */
    public const PROCESO_CIERRE_TRANSFORMACION = 'Empaquetado';

    /** @var list<string> Procesos excluidos de la línea de transformación. */
    private const EXCLUIDOS_TRANSFORMACION = ['Control de Calidad'];

    /** @var array<string, string> nombre alternativo (minúsculas) => canónico */
    private const ALIAS = [
        'control de calidad' => 'Control de Calidad',
        'empaque' => 'Empaquetado',
        'clasificación por calidad' => 'Control de Calidad',
        'clasificacion por calidad' => 'Control de Calidad',
        'lavado y selección' => 'Preparación de Materias Primas',
        'lavado y seleccion' => 'Preparación de Materias Primas',
    ];

    /**
     * @return Collection<int, ProcesoPlanta>
     */
    public static function activosOrdenados(): Collection
    {
        $items = self::queryActivos()->get();

        return $items->sortBy(function (ProcesoPlanta $p) {
            $idx = array_search($p->nombre, self::CANONICOS, true);

            return $idx === false ? 99 : $idx;
        })->values();
    }

    public static function queryActivos(): Builder
    {
        return ProcesoPlanta::query()
            ->where('activo', true)
            ->whereIn('nombre', self::CANONICOS);
    }

    /**
     * Procesos disponibles para registrar etapas de transformación (sin control de calidad).
     *
     * @return Collection<int, ProcesoPlanta>
     */
    public static function paraTransformacion(): Collection
    {
        return self::activosOrdenados()
            ->filter(fn (ProcesoPlanta $p) => ! in_array($p->nombre, self::EXCLUIDOS_TRANSFORMACION, true))
            ->values();
    }

    public static function esCierreTransformacion(?string $nombreProceso): bool
    {
        $norm = self::normalizarNombre($nombreProceso);

        return $norm === self::PROCESO_CIERRE_TRANSFORMACION;
    }

    public static function idProcesoCierreTransformacion(): ?int
    {
        $id = ProcesoPlanta::query()
            ->where('nombre', self::PROCESO_CIERRE_TRANSFORMACION)
            ->where('activo', true)
            ->value('procesoplantaid');

        return $id !== null ? (int) $id : null;
    }

    /**
     * @param  list<array{procesoplantaid?: mixed}>  $pasos
     */
    public static function errorSiUltimoPasoNoEsEmpaquetado(array $pasos): ?string
    {
        $ids = [];
        foreach ($pasos as $paso) {
            if (! empty($paso['procesoplantaid'])) {
                $ids[] = (int) $paso['procesoplantaid'];
            }
        }

        if ($ids === []) {
            return 'Debe definir al menos un paso en la línea.';
        }

        $cierreId = self::idProcesoCierreTransformacion();
        $vecesEmpaquetado = 0;
        foreach ($ids as $id) {
            if ($cierreId !== null && $id === $cierreId) {
                $vecesEmpaquetado++;
            }
        }

        if ($vecesEmpaquetado > 1) {
            return 'Solo puede incluir «'.self::PROCESO_CIERRE_TRANSFORMACION.'» una vez en la línea.';
        }

        $ultimoId = $ids[array_key_last($ids)];
        $nombre = ProcesoPlanta::query()->find($ultimoId)?->nombre;

        if (! self::esCierreTransformacion($nombre)) {
            return 'El último paso debe ser «'.self::PROCESO_CIERRE_TRANSFORMACION.'». Agregue Empaquetado como etapa final antes de guardar.';
        }

        return null;
    }

    public static function normalizarNombre(?string $nombre): ?string
    {
        if ($nombre === null || trim($nombre) === '') {
            return null;
        }

        $key = Str::lower(trim($nombre));

        return self::ALIAS[$key] ?? trim($nombre);
    }

    public static function consolidarDuplicados(): int
    {
        $desactivados = 0;

        foreach (ProcesoPlanta::query()->orderBy('procesoplantaid')->get() as $proceso) {
            $canonico = self::normalizarNombre($proceso->nombre);
            if ($canonico === null) {
                continue;
            }

            $destino = ProcesoPlanta::query()
                ->where('nombre', $canonico)
                ->orderByDesc('activo')
                ->orderBy('procesoplantaid')
                ->first();

            if (! $destino) {
                $proceso->update([
                    'nombre' => $canonico,
                    'activo' => in_array($canonico, self::CANONICOS, true),
                ]);

                continue;
            }

            if ((int) $proceso->procesoplantaid === (int) $destino->procesoplantaid) {
                if (! in_array($canonico, self::CANONICOS, true)) {
                    $proceso->update(['activo' => false]);
                    $desactivados++;
                }

                continue;
            }

            LoteProduccionPedido::query()
                ->where('procesoplantaid', $proceso->procesoplantaid)
                ->update(['procesoplantaid' => $destino->procesoplantaid]);

            $proceso->update(['activo' => false]);
            $desactivados++;
        }

        foreach (self::CANONICOS as $nombre) {
            ProcesoPlanta::updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => true, 'descripcion' => ProcesoPlanta::where('nombre', $nombre)->value('descripcion')]
            );
        }

        ProcesoPlanta::query()
            ->where('activo', true)
            ->whereNotIn('nombre', self::CANONICOS)
            ->update(['activo' => false]);

        return $desactivados;
    }
}
