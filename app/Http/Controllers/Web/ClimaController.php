<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Clima;
use App\Models\Lote;
use App\Services\WeatherOpenWeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClimaController extends Controller
{
    public function __construct(
        private readonly WeatherOpenWeatherService $weather
    ) {}

    public function index(Request $request): View
    {
        $historial = $this->historialQuery($request)->paginate(15)->withQueryString();

        $weatherData = $this->weather->resolveForDisplay();
        $cargarClimaAsync = (bool) ($weatherData['needs_api_refresh'] ?? false);
        unset($weatherData['needs_api_refresh']);

        $lotesFiltro = Lote::query()->orderBy('nombre')->get(['loteid', 'nombre']);

        return view('climas.index', compact(
            'historial',
            'weatherData',
            'cargarClimaAsync',
            'lotesFiltro'
        ));
    }

    public function datosTiempo(Request $request): JsonResponse
    {
        $data = $this->weather->resolveWithApi($request->boolean('refresh'));
        unset($data['needs_api_refresh']);

        if (! empty($data['actual']) && ($data['fuente'] ?? '') === 'openweather') {
            $this->guardarClimaDesdeActual($data['actual']);
        }

        return response()->json($data);
    }

    private function historialQuery(Request $request)
    {
        $query = Clima::query()
            ->with('lote:loteid,nombre')
            ->where('fecha', '>=', now()->subDays(30))
            ->orderByDesc('fecha');

        if ($request->filled('loteid')) {
            $query->where('loteid', (int) $request->loteid);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('temp_min')) {
            $query->where('temperatura', '>=', (float) $request->temp_min);
        }

        if ($request->filled('temp_max')) {
            $query->where('temperatura', '<=', (float) $request->temp_max);
        }

        if ($request->filled('buscar')) {
            $buscar = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($buscar) {
                $q->where('observaciones', 'like', $buscar)
                    ->orWhere('descripcion', 'like', $buscar)
                    ->orWhereHas('lote', fn ($l) => $l->where('nombre', 'like', $buscar));
            });
        }

        return $query;
    }

    private function guardarClimaDesdeActual(?array $actual): void
    {
        if (! $actual) {
            return;
        }

        if (Clima::where('fecha', '>=', now()->subHours(4))->exists()) {
            return;
        }

        $loteId = Lote::query()->value('loteid');
        if (! $loteId) {
            return;
        }

        Clima::create([
            'loteid' => $loteId,
            'fecha' => now(),
            'temperatura' => $actual['temperatura'] ?? null,
            'humedad' => $actual['humedad'] ?? null,
            'lluvia' => $actual['lluvia'] ?? 0,
            'viento' => $actual['viento_kmh'] ?? null,
            'presion' => $actual['presion'] ?? null,
            'descripcion' => $actual['descripcion'] ?? null,
            'icono' => $actual['icono'] ?? null,
            'observaciones' => isset($actual['descripcion']) ? ucfirst($actual['descripcion']) : null,
        ]);
    }
}
