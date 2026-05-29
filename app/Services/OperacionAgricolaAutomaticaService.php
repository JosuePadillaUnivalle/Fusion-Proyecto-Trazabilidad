<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Clima;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Models\LoteInsumo;
use App\Models\Prioridad;
use App\Models\Produccion;
use App\Models\TipoActividad;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Automatización operativa: actividades y clima desde eventos reales del negocio
 * (insumos en lote, cosechas, clima programado). No es carga demo manual.
 */
class OperacionAgricolaAutomaticaService
{
    public const MARK = '[AUTO-OP]';

    public function sincronizarTodo(): array
    {
        $resumen = [
            'clima_lotes' => 0,
            'actividades_insumo' => 0,
            'actividades_cosecha' => 0,
            'actividades_riego' => 0,
            'actividades_clima' => 0,
            'lotes_promovidos' => 0,
        ];

        $resumen['clima_lotes'] = $this->registrarClimaPorLotes();
        $resumen['actividades_insumo'] = $this->reconciliarActividadesDesdeInsumos();
        $resumen['actividades_cosecha'] = $this->reconciliarActividadesDesdeProduccion();
        $resumen['actividades_riego'] = $this->generarRiegosProgramados();
        $resumen['actividades_clima'] = $this->generarAlertasClimaticas();
        $resumen['lotes_promovidos'] = $this->promoverLotesConOperacionReciente();

        return $resumen;
    }

    public function desdeLoteInsumo(LoteInsumo $loteInsumo): ?Actividad
    {
        $loteInsumo->loadMissing(['lote', 'insumo.tipo']);

        if (! $loteInsumo->lote) {
            return null;
        }

        $tipoNombre = $this->inferirTipoPorInsumo($loteInsumo->insumo?->nombre ?? '', $loteInsumo->insumo?->tipo?->nombre ?? '');
        $tipo = $this->resolverTipoActividad($tipoNombre);
        if (! $tipo) {
            return null;
        }

        $marcador = self::MARK.' insumo|'.$loteInsumo->loteinsumoid;

        $actividad = Actividad::firstOrCreate(
            ['observaciones' => $marcador],
            [
                'loteid' => $loteInsumo->loteid,
                'usuarioid' => $loteInsumo->usuarioid ?? $loteInsumo->lote->usuarioid,
                'descripcion' => "Aplicación automática: {$loteInsumo->insumo?->nombre} ({$loteInsumo->cantidadusada} u.)",
                'fechainicio' => $loteInsumo->fechauo ?? now(),
                'fechafin' => now(),
                'tipoactividadid' => $tipo->tipoactividadid,
                'prioridadid' => $this->prioridadMediaId(),
            ]
        );

        $this->promoverLoteAProduccion($loteInsumo->lote);

        return $actividad;
    }

    public function desdeProduccion(Produccion $produccion): ?Actividad
    {
        $produccion->loadMissing(['lote.cultivo', 'unidadMedida']);
        if (! $produccion->lote) {
            return null;
        }

        $tipo = $this->resolverTipoActividad('cosecha');
        if (! $tipo) {
            return null;
        }

        $marcador = self::MARK.' cosecha|'.$produccion->produccionid;
        $um = $produccion->unidadMedida->abreviatura ?? $produccion->unidadMedida->nombre ?? 'ud';

        return Actividad::firstOrCreate(
            ['observaciones' => $marcador],
            [
                'loteid' => $produccion->loteid,
                'usuarioid' => $produccion->lote->usuarioid,
                'descripcion' => 'Cosecha automática: '.$produccion->cantidad.' '.$um
                    .($produccion->lote->cultivo?->nombre ? ' · '.$produccion->lote->cultivo->nombre : ''),
                'fechainicio' => $produccion->fechacosecha ?? now(),
                'fechafin' => $produccion->fechacosecha ?? now(),
                'tipoactividadid' => $tipo->tipoactividadid,
                'prioridadid' => $this->prioridadAltaId(),
            ]
        );
    }

