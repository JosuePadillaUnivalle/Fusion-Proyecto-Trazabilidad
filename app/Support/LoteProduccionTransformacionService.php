<?php

namespace App\Support;

use App\Models\LoteProduccionPedido;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\RegistroProcesoMaquinaPlanta;
use Illuminate\Support\Collection;

class LoteProduccionTransformacionService
{
    public function registrosOrdenados(LoteProduccionPedido $lote): Collection
    {
        return RegistroProcesoMaquinaPlanta::query()
            ->where('loteproduccionpedidoid', $lote->loteproduccionpedidoid)
            ->orderBy('hora_inicio')
            ->orderBy('registroprocesomaquinaplantaid')
            ->with(['procesoMaquina.proceso', 'procesoMaquina.maquina', 'usuario'])
            ->get();
    }

    public function transformacionCompleta(LoteProduccionPedido $lote): bool
    {
        $registros = $this->registrosOrdenados($lote);
        if ($registros->isEmpty()) {
            return false;
        }

        $ultimo = $registros->last();
        $nombre = $ultimo->procesoMaquina?->proceso?->nombre;

        return ProcesoPlantaCatalogo::esCierreTransformacion($nombre);
    }

    public function transformacionIniciada(LoteProduccionPedido $lote): bool
    {
        return $this->registrosOrdenados($lote)->isNotEmpty();
    }

    /**
     * @return list<array{numero: int, registro: RegistroProcesoMaquinaPlanta, proceso: string, maquina: string, inicio: ?\Carbon\Carbon, fin: ?\Carbon\Carbon, observaciones: ?string, operador: ?string, es_cierre: bool}>
     */
    public function timeline(LoteProduccionPedido $lote): array
    {
        $items = [];
        $num = 1;

        foreach ($this->registrosOrdenados($lote) as $registro) {
            $proceso = $registro->procesoMaquina?->proceso?->nombre ?? '—';
            $items[] = [
                'numero' => $num++,
                'registro' => $registro,
                'proceso' => $proceso,
                'maquina' => $registro->procesoMaquina?->maquina?->nombre ?? '—',
                'inicio' => $registro->hora_inicio,
                'fin' => $registro->hora_fin,
                'observaciones' => $registro->observaciones,
                'operador' => $registro->usuario?->nombre,
                'es_cierre' => ProcesoPlantaCatalogo::esCierreTransformacion($proceso),
            ];
        }

        return $items;
    }

    public function procesoYaRegistrado(LoteProduccionPedido $lote, int $procesoplantaid): bool
    {
        return $this->registrosOrdenados($lote)->contains(function (RegistroProcesoMaquinaPlanta $reg) use ($procesoplantaid) {
            return (int) ($reg->procesoMaquina?->procesoplantaid ?? 0) === $procesoplantaid;
        });
    }

    /**
     * @return list<int>
     */
    public function procesosRegistradosIds(LoteProduccionPedido $lote): array
    {
        return $this->registrosOrdenados($lote)
            ->map(fn (RegistroProcesoMaquinaPlanta $r) => (int) ($r->procesoMaquina?->procesoplantaid ?? 0))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function resolverPasoProcesoMaquina(int $procesoplantaid, int $maquinaplantaid): ProcesoMaquinaPlanta
    {
        if (! MaquinaProcesoCompatibilidad::compatible($procesoplantaid, $maquinaplantaid)) {
            throw new \InvalidArgumentException('La maquinaria seleccionada no corresponde a ese proceso de planta.');
        }

        $existente = ProcesoMaquinaPlanta::query()
            ->where('procesoplantaid', $procesoplantaid)
            ->where('maquinaplantaid', $maquinaplantaid)
            ->first();

        if ($existente) {
            return $existente;
        }

        throw new \InvalidArgumentException('No hay vínculo configurado entre ese proceso y la maquinaria.');
    }
}
