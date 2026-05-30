<?php

namespace App\Http\Controllers\Web\Envios;

use App\Http\Controllers\Controller;
use App\Models\EstadoVehiculo;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnvioVehiculoController extends Controller
{
    public function __construct()
    {
        $this->middleware('action.permission:vehiculos,read')->only(['index', 'show']);
        $this->middleware('action.permission:vehiculos,create')->only(['create', 'store']);
        $this->middleware('action.permission:vehiculos,update')->only(['edit', 'update']);
        $this->middleware('action.permission:vehiculos,delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $q = Vehiculo::query()->with(['tipoVehiculo', 'estadoVehiculo']);

        if ($request->filled('buscar')) {
            $b = '%'.trim((string) $request->buscar).'%';
            $q->where(function ($query) use ($b) {
                $query->where('placa', 'like', $b)
                    ->orWhere('marca', 'like', $b)
                    ->orWhere('modelo', 'like', $b);
            });
        }

        if ($request->filled('estado')) {
            $estado = EstadoVehiculo::whereRaw('LOWER(nombre) LIKE ?', ['%'.strtolower($request->estado).'%'])->value('estadovehiculoid');
            if ($estado) {
                $q->where('estadovehiculoid', $estado);
            }
        }

        $vehiculos = $q->orderBy('placa')->paginate(15)->withQueryString();

        return view('envios.vehiculos.index', [
            'vehiculos' => $vehiculos,
            'stats' => ['total' => Vehiculo::count()],
            'estadosFiltro' => EstadoVehiculo::orderBy('nombre')->pluck('nombre'),
        ]);
    }

    public function create(): View
    {
        return view('envios.vehiculos.create', [
            'tipos' => TipoVehiculo::orderBy('nombre')->get(),
            'estados' => EstadoVehiculo::orderBy('nombre')->get(),
        ]);
    }

    public function show(Vehiculo $vehiculo): View
    {
        $vehiculo->load(['tipoVehiculo', 'estadoVehiculo']);

        return view('envios.vehiculos.show', compact('vehiculo'));
    }

    public function edit(Vehiculo $vehiculo): View
    {
        $vehiculo->load(['tipoVehiculo', 'estadoVehiculo']);

        return view('envios.vehiculos.edit', [
            'vehiculo' => $vehiculo,
            'tipos' => TipoVehiculo::orderBy('nombre')->get(),
            'estados' => EstadoVehiculo::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $vehiculo = Vehiculo::create($data);

        return redirect()
            ->route('envios.vehiculos.show', $vehiculo)
            ->with('success', 'Vehículo registrado correctamente.');
    }

    public function update(Request $request, Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->update($this->validar($request, $vehiculo));

        return redirect()
            ->route('envios.vehiculos.show', $vehiculo)
            ->with('success', 'Vehículo actualizado.');
    }

    public function destroy(Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->delete();

        return redirect()
            ->route('envios.vehiculos.index')
            ->with('success', 'Vehículo eliminado.');
    }

    private function validar(Request $request, ?Vehiculo $vehiculo = null): array
    {
        $data = $request->validate([
            'placa' => 'required|string|max:20|unique:vehiculo,placa'.($vehiculo ? ','.$vehiculo->vehiculoid.',vehiculoid' : ''),
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'anio' => 'nullable|integer|min:1980|max:2100',
            'color' => 'nullable|string|max:50',
            'tipovehiculoid' => 'required|integer|exists:tipo_vehiculo,tipovehiculoid',
            'estadovehiculoid' => 'nullable|integer|exists:estado_vehiculo,estadovehiculoid',
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);

        return $data;
    }
}
