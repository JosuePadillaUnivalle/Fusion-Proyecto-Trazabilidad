<?php

namespace App\Support;

use App\Models\LoteProduccionPedido;
use App\Models\PlantillaTransformacion;
use App\Models\PlantillaTransformacionPaso;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\ProcesoPlanta;
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

        $plantilla = $this->plantillaDelLote($lote);
        if ($plantilla) {
            $plantilla->loadMissing(['pasos.proceso']);
            $totalPasos = $plantilla->pasos->count();
            if ($totalPasos === 0 || $registros->count() < $totalPasos) {
                return false;
            }

            $ultimoPasoPlantilla = $plantilla->pasos->sortByDesc('orden')->first();
            $ultimoRegistro = $registros->last();
            $nombreUltimoPaso = $ultimoPasoPlantilla?->proceso?->nombre ?? '';
            $nombreUltimoRegistro = $ultimoRegistro->procesoMaquina?->proceso?->nombre ?? '';

            return $registros->count() === $totalPasos
                && ProcesoPlantaCatalogo::esCierreTransformacion($nombreUltimoPaso)
                && $nombreUltimoRegistro === $nombreUltimoPaso;
        }

        $ultimo = $registros->last();
        $nombre = $ultimo->procesoMaquina?->proceso?->nombre;

        return ProcesoPlantaCatalogo::esCierreTransformacion($nombre);
    }

    public function plantillaAgotada(LoteProduccionPedido $lote): bool
    {
        $plantilla = $this->plantillaDelLote($lote);
        if (! $plantilla) {
            return false;
        }

        return $this->registrosOrdenados($lote)->count() >= $plantilla->pasos()->count();
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

        $proceso = ProcesoPlanta::query()->find($procesoplantaid);
        $orden = (int) ProcesoMaquinaPlanta::query()
            ->where('procesoplantaid', $procesoplantaid)
            ->max('orden_paso');

        return ProcesoMaquinaPlanta::create([
            'procesoplantaid' => $procesoplantaid,
            'maquinaplantaid' => $maquinaplantaid,
            'orden_paso' => max(1, $orden + 1),
            'nombre' => $proceso?->nombre ?? 'Paso',
            'descripcion' => 'Vínculo generado automáticamente desde plantilla de transformación.',
        ]);
    }

    public function plantillaDelLote(LoteProduccionPedido $lote): ?PlantillaTransformacion
    {
        if (! $lote->plantillatransformacionid) {
            return null;
        }

        return PlantillaTransformacionResolver::resolverPorId((int) $lote->plantillatransformacionid);
    }

    /**
     * @return list<array{orden: int, proceso: string, maquina: ?string, procesoplantaid: int, maquinaplantaid: ?int, notas: ?string, estado: string}>
     */
    public function rutaPlantilla(LoteProduccionPedido $lote): array
    {
        $plantilla = $this->plantillaDelLote($lote);
        if (! $plantilla) {
            return [];
        }

        $plantilla->loadMissing(['pasos.proceso', 'pasos.maquina']);
        $completados = $this->registrosOrdenados($lote)->count();
        $items = [];

        foreach ($plantilla->pasos as $paso) {
            $orden = (int) $paso->orden;
            $estado = 'pendiente';
            if ($orden <= $completados) {
                $estado = 'hecho';
            } elseif ($orden === $completados + 1) {
                $estado = 'actual';
            }

            $items[] = [
                'orden' => $orden,
                'proceso' => $paso->proceso?->nombre ?? '—',
                'maquina' => $paso->maquina?->nombre,
                'procesoplantaid' => (int) $paso->procesoplantaid,
                'maquinaplantaid' => $paso->maquinaplantaid ? (int) $paso->maquinaplantaid : null,
                'notas' => $paso->notas,
                'estado' => $estado,
            ];
        }

        return $items;
    }

    public function siguientePasoPlantilla(LoteProduccionPedido $lote): ?PlantillaTransformacionPaso
    {
        $plantilla = $this->plantillaDelLote($lote);
        if (! $plantilla) {
            return null;
        }

        $siguienteOrden = $this->registrosOrdenados($lote)->count() + 1;

        return $plantilla->pasos()
            ->with(['proceso', 'maquina'])
            ->where('orden', $siguienteOrden)
            ->first();
    }
}