    public function registrarClimaPorLotes(): int
    {
        if (! Schema::hasTable('clima')) {
            return 0;
        }

        $apiKey = config('services.openweather.key', env('OPENWEATHER_API_KEY', ''));
        $creados = 0;

        $lotes = Lote::query()
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        foreach ($lotes as $lote) {
            if (Clima::where('loteid', $lote->loteid)->whereDate('fecha', today())->exists()) {
                continue;
            }

            $payload = $apiKey
                ? $this->fetchClimaApi($lote->latitud, $lote->longitud, $apiKey)
                : $this->climaEstimadoSantaCruz();

            if (! $payload) {
                continue;
            }

            Clima::create(array_merge($payload, [
                'loteid' => $lote->loteid,
                'fecha' => now(),
                'observaciones' => self::MARK.' clima diario '.$lote->nombre,
            ]));

            $creados++;
        }

        return $creados;
    }

    public function generarRiegosProgramados(): int
    {
        $tipo = $this->resolverTipoActividad('riego');
        $estadoProd = $this->estadoId('en producción');
        if (! $tipo || ! $estadoProd) {
            return 0;
        }

        $creados = 0;
        $lotes = Lote::where('estadolotetipoid', $estadoProd)->get();

        foreach ($lotes as $lote) {
            $tieneRiegoReciente = Actividad::where('loteid', $lote->loteid)
                ->where('tipoactividadid', $tipo->tipoactividadid)
                ->where('fechainicio', '>=', now()->subDays(7))
                ->exists();

            if ($tieneRiegoReciente) {
                continue;
            }

            $marcador = self::MARK.' riego|'.$lote->loteid.'|'.now()->format('Y-W');
            Actividad::firstOrCreate(
                ['observaciones' => $marcador],
                [
                    'loteid' => $lote->loteid,
                    'usuarioid' => $lote->usuarioid,
                    'descripcion' => 'Riego programado automático (ciclo semanal)',
                    'fechainicio' => now(),
                    'fechafin' => null,
                    'tipoactividadid' => $tipo->tipoactividadid,
                    'prioridadid' => $this->prioridadMediaId(),
                ]
            );
            $creados++;
        }

        return $creados;
    }

    public function generarAlertasClimaticas(): int
    {
        if (! Schema::hasTable('clima')) {
            return 0;
        }

        $tipo = $this->resolverTipoActividad('control de plagas') ?? $this->resolverTipoActividad('riego');
        if (! $tipo) {
            return 0;
        }

        $creados = 0;
        $climas = Clima::with('lote')
            ->where('fecha', '>=', now()->subDay())
            ->whereNotNull('loteid')
            ->get();

        foreach ($climas as $clima) {
            if (! $clima->lote) {
                continue;
            }

            $alerta = null;
            if (($clima->lluvia ?? 0) >= 5) {
                $alerta = 'Lluvia registrada: revisión de drenaje y fungicida preventivo';
            } elseif (($clima->temperatura ?? 0) >= 34) {
                $alerta = 'Temperatura alta: riego y sombra en horario pico';
            } elseif (($clima->humedad ?? 0) >= 85) {
                $alerta = 'Humedad elevada: monitoreo de hongos';
            }

            if (! $alerta) {
                continue;
            }

            $marcador = self::MARK.' clima-alerta|'.$clima->climaid;
            Actividad::firstOrCreate(
                ['observaciones' => $marcador],
                [
                    'loteid' => $clima->loteid,
                    'usuarioid' => $clima->lote->usuarioid,
                    'descripcion' => $alerta,
                    'fechainicio' => now(),
                    'fechafin' => null,
                    'tipoactividadid' => $tipo->tipoactividadid,
                    'prioridadid' => $this->prioridadAltaId(),
                ]
            );
            $creados++;
        }

        return $creados;
    }

    public function reconciliarActividadesDesdeInsumos(): int
    {
        $n = 0;
        LoteInsumo::with(['lote', 'insumo.tipo'])
            ->orderBy('loteinsumoid')
            ->chunk(100, function ($items) use (&$n) {
                foreach ($items as $item) {
                    if ($this->desdeLoteInsumo($item)) {
                        $n++;
                    }
                }
            });

        return $n;
    }

    public function reconciliarActividadesDesdeProduccion(): int
    {
        $n = 0;
        Produccion::with(['lote.cultivo', 'unidadMedida'])
            ->orderBy('produccionid')
            ->chunk(100, function ($items) use (&$n) {
                foreach ($items as $item) {
                    if ($this->desdeProduccion($item)) {
                        $n++;
                    }
                }
            });

        return $n;
    }

