<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\RutaDistribucion;
use App\Services\SimulacionRutaService;
use App\Services\TrasladoPlantaMayoristaService;
use App\Support\AlmacenAmbito;
use App\Support\MayoristaAccess;
use App\Support\RutaDistribucionCatalogo;
use App\Support\SimulacionRutaCatalogo;
use App\Support\UbicacionGpsParser;
use App\Support\UsuarioRol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrasladoPlantaMayoristaController extends Controller
{
    public function __construct(
        private readonly TrasladoPlantaMayoristaService $traslados
    ) {}

    public function create(): View
    {
        return redirect()->route('pedidos.create', ['destino' => 'mayorista']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($this->puedeVerListado($user), 403);

        $query = RutaDistribucion::query()
            ->where('tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA)
            ->with(['almacenPlantaOrigen', 'almacenMayoristaDestino', 'transportista', 'detallesTraslado'])
            ->orderByDesc('rutadistribucionid');

        if (UsuarioRol::esMayorista($user) && ! UsuarioRol::esAdminGlobal($user)) {
            $ids = MayoristaAccess::idsAlmacenesMayorista($user);
            $query->whereIn('almacen_mayorista_destinoid', $ids);
        }

        $traslados = $query->paginate(15)->withQueryString();
        $pendientesCount = (clone $query)->where('estado', RutaDistribucionCatalogo::ESTADO_PENDIENTE_APROBACION)->count();

        $esVistaMayorista = $request->routeIs('almacen-mayorista.traslados-planta.*');

        return view('logistica.traslados-planta.index', [
            'traslados' => $traslados,
            'pendientesCount' => $pendientesCount,
            'esVistaMayorista' => $esVistaMayorista,
            'rutaPrefijo' => $esVistaMayorista ? 'almacen-mayorista.traslados-planta' : 'logistica.traslados-planta',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->puedeGestionarPlanta(), 403);

        $data = $request->validate([
            'almacen_planta_origenid' => 'required|integer|exists:almacen,almacenid',
            'almacen_mayorista_destinoid' => 'required|integer|exists:almacen,almacenid',
            'transportista_usuarioid' => 'required|integer|exists:usuario,usuarioid',
            'vehiculoid' => 'required|integer|exists:vehiculo,vehiculoid',
            'nombre' => 'nullable|string|max:200',
            'costo_bs' => 'required|numeric|min:1',
            'detalles' => 'required|array|min:1',
            'detalles.*.insumoid' => 'required|integer|exists:insumo,insumoid',
            'detalles.*.insumo_presentacionid' => 'nullable|integer|exists:insumo_presentacion,insumo_presentacionid',
            'detalles.*.inventario_presentacion_loteid' => 'nullable|integer|exists:inventario_presentacion_lote,inventario_presentacion_loteid',
            'detalles.*.cantidad_unidades' => 'nullable|numeric|min:0.01',
            'detalles.*.cantidad' => 'nullable|numeric|min:0.01',
            'detalles.*.observaciones' => 'nullable|string|max:255',
        ]);
        $planta = Almacen::query()->findOrFail((int) $data['almacen_planta_origenid']);
        $mayorista = Almacen::query()->findOrFail((int) $data['almacen_mayorista_destinoid']);

        try {
            $ruta = $this->traslados->crear(
                $planta,
                $mayorista,
                (int) $data['transportista_usuarioid'],
                isset($data['vehiculoid']) ? (int) $data['vehiculoid'] : null,
                (int) auth()->id(),
                $data['detalles'],
                $data['nombre'] ?? null,
                (float) $data['costo_bs']
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('pedidos.create', ['destino' => 'mayorista'])
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('logistica.traslados-planta.show', $ruta)
            ->with('success', 'Traslado registrado. El mayorista recibirá una alerta para aceptar o rechazar la recepción.');
    }

    public function show(Request $request, RutaDistribucion $ruta): View
    {
        abort_unless($ruta->esTrasladoPlantaMayorista(), 404);
        $this->autorizarVer($ruta);

        $ruta->load([
            'transportista',
            'vehiculo.tipoVehiculo',
            'almacenPlantaOrigen',
            'almacenMayoristaDestino',
            'paradas',
            'detallesTraslado.insumo.unidadMedida',
            'aprobadoPor',
        ]);

        $esVistaMayorista = $request->routeIs('almacen-mayorista.traslados-planta.*');
        $user = auth()->user();
        $pendienteAprobacion = RutaDistribucionCatalogo::pendienteAprobacionMayorista($ruta);
        $puedeGestionarMayorista = MayoristaAccess::puedeGestionarTraslado($user, $ruta);

        return view('logistica.traslados-planta.show', [
            'ruta' => $ruta,
            'trayecto' => $this->traslados->trayectoTexto($ruta),
            'simulacionActiva' => SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta),
            'puedeEmpezar' => SimulacionRutaCatalogo::usuarioPuedeEmpezarDistribucion($user, $ruta),
            'pendienteAprobacion' => $pendienteAprobacion,
            'puedeGestionarMayorista' => $puedeGestionarMayorista,
            'rutaPrefijo' => $esVistaMayorista ? 'almacen-mayorista.traslados-planta' : 'logistica.traslados-planta',
            'volverUrl' => $esVistaMayorista
                ? route('almacen-mayorista.traslados-planta.index')
                : route('logistica.asignaciones.index'),
        ]);
    }

    public function aceptar(RutaDistribucion $ruta): RedirectResponse
    {
        abort_unless($ruta->esTrasladoPlantaMayorista(), 404);
        abort_unless(MayoristaAccess::puedeGestionarTraslado(auth()->user(), $ruta), 403);

        try {
            $this->traslados->aceptar($ruta, auth()->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $rutaPrefijo = request()->routeIs('almacen-mayorista.traslados-planta.*')
            ? 'almacen-mayorista.traslados-planta'
            : 'logistica.traslados-planta';

        return redirect()
            ->route($rutaPrefijo.'.show', $ruta)
            ->with('success', 'Traslado aceptado. El transportista puede marcar en ruta cuando salga de planta.');
    }

    public function rechazar(Request $request, RutaDistribucion $ruta): RedirectResponse
    {
        abort_unless($ruta->esTrasladoPlantaMayorista(), 404);
        abort_unless(MayoristaAccess::puedeGestionarTraslado(auth()->user(), $ruta), 403);

        $data = $request->validate(['motivo_rechazo' => 'nullable|string|max:500']);

        try {
            $this->traslados->rechazar($ruta, auth()->user(), $data['motivo_rechazo'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('almacen-mayorista.traslados-planta.index')
            ->with('success', 'Traslado rechazado. La planta fue notificada.');
    }

    public function empezarRuta(RutaDistribucion $ruta, SimulacionRutaService $simulacion): RedirectResponse
    {
        abort_unless($ruta->esTrasladoPlantaMayorista(), 404);

        $user = auth()->user();
        if (! $this->puedeGestionarPlanta()
            && (int) $ruta->transportista_usuarioid !== (int) $user?->usuarioid) {
            abort(403);
        }

        if (! RutaDistribucionCatalogo::puedeEmpezarTrasladoPlanta($ruta)) {
            return back()->with('error', 'El traslado debe ser aceptado por el mayorista antes de marcar en ruta.');
        }

        try {
            $simulacion->empezarDistribucion($ruta);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $redirect = redirect()
            ->route('logistica.traslados-planta.show', $ruta)
            ->with('success', 'Traslado marcado en ruta. El recorrido simulado está en marcha hacia el almacén mayorista.');

        if ($this->puedeGestionarPlanta()) {
            $redirect->with('info', 'Puede seguir el vehículo en Logística → Ruta en tiempo real.');
        }

        return $redirect;
    }

    private function puedeGestionarPlanta(): bool
    {
        $user = auth()->user();

        return $user && (
            UsuarioRol::esAdminGlobal($user)
            || UsuarioRol::esJefePlanta($user)
            || $user->can('asignaciones.update')
        );
    }

    private function puedeVerListado($user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->puedeGestionarPlanta()
            || UsuarioRol::esMayorista($user)
            || UsuarioRol::esAdminGlobal($user);
    }

    private function autorizarVer(RutaDistribucion $ruta): void
    {
        $user = auth()->user();
        if ($this->puedeGestionarPlanta()) {
            return;
        }
        if (MayoristaAccess::puedeGestionarTraslado($user, $ruta)) {
            return;
        }
        if (UsuarioRol::esTransportista($user) && (int) $ruta->transportista_usuarioid === (int) $user->usuarioid) {
            return;
        }

        abort(403);
    }
}
