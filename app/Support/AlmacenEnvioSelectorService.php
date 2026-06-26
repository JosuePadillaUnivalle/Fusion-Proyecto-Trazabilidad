<?php

namespace App\Support;

use App\Models\Almacen;
use App\Models\AlmacenajeLoteProduccion;
use App\Models\Lote;
use App\Models\ProduccionAlmacenamiento;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

class AlmacenEnvioSelectorService
{
    /**
     * @param  callable(Builder): void|null  $personalizarQuery
     * @return array<string, mixed>
     */
    public function datosAmbito(string $ambito, ?callable $personalizarQuery = null, ?string $tablaUso = null): array
    {
        AlmacenAmbito::asegurarAmbitosEnRegistros();
        $capacidadService = app(\App\Services\AlmacenCapacidadService::class);

        $query = AlmacenAmbito::scope(
            Almacen::with(['tipoAlmacen', 'unidadMedida', 'almacenamientos'])
                ->where('activo', true)
                ->where('capacidad', '>', 0),
            $ambito
        );

        if ($personalizarQuery) {
            $personalizarQuery($query);
        }

        $almacenesTodos = $query->get();

        $usoPorAlmacen = match ($tablaUso ?? $ambito) {
            AlmacenAmbito::PLANTA => AlmacenajeLoteProduccion::query()
                ->selectRaw('almacenid, COUNT(*) as total')
                ->whereNotNull('almacenid')
                ->groupBy('almacenid')
                ->pluck('total', 'almacenid'),
            default => ProduccionAlmacenamiento::query()
                ->selectRaw('almacenid, COUNT(*) as total')
                ->groupBy('almacenid')
                ->pluck('total', 'almacenid'),
        };

        $ordenados = $almacenesTodos->count() <= 4
            ? $almacenesTodos->values()
            : $almacenesTodos->sortByDesc(fn (Almacen $a) => (int) ($usoPorAlmacen[$a->almacenid] ?? 0));

        $almacenesMasUsados = $almacenesTodos->count() <= 4
            ? $almacenesTodos->values()
            : $ordenados->take(4)->values();

        $almacenesMenosUsados = $almacenesTodos->count() <= 4
            ? $almacenesTodos->values()
            : $ordenados->reverse()->take(4)->values();

        $resumenesCapacidad = $almacenesTodos
            ->mapWithKeys(fn (Almacen $a) => [$a->almacenid => $capacidadService->resumen($a)])
            ->all();

        $almacenesCatalogo = $almacenesTodos->map(function (Almacen $almacen) use ($capacidadService) {
            $resumen = $capacidadService->resumen($almacen);
            $resuelto = UbicacionGpsParser::resolverAlmacen(
                (int) $almacen->almacenid,
                $almacen->nombre,
                $almacen->ubicacion
            );

            return [
                'id' => $almacen->almacenid,
                'nombre' => $almacen->nombre,
                'tipo' => $almacen->tipoAlmacen->nombre ?? 'General',
                'ubicacion' => $almacen->ubicacion,
                'disponible' => $resumen['disponible_kg'],
                'capacidad' => $resumen['capacidad_kg'],
                'um' => 'kg',
                'tags' => strtolower($almacen->nombre.' '.($almacen->tipoAlmacen->nombre ?? '').' '.($almacen->ubicacion ?? '')),
                'lat' => $resuelto['lat'],
                'lng' => $resuelto['lng'],
                'direccion' => $resuelto['direccion'],
                'ubicacion_estimada' => $resuelto['estimada'],
            ];
        })->values()->all();

        return [
            'almacenes' => $almacenesMasUsados,
            'almacenesMasUsados' => $almacenesMasUsados,
            'almacenesMenosUsados' => $almacenesMenosUsados,
            'almacenesTodos' => $almacenesTodos,
            'almacenesCatalogo' => $almacenesCatalogo,
            'resumenesCapacidad' => $resumenesCapacidad,
        ];
    }

    /** @return array<string, mixed> */
    public function datosCampoDesdeLote(Lote $lote, ?Usuario $user = null): array
    {
        $lote->loadMissing('usuario');

        $base = $this->datosAmbito(
            AlmacenAmbito::AGRICOLA,
            fn (Builder $query) => AlmacenAmbito::scopeEnvioCampoDesdeLote($query, $lote, $user),
            AlmacenAmbito::AGRICOLA
        );

        $produccion = app(LoteTrazabilidadService::class)->produccionPendienteAlmacen($lote);
        $almacenDestinoId = old('almacenid') ?: $produccion?->almacendestinoid;
        $almacenDestino = $almacenDestinoId
            ? $base['almacenesTodos']->firstWhere('almacenid', (int) $almacenDestinoId)
            : null;

        return array_merge($base, [
            'produccion_pendiente_almacen' => $produccion,
            'almacen_destino_id' => $almacenDestinoId ? (int) $almacenDestinoId : null,
            'almacen_destino_preseleccionado' => $almacenDestino,
            'almacen_destino_resumen' => $almacenDestino
                ? ($base['resumenesCapacidad'][$almacenDestino->almacenid] ?? null)
                : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function datosPlanta(): array
    {
        return $this->datosAmbito(AlmacenAmbito::PLANTA, null, AlmacenAmbito::PLANTA);
    }
}
