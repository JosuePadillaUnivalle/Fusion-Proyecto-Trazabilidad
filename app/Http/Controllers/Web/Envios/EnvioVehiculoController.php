<?php

namespace App\Http\Controllers\Web\Envios;

use App\Exceptions\EliminacionBloqueadaException;
use App\Http\Controllers\Controller;
use App\Models\TipoTransporte;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Services\VehiculoEliminacionService;
use App\Services\VehiculoEmpaqueCapacidadService;
use App\Services\VehiculoFlotaEstadoService;
use App\Support\EstadoVehiculoCatalogo;
use App\Support\TipoVehiculoCatalogo;
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

        $q = Vehiculo::query()->with(['tipoVehiculo', 'estadoVehiculo', 'tiposTransporte']);

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

        $vehiculos = $q
            ->orderByRaw("CASE COALESCE(ambito_flota, 'agricola') WHEN 'agricola' THEN 1 WHEN 'planta' THEN 2 WHEN 'mayorista' THEN 3 ELSE 9 END")
            ->orderBy('placa')
            ->paginate(15)->withQueryString();
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
            'tiposCatalogo' => $this->tiposCatalogoOrdenados(),
            'mapaEnRuta' => $mapaEnRuta,
        ]);
    }

    public function create(): View
    {
        return view('envios.vehiculos.create', $this->datosFormulario());
    }

    public function show(Vehiculo $vehiculo): View
    {
        $vehiculo->load(['tipoVehiculo.tiposTransporte', 'tiposTransporte', 'estadoVehiculo']);

        $estadoSvc = app(VehiculoFlotaEstadoService::class);
        $capacidadResumen = app(VehiculoEmpaqueCapacidadService::class)->resumenParaVehiculo($vehiculo);

        return view('envios.vehiculos.show', [
            'vehiculo' => $vehiculo,
            'tiposCatalogo' => $this->tiposCatalogoOrdenados(),
            'estadoVisual' => $estadoSvc->codigoVisual($vehiculo),
            'estadoLabel' => $estadoSvc->etiquetaVisual($vehiculo),
            'badgeEstado' => $estadoSvc->badgeClaseVisual($vehiculo),
            'rutaTiempoReal' => $estadoSvc->rutaTiempoRealParaVehiculo($vehiculo),
            'capacidadResumen' => $capacidadResumen,
        ]);
    }

    public function edit(Vehiculo $vehiculo): View
    {
        $vehiculo->load(['tipoVehiculo.tiposTransporte', 'tiposTransporte', 'estadoVehiculo']);

        return view('envios.vehiculos.edit', array_merge(
            $this->datosFormulario(),
            [
                'vehiculo' => $vehiculo,
                'tiposCatalogo' => $this->tiposCatalogoOrdenados(),
            ]
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $idsTransporte = $this->idsTransporte($request);
        $vehiculo = Vehiculo::create($data);
        $vehiculo->tiposTransporte()->sync($idsTransporte);

        return redirect()
            ->route('envios.vehiculos.show', $vehiculo)
            ->with('success', 'Vehículo registrado correctamente.');
    }

    public function update(Request $request, Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->update($this->validar($request, $vehiculo));
        $vehiculo->tiposTransporte()->sync($this->idsTransporte($request));

        return redirect()
            ->route('envios.vehiculos.show', $vehiculo)
            ->with('success', 'Vehículo actualizado.');
    }

    public function destroy(Vehiculo $vehiculo): RedirectResponse
    {
        try {
            app(VehiculoEliminacionService::class)->eliminar($vehiculo);
        } catch (EliminacionBloqueadaException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('envios.vehiculos')
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
            'largo_m_override' => 'nullable|numeric|min:0|max:99',
            'ancho_m_override' => 'nullable|numeric|min:0|max:99',
            'alto_m_override' => 'nullable|numeric|min:0|max:99',
            'tipotransporteid' => 'required|integer|exists:tipo_transporte,tipotransporteid',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $data['capacidad_kg_override'] = filled($data['capacidad_kg_override'] ?? null)
            ? $data['capacidad_kg_override']
            : null;
        $data['capacidad_m3_override'] = filled($data['capacidad_m3_override'] ?? null)
            ? $data['capacidad_m3_override']
            : null;
        foreach (['largo_m_override', 'ancho_m_override', 'alto_m_override'] as $campoDim) {
            $data[$campoDim] = filled($data[$campoDim] ?? null) ? $data[$campoDim] : null;
        }

        $tipo = TipoVehiculo::query()->find($data['tipovehiculoid']);
        if ($tipo) {
            $data['capacidad_kg_override'] = $this->overrideSiDifiere(
                $data['capacidad_kg_override'] ?? null,
                $tipo->capacidad_kg
            );
            $data['capacidad_m3_override'] = $this->overrideSiDifiere(
                $data['capacidad_m3_override'] ?? null,
                $tipo->capacidad_m3
            );
            $data['largo_m_override'] = $this->overrideSiDifiere($data['largo_m_override'] ?? null, $tipo->largo_m);
            $data['ancho_m_override'] = $this->overrideSiDifiere($data['ancho_m_override'] ?? null, $tipo->ancho_m);
            $data['alto_m_override'] = $this->overrideSiDifiere($data['alto_m_override'] ?? null, $tipo->alto_m);
        }

        if ($vehiculo === null) {
            $data['estadovehiculoid'] = EstadoVehiculoCatalogo::idOperativo();
        }

        return $data;
    }

    private function overrideSiDifiere(mixed $valor, mixed $valorTipo): ?float
    {
        if (! filled($valor)) {
            return null;
        }

        $valor = (float) $valor;

        if (! filled($valorTipo)) {
            return $valor;
        }

        if (abs($valor - (float) $valorTipo) < 0.0001) {
            return null;
        }

        return $valor;
    }

    /** @return array<string, mixed> */
    private function datosFormulario(): array
    {
        return [
            'tipos' => $this->tiposCatalogoOrdenados(),
            'catalogoTransporte' => TipoTransporte::query()->orderBy('nombre')->get(),
        ];
    }

    /** @return \Illuminate\Support\Collection<int, TipoVehiculo> */
    private function tiposCatalogoOrdenados(): \Illuminate\Support\Collection
    {
        return TipoVehiculoCatalogo::ordenar(
            TipoVehiculo::with('tiposTransporte')->orderBy('nombre')->get()
        );
    }

    /** @return list<int> */
    private function idsTransporte(Request $request): array
    {
        $id = (int) $request->input('tipotransporteid', 0);

        return $id > 0 ? [$id] : [];
    }
}