    public function promoverLotesConOperacionReciente(): int
    {
        $n = 0;
        $estadoProd = $this->estadoId('en producción');
        if (! $estadoProd) {
            return 0;
        }

        $loteIds = LoteInsumo::where('fechauo', '>=', now()->subDays(30))
            ->pluck('loteid')
            ->merge(
                Produccion::where('fechacosecha', '>=', now()->subDays(90)->toDateString())->pluck('loteid')
            )
            ->unique();

        foreach (Lote::whereIn('loteid', $loteIds)->get() as $lote) {
            if ($this->promoverLoteAProduccion($lote)) {
                $n++;
            }
        }

        return $n;
    }

    private function promoverLoteAProduccion(Lote $lote): bool
    {
        $estadoProd = $this->estadoId('en producción');
        if (! $estadoProd || (int) $lote->estadolotetipoid === (int) $estadoProd) {
            return false;
        }

        $estadoCosechado = $this->estadoId('cosechado');
        if ($estadoCosechado && (int) $lote->estadolotetipoid === (int) $estadoCosechado) {
            return false;
        }

        $lote->update([
            'estadolotetipoid' => $estadoProd,
            'fechamodificacion' => now(),
        ]);

        if (Schema::hasTable('historial_estados_lote')) {
            HistorialEstadoLote::firstOrCreate(
                [
                    'loteid' => $lote->loteid,
                    'observaciones' => self::MARK.' promoción en producción',
                ],
                [
                    'estadolotetipoid' => $estadoProd,
                    'fecha_cambio' => now(),
                    'usuarioid' => $lote->usuarioid,
                ]
            );
        }

        return true;
    }

    private function inferirTipoPorInsumo(string $insumo, string $tipoInsumo): string
    {
        $texto = mb_strtolower($insumo.' '.$tipoInsumo);

        if (str_contains($texto, 'semilla') || str_contains($texto, 'siembra')) {
            return 'siembra';
        }
        if (str_contains($texto, 'fertil') || str_contains($texto, 'npk')) {
            return 'fertilización';
        }
        if (str_contains($texto, 'fung') || str_contains($texto, 'herb') || str_contains($texto, 'plaga')) {
            return 'control de plagas';
        }

        return 'fertilización';
    }

    private function resolverTipoActividad(string $nombre): ?TipoActividad
    {
        return TipoActividad::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($nombre))])->first();
    }

    private function estadoId(string $nombre): ?int
    {
        $id = EstadoLoteTipo::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($nombre))])->value('estadolotetipoid');

        return $id ? (int) $id : null;
    }

    private function prioridadMediaId(): ?int
    {
        return Prioridad::whereRaw('LOWER(nombre) = ?', ['media'])->value('prioridadid')
            ?? Prioridad::query()->value('prioridadid');
    }

    private function prioridadAltaId(): ?int
    {
        return Prioridad::whereRaw('LOWER(nombre) = ?', ['alta'])->value('prioridadid')
            ?? $this->prioridadMediaId();
    }

    private function fetchClimaApi(float $lat, float $lng, string $apiKey): ?array
    {
        try {
            $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => $lat,
                'lon' => $lng,
                'appid' => $apiKey,
                'units' => 'metric',
                'lang' => 'es',
            ]);

            if (! $response->successful()) {
                return $this->climaEstimadoSantaCruz();
            }

            $data = $response->json();

            return [
                'temperatura' => round($data['main']['temp'] ?? 28, 1),
                'humedad' => $data['main']['humidity'] ?? 60,
                'lluvia' => $data['rain']['1h'] ?? $data['rain']['3h'] ?? 0,
                'viento' => round(($data['wind']['speed'] ?? 0) * 3.6, 1),
                'presion' => $data['main']['pressure'] ?? 1013,
                'descripcion' => ucfirst($data['weather'][0]['description'] ?? 'Parcialmente nublado'),
                'icono' => $data['weather'][0]['icon'] ?? '02d',
            ];
        } catch (\Exception $e) {
            Log::warning('Clima API por lote: '.$e->getMessage());

            return $this->climaEstimadoSantaCruz();
        }
    }

    private function climaEstimadoSantaCruz(): array
    {
        return [
            'temperatura' => 28 + random_int(0, 4),
            'humedad' => 55 + random_int(0, 20),
            'lluvia' => 0,
            'viento' => 10 + random_int(0, 5),
            'presion' => 1012,
            'descripcion' => 'Condición estimada Santa Cruz',
            'icono' => '02d',
        ];
    }
}
