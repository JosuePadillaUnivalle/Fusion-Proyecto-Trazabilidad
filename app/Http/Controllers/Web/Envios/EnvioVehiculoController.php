<?php

namespace App\Http\Controllers\Web\Envios;

use App\Http\Controllers\Controller;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Services\VehiculoFlotaEstadoService;
use App\Support\EstadoVehiculoCatalogo;
use App\Support\TransportistaFlotaCatalogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnvioVehiculoController extends Controller
{
    public function __construct()
    {
        $this->middleware('action.permission:vehiculos,read')->only(['index', 'show']);
        $this->middleware('action.permission:vehiculos,create')->only(['create', 'store']);
        $this->middleware('action.permission:vehiculos,update')->only(['edit', 'update', 'toggleMantenimiento']);
        $this->middleware('action.permission:vehiculos,delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $estadoSvc = app(VehiculoFlotaEstadoService::class);

        $q = Vehiculo::query()->with(['tipoVehiculo', 'estadoVehiculo']);

        if ($request->filled('buscar')) {
            $b = '%'.trim((string) $request->buscar).'%';
            $q->where(function ($query) use ($b) {
                $query->where('placa', 'like', $b)
                    ->orWhere('marca', 'like', $b)
                    ->orWhere('modelo', 'like', $b);
            });
        }

        $filtroEstado = $request->string('estado')->toString();
        if ($filtroEstado !== '' && array_key_exists($filtroEstado, $estadoSvc->opcionesFiltro())) {
            $estadoSvc->aplicarFiltroVisual($q, $filtroEstado);
        }

        if ($request->filled('ambito_flota') && in_array($request->string('ambito_flota')->toString(), TransportistaFlotaCatalogo::valores(), true)) {
            $q->where('ambito_flota', $request->string('ambito_flota')->toString());
        }

        $vehiculos = $q->orderBy('placa')->paginate(15)->withQueryString();
        $conteoEstados = $estadoSvc->contarPorEstadoVisual();
        $mapaEnRuta = $estadoSvc->mapaEnRuta();

        return view('envios.vehiculos.index', [
            'vehiculos' => $vehiculos,
            'stats' => [
                'total' => Vehiculo::count(),
                'operativo' => $conteoEstados['operativo'],
                'mantenimiento' => $conteoEstados['mantenimiento'],
                'en_ruta' => $conteoEstados['en_ruta'],
            ],
            'estadosFiltro' => $estadoSvc->opcionesFiltro(),
            'tiposCatalogo' => TipoVehiculo::orderBy('nombre')->get(),
            'mapaEnRuta' => $mapaEnRuta,
        ]);
    }

    public function create(): View
    {
        return view('envios.vehiculos.create', [
            'tipos' => TipoVehiculo::orderBy('nombre')->get(),
        ]);
    }

    public function show(Vehiculo $vehiculo): View
    {
        $vehiculo->load(['tipoVehiculo', 'estadoVehiculo']);

        $estadoSvc = app(VehiculoFlotaEstadoService::class);

        return view('envios.vehiculos.show', [
            'vehiculo' => $vehiculo,
            'tiposCatalogo' => TipoVehiculo::orderBy('nombre')->get(),
            'estadoVisual' => $estadoSvc->codigoVisual($vehiculo),
            'estadoLabel' => $estadoSvc->etiquetaVisual($vehiculo),
            'badgeEstado' => $estadoSvc->badgeClaseVisual($vehiculo),
            'rutaTiempoReal' => $estadoSvc->rutaTiempoRealParaVehiculo($vehiculo),
        ]);
    }

    public function edit(Vehiculo $vehiculo): View
    {
        $vehiculo->load(['tipoVehiculo', 'estadoVehiculo']);

        return view('envios.vehiculos.edit', [
            'vehiculo' => $vehiculo,
            'tipos' => TipoVehiculo::orderBy('nombre')->get(),
            'tiposCatalogo' => TipoVehiculo::orderBy('nombre')->get(),
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

    public function toggleMantenimiento(Vehiculo $vehiculo): RedirectResponse
    {
        if (EstadoVehiculoCatalogo::enBaja($vehiculo)) {
            return back()->with('error', 'Un vehículo de baja no puede cambiar de estado desde aquí.');
        }

        if (app(VehiculoFlotaEstadoService::class)->estaEnRuta($vehiculo)) {
            return back()->with('error', 'No puede poner en mantenimiento un vehículo que está en ruta.');
        }

        $idMantenimiento = EstadoVehiculoCatalogo::idMantenimiento();
        $idOperativo = EstadoVehiculoCatalogo::idOperativo();

        if (! $idMantenimiento || ! $idOperativo) {
            return back()->with('error', 'No están configurados los estados operativo/mantenimiento en el catálogo.');
        }

        $enMantenimiento = EstadoVehiculoCatalogo::enMantenimiento($vehiculo);
        $vehiculo->estadovehiculoid = $enMantenimiento ? $idOperativo : $idMantenimiento;
        $vehiculo->save();

        $mensaje = $enMantenimiento
            ? 'El vehículo '.$vehiculo->placa.' quedó operativo y disponible para asignación.'
            : 'El vehículo '.$vehiculo->placa.' quedó en mantenimiento. No podrá usarse en envíos ni rutas.';

        return back()->with('success', $mensaje);
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
            'activo' => 'nullable|boolean',
            'ambito_flota' => 'required|in:'.implode(',', TransportistaFlotaCatalogo::valores()),
            'capacidad_kg_override' => 'nullable|numeric|min:0|max:999999',
            'capacidad_m3_override' => 'nullable|numeric|min:0|max:999999',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $data['capacidad_kg_override'] = filled($data['capacidad_kg_override'] ?? null)
            ? $data['capacidad_kg_override']
            : null;
        $data['capacidad_m3_override'] = filled($data['capacidad_m3_override'] ?? null)
            ? $data['capacidad_m3_override']
            : null;

        if ($vehiculo === null) {
            $data['estadovehiculoid'] = EstadoVehiculoCatalogo::idOperativo();
        }

        return $data;
    }
}
