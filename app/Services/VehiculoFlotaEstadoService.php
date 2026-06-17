<?php

namespace App\Services;

use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;
use App\Models\Vehiculo;
use App\Support\EstadoVehiculoCatalogo;
use App\Support\RutaDistribucionCatalogo;
use App\Support\SimulacionRutaCatalogo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VehiculoFlotaEstadoService
{
    /** @var array{placas: array<string, true>, ids: array<int, true>}|null */
    private ?array $mapaEnRuta = null;

    /** @var array<int, array{tipo: string, id: int, url: string}>|null */
    private ?array $rutasPorVehiculoId = null;

    /** @var array<string, array{tipo: string, id: int, url: string}>|null */
    private ?array $rutasPorPlaca = null;

    /**
     * @return array{placas: array<string, true>, ids: array<int, true>}
     */
    public function mapaEnRuta(): array
    {
        if ($this->mapaEnRuta !== null) {
            return $this->mapaEnRuta;
        }

        $this->construirMapasSimulacionActiva();

        return $this->mapaEnRuta ?? ['placas' => [], 'ids' => []];
    }

    public function estaEnRuta(Vehiculo $vehiculo, ?array $mapa = null): bool
    {
        $mapa ??= $this->mapaEnRuta();
        $placa = strtoupper(trim((string) $vehiculo->placa));

        if ($placa !== '' && isset($mapa['placas'][$placa])) {
            return true;
        }

        return isset($mapa['ids'][(int) $vehiculo->vehiculoid]);
    }

    /**
     * @return array{tipo: string, id: int, url: string, codigo: string}|null
     */
    public function rutaTiempoRealParaVehiculo(Vehiculo $vehiculo): ?array
    {
        $this->construirMapasSimulacionActiva();

        $placa = strtoupper(trim((string) $vehiculo->placa));
        if ($placa !== '' && isset($this->rutasPorPlaca[$placa])) {
            return $this->rutasPorPlaca[$placa];
        }

        return $this->rutasPorVehiculoId[(int) $vehiculo->vehiculoid] ?? null;
    }

    /** @return 'en_ruta'|'mantenimiento'|'operativo' */
    public function codigoVisual(Vehiculo $vehiculo, ?array $mapa = null): string
    {
        if ($this->estaEnRuta($vehiculo, $mapa)) {
            return 'en_ruta';
        }

        if (EstadoVehiculoCatalogo::enMantenimiento($vehiculo)) {
            return 'mantenimiento';
        }

        return 'operativo';
    }

    public function etiquetaVisual(Vehiculo $vehiculo, ?array $mapa = null): string
    {
        return match ($this->codigoVisual($vehiculo, $mapa)) {
            'en_ruta' => 'En ruta',
            'mantenimiento' => 'En mantenimiento',
            default => 'Operativo',
        };
    }

    public function badgeClaseVisual(Vehiculo $vehiculo, ?array $mapa = null): string
    {
        return match ($this->codigoVisual($vehiculo, $mapa)) {
            'en_ruta' => 'veh-estado veh-estado--ruta',
            'mantenimiento' => 'veh-estado veh-estado--mantenimiento',
            default => 'veh-estado veh-estado--operativo',
        };
    }

    /** @return Collection<int, Vehiculo> */
    public function todosOperativos(?Collection $vehiculos = null): Collection
    {
        $mapa = $this->mapaEnRuta();
        $coleccion = $vehiculos ?? Vehiculo::query()->with('estadoVehiculo')->get();

        return $coleccion->filter(
            fn (Vehiculo $v) => $this->codigoVisual($v, $mapa) === 'operativo'
        )->values();
    }

    public function contarPorEstadoVisual(?Collection $vehiculos = null): array
    {
        $mapa = $this->mapaEnRuta();
        $coleccion = $vehiculos ?? Vehiculo::query()->with('estadoVehiculo')->get();

        $conteo = ['operativo' => 0, 'mantenimiento' => 0, 'en_ruta' => 0];

        foreach ($coleccion as $vehiculo) {
            $codigo = $this->codigoVisual($vehiculo, $mapa);
            $conteo[$codigo]++;
        }

        return $conteo;
    }

    /** @return list<string> */
    public function opcionesFiltro(): array
    {
        return [
            'operativo' => 'Operativo',
            'mantenimiento' => 'En mantenimiento',
            'en_ruta' => 'En ruta',
        ];
    }

    public function aplicarFiltroVisual($query, string $filtro): void
    {
        $mapa = $this->mapaEnRuta();
        $placas = array_keys($mapa['placas']);
        $ids = array_keys($mapa['ids']);

        if ($filtro === 'en_ruta') {
            $query->where(function ($w) use ($placas, $ids) {
                $tieneCriterio = false;
                if ($placas !== []) {
                    $w->whereIn(DB::raw('UPPER(placa)'), $placas);
                    $tieneCriterio = true;
                }
                if ($ids !== []) {
                    $tieneCriterio
                        ? $w->orWhereIn('vehiculoid', $ids)
                        : $w->whereIn('vehiculoid', $ids);
                }
                if (! $tieneCriterio) {
                    $w->whereRaw('1 = 0');
                }
            });

            return;
        }

        if ($filtro === 'mantenimiento') {
            $idMant = EstadoVehiculoCatalogo::idMantenimiento();
            if ($idMant) {
                $query->where('estadovehiculoid', $idMant);
            } else {
                $query->whereRaw('1 = 0');
            }

            return;
        }

        if ($filtro === 'operativo') {
            $idMant = EstadoVehiculoCatalogo::idMantenimiento();
            if ($idMant) {
                $query->where(function ($w) use ($idMant) {
                    $w->where('estadovehiculoid', '!=', $idMant)
                        ->orWhereNull('estadovehiculoid');
                });
            }

            if ($placas !== []) {
                $query->whereNotIn(DB::raw('UPPER(placa)'), $placas);
            }
            if ($ids !== []) {
                $query->whereNotIn('vehiculoid', $ids);
            }

            $query->where('activo', true);
        }
    }

    private function construirMapasSimulacionActiva(): void
    {
        if ($this->mapaEnRuta !== null) {
            return;
        }

        $placas = [];
        $ids = [];
        $this->rutasPorPlaca = [];
        $this->rutasPorVehiculoId = [];

        foreach ($this->enviosAgricolaEnSimulacion() as $envio) {
            $placa = strtoupper(trim((string) $envio->vehiculo_ref));
            $ruta = [
                'tipo' => SimulacionRutaCatalogo::TIPO_AGRICOLA,
                'id' => (int) $envio->envioasignacionmultipleid,
                'url' => route('logistica.rutas-tiempo-real.show', [
                    'tipo' => SimulacionRutaCatalogo::TIPO_AGRICOLA,
                    'id' => $envio->envioasignacionmultipleid,
                ]),
                'codigo' => $envio->externo_envio_id ?? ('#'.$envio->envioasignacionmultipleid),
            ];

            if ($placa !== '') {
                $placas[$placa] = true;
                $this->rutasPorPlaca[$placa] = $ruta;
            }
        }

        foreach ($this->rutasDistribucionEnSimulacion() as $ruta) {
            $vehiculoId = (int) $ruta->vehiculoid;
            if ($vehiculoId <= 0) {
                continue;
            }

            $ids[$vehiculoId] = true;
            $this->rutasPorVehiculoId[$vehiculoId] = [
                'tipo' => SimulacionRutaCatalogo::TIPO_DISTRIBUCION,
                'id' => (int) $ruta->rutadistribucionid,
                'url' => route('logistica.rutas-tiempo-real.show', [
                    'tipo' => SimulacionRutaCatalogo::TIPO_DISTRIBUCION,
                    'id' => $ruta->rutadistribucionid,
                ]),
                'codigo' => $ruta->codigo,
            ];
        }

        $this->mapaEnRuta = ['placas' => $placas, 'ids' => $ids];
    }

    /** @return Collection<int, EnvioAsignacionMultiple> */
    private function enviosAgricolaEnSimulacion(): Collection
    {
        return EnvioAsignacionMultiple::query()
            ->whereNotNull('simulacion_inicio_at')
            ->get()
            ->filter(fn (EnvioAsignacionMultiple $e) => SimulacionRutaCatalogo::simulacionActivaAgricola($e))
            ->values();
    }

    /** @return Collection<int, RutaDistribucion> */
    private function rutasDistribucionEnSimulacion(): Collection
    {
        if (! Schema::hasTable('ruta_distribucion')) {
            return collect();
        }

        return RutaDistribucion::query()
            ->whereNotNull('simulacion_inicio_at')
            ->where('estado', RutaDistribucionCatalogo::ESTADO_EN_RUTA)
            ->whereNotNull('vehiculoid')
            ->get()
            ->filter(fn (RutaDistribucion $r) => SimulacionRutaCatalogo::simulacionActivaDistribucion($r))
            ->values();
    }
}
