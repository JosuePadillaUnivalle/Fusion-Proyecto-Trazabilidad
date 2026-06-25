<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\EstadoLoteInsumo;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteInsumo;
use App\Models\TipoInsumo;
use App\Support\ActividadDetalleCatalogo;
use App\Support\InsumoCatalogo;
use App\Support\InsumoImagenCatalogo;
use App\Support\PedidoCatalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActividadInsumoService
{
    /**
     * @return array<string, mixed>
     */
    public function parseDetalleDesdeRequest(Request $request, ?string $tipoActividadNombre): array
    {
        $raw = $request->input('detalle_actividad_json');
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $detalle
     * @return array<string, mixed>
     */
    public function validarDetalle(array $detalle, ?string $tipoActividadNombre): array
    {
        if (ActividadDetalleCatalogo::esRiego($tipoActividadNombre)) {
            $tipoRiego = trim((string) ($detalle['riego']['key'] ?? ''));
            if ($tipoRiego === '') {
                throw ValidationException::withMessages([
                    'detalle_actividad_json' => 'Seleccione el tipo de riego en el modal.',
                ]);
            }

            return [
                'modo' => 'riego',
                'riego' => [
                    'key' => $tipoRiego,
                    'label' => trim((string) ($detalle['riego']['label'] ?? $tipoRiego)),
                ],
                'stock_aplicado' => false,
            ];
        }

        $slug = ActividadDetalleCatalogo::slugInsumoParaTipoActividad($tipoActividadNombre);
        if ($slug === null) {
            return [];
        }

        $filas = collect($detalle['insumos'] ?? [])
            ->filter(fn ($f) => is_array($f) && (int) ($f['insumoid'] ?? 0) > 0)
            ->values();

        if ($filas->isEmpty()) {
            throw ValidationException::withMessages([
                'detalle_actividad_json' => 'Seleccione al menos un insumo en el modal.',
            ]);
        }

        $max = ActividadDetalleCatalogo::maxInsumosPorTipo($tipoActividadNombre);
        if ($filas->count() > $max) {
            throw ValidationException::withMessages([
                'detalle_actividad_json' => $max === 1
                    ? 'En siembra solo puede usar un material.'
                    : 'Demasiados insumos seleccionados.',
            ]);
        }

        $normalizados = [];
        foreach ($filas as $fila) {
            $insumo = Insumo::query()->with(['tipo', 'unidadMedida'])->find((int) $fila['insumoid']);
            if ($insumo === null || ! InsumoCatalogo::esInsumoOperativo($insumo)) {
                throw ValidationException::withMessages([
                    'detalle_actividad_json' => 'Uno de los insumos seleccionados no es válido.',
                ]);
            }

            $insumoSlug = InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre);
            if ($insumoSlug !== $slug) {
                throw ValidationException::withMessages([
                    'detalle_actividad_json' => 'El insumo «'.$insumo->nombre.'» no corresponde a esta actividad.',
                ]);
            }

            $cantidad = (float) ($fila['cantidad'] ?? 0);
            if ($cantidad <= 0) {
                throw ValidationException::withMessages([
                    'detalle_actividad_json' => 'Indique una cantidad mayor a cero para «'.$insumo->nombre.'».',
                ]);
            }

            $unidad = $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? 'ud';
            if ((float) $insumo->stock < $cantidad) {
                throw ValidationException::withMessages([
                    'detalle_actividad_json' => 'Stock insuficiente de «'.$insumo->nombre.'». Disponible: '
                        .number_format((float) $insumo->stock, 2).' '.$unidad
                        .'; intentó aplicar: '.number_format($cantidad, 2).' '.$unidad.'.',
                ]);
            }

            $normalizados[] = [
                'insumoid' => (int) $insumo->insumoid,
                'nombre' => $insumo->nombre,
                'cantidad' => $cantidad,
                'unidad' => $unidad,
            ];
        }

        return [
            'modo' => 'insumos',
            'insumos' => $normalizados,
            'stock_aplicado' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $detalle
     */
    public function aplicarStockSiCorresponde(Actividad $actividad, array &$detalle): void
    {
        if (($detalle['modo'] ?? '') !== 'insumos' || ! empty($detalle['stock_aplicado'])) {
            return;
        }

        $actividad->loadMissing('lote');

        DB::transaction(function () use ($actividad, &$detalle) {
            $estadoId = $this->idEstadoAplicado();

            foreach ($detalle['insumos'] as $fila) {
                $insumo = Insumo::query()->lockForUpdate()->findOrFail((int) $fila['insumoid']);
                $cantidad = (float) $fila['cantidad'];

                if ($insumo->stock < $cantidad) {
                    throw ValidationException::withMessages([
                        'detalle_actividad_json' => 'Stock insuficiente de «'.$insumo->nombre.'». Disponible: '
                            .number_format((float) $insumo->stock, 2).' '.($fila['unidad'] ?? ''),
                    ]);
                }

                $insumo->decrementarStock($cantidad);

                LoteInsumo::create([
                    'loteid' => $actividad->loteid,
                    'actividadid' => $actividad->actividadid,
                    'insumoid' => $insumo->insumoid,
                    'usuarioid' => $actividad->usuarioid,
                    'cantidadusada' => $cantidad,
                    'fechauo' => now(),
                    'costototal' => 0,
                    'estadoloteinsumoid' => $estadoId,
                    'observaciones' => 'Actividad #'.$actividad->actividadid,
                ]);
            }

            $detalle['stock_aplicado'] = true;
            $actividad->detalle_json = json_encode($detalle, JSON_UNESCAPED_UNICODE);
            $actividad->save();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarInsumosParaModal(string $tipoSlug, ?Lote $lote = null): array
    {
        $tipoIds = TipoInsumo::query()
            ->get(['tipoinsumoid', 'nombre'])
            ->filter(fn (TipoInsumo $t) => InsumoCatalogo::slugFromNombreTipo($t->nombre) === $tipoSlug)
            ->pluck('tipoinsumoid')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($tipoIds === []) {
            return [];
        }

        $query = InsumoCatalogo::aplicarFiltroOperativo(
            Insumo::query()->with(['tipo', 'unidadMedida'])
        )->whereIn('tipoinsumoid', $tipoIds)
            ->where('stock', '>', 0)
            ->orderBy('nombre');

        $referenciaNombre = null;
        $insumoPlanificadoId = null;
        if ($lote) {
            $lote->loadMissing('insumoSemilla', 'cultivo');
            if ($lote->insumoSemilla) {
                $insumoPlanificadoId = (int) $lote->insumosemillaid;
                $referenciaNombre = PedidoCatalogo::cultivoDesdeInsumo($lote->insumoSemilla);
            } else {
                $referenciaNombre = $lote->cultivo?->nombre;
            }
        }

        if ($tipoSlug === 'material_siembra' && $insumoPlanificadoId) {
            $planificado = (clone $query)->where('insumoid', $insumoPlanificadoId)->first();
            $resto = $query->get()->reject(fn (Insumo $i) => (int) $i->insumoid === $insumoPlanificadoId);

            $coleccion = collect($planificado ? [$planificado] : [])->merge($resto);

            return $coleccion->map(fn (Insumo $i) => self::mapearInsumoModal($i, $lote, $tipoSlug))->values()->all();
        }

        if ($tipoSlug === 'material_siembra' && $referenciaNombre) {
            $candidatos = (clone $query)->get();
            $filtrados = $candidatos->filter(function (Insumo $i) use ($referenciaNombre) {
                $nombre = mb_strtolower($i->nombre);
                $palabras = preg_split('/\s+/u', mb_strtolower($referenciaNombre)) ?: [];
                foreach ($palabras as $palabra) {
                    if (mb_strlen($palabra) >= 3 && str_contains($nombre, $palabra)) {
                        return true;
                    }
                }

                return false;
            });
            if ($filtrados->isNotEmpty()) {
                return $filtrados->map(fn (Insumo $i) => self::mapearInsumoModal($i, $lote, $tipoSlug))->values()->all();
            }
        }

        return $query->get()->map(fn (Insumo $i) => self::mapearInsumoModal($i, $lote, $tipoSlug))->values()->all();
    }

    /** @return array<string, mixed> */
    private static function mapearInsumoModal(Insumo $i, ?Lote $lote = null, ?string $tipoSlug = null): array
    {
        $data = [
            'id' => (int) $i->insumoid,
            'nombre' => $i->nombre,
            'stock' => (float) $i->stock,
            'unidad' => $i->unidadMedida?->abreviatura ?? $i->unidadMedida?->nombre ?? 'ud',
            'unidad_nombre' => $i->unidadMedida?->nombre ?? 'Unidad',
            'imagen' => InsumoImagenCatalogo::urlPara($i),
        ];

        if ($lote && in_array($tipoSlug, ['fertilizantes', 'pesticidas'], true)) {
            $sug = \App\Support\CultivoSiembraCatalogo::sugerenciaAplicacionInsumo($i, (float) $lote->superficie);
            $data['sugerencia'] = $sug['tiene_dosis'] ? $sug['sugerido'] : null;
            $data['sugerencia_detalle'] = $sug;
        }

        return $data;
    }

    private function idEstadoAplicado(): int
    {
        return (int) (EstadoLoteInsumo::query()
            ->whereRaw('LOWER(nombre) = ?', ['aplicado'])
            ->value('estadoloteinsumoid')
            ?? EstadoLoteInsumo::query()->firstOrCreate(['nombre' => 'Aplicado'], ['nombre' => 'Aplicado'])->estadoloteinsumoid);
    }
}
