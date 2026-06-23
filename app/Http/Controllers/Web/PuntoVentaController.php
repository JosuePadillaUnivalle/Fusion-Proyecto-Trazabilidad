<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\PuntoVenta;
use App\Models\Usuario;
use App\Services\PuntoVentaAlmacenService;
use App\Support\CuentaEstado;
use App\Support\PuntoVentaAccess;
use App\Support\PuntoVentaEliminacionCatalogo;
use App\Support\UsuarioRol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PuntoVentaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = PuntoVentaAccess::scopePuntosDelUsuario(
            PuntoVenta::query()->with(['minorista', 'almacen']),
            $user
        );

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $query->where(function ($w) use ($term) {
                $w->where('nombre', 'like', "%{$term}%")
                    ->orWhere('direccion', 'like', "%{$term}%")
                    ->orWhereHas('minorista', function ($m) use ($term) {
                        $m->where('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $puntos = $query->orderByDesc('puntoventaid')->get();
        $esAdmin = UsuarioRol::esAdminGlobal($user);

        return view('punto_venta.puntos.index', compact('puntos', 'esAdmin'));
    }

    public function create(Request $request): View
    {
        $minoristas = $this->minoristasParaSelector($request->user());
        $puntosMapa = $this->puntosParaMapa($request->user());

        return view('punto_venta.puntos.create', compact('minoristas', 'puntosMapa'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $esAdmin = UsuarioRol::esAdminGlobal($user);

        if ($esAdmin && $request->input('usuarioid') === '') {
            $request->merge(['usuarioid' => null]);
        }

        $rules = [
            'nombre' => 'required|string|max:150',
            'direccion' => 'nullable|string|max:500',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'observaciones' => 'nullable|string|max:1000',
        ];

        if ($esAdmin) {
            $rules['usuarioid'] = 'required|integer|exists:usuario,usuarioid';
        }

        $data = $request->validate($rules);

        if ($esAdmin) {
            $responsable = Usuario::query()->findOrFail((int) $data['usuarioid']);
            if (! UsuarioRol::esMinorista($responsable)) {
                return back()
                    ->withInput()
                    ->withErrors(['usuarioid' => 'Debe asignar un minorista válido. El administrador no puede ser dueño del punto de venta.']);
            }
            $usuarioidResponsable = (int) $responsable->usuarioid;
        } else {
            $usuarioidResponsable = (int) $user->usuarioid;
        }

        $puntoVenta = PuntoVenta::create([
            'usuarioid' => $usuarioidResponsable,
            'nombre' => $data['nombre'],
            'direccion' => $data['direccion'] ?? null,
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'activo' => true,
            'fechacreacion' => now(),
        ]);

        app(PuntoVentaAlmacenService::class)->crearAlmacenParaPuntoVenta($puntoVenta);

        return redirect()
            ->route('punto-venta.puntos.show', $puntoVenta)
            ->with('success', 'Punto de venta registrado correctamente.');
    }

    public function show(PuntoVenta $punto): View
    {
        abort_unless(PuntoVentaAccess::puedeVerPunto(auth()->user(), $punto), 403);

        $punto->load(['minorista', 'almacen']);
        $insumos = app(PuntoVentaAlmacenService::class)->insumosEnPuntoVenta($punto);
        $pedidos = $punto->pedidosDistribucion()
            ->with('detalles')
            ->orderByDesc('pedidodistribucionid')
            ->limit(10)
            ->get();

        return view('punto_venta.puntos.show', compact('punto', 'insumos', 'pedidos'));
    }

    public function edit(PuntoVenta $punto): View
    {
        abort_unless(PuntoVentaAccess::puedeEditarPunto(auth()->user(), $punto), 403);

        $minoristas = $this->minoristasParaSelector(auth()->user());
        $puntosMapa = $this->puntosParaMapa(auth()->user(), $punto->puntoventaid);

        return view('punto_venta.puntos.edit', compact('punto', 'minoristas', 'puntosMapa'));
    }

    public function update(Request $request, PuntoVenta $punto): RedirectResponse
    {
        abort_unless(PuntoVentaAccess::puedeEditarPunto(auth()->user(), $punto), 403);

        $esAdmin = UsuarioRol::esAdminGlobal($request->user());

        if ($esAdmin && $request->input('usuarioid') === '') {
            $request->merge(['usuarioid' => null]);
        }

        $rules = [
            'nombre' => 'required|string|max:150',
            'direccion' => 'nullable|string|max:500',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'observaciones' => 'nullable|string|max:1000',
            'activo' => 'sometimes|boolean',
        ];

        if ($esAdmin) {
            $rules['usuarioid'] = 'required|integer|exists:usuario,usuarioid';
        }

        $data = $request->validate($rules);

        if ($esAdmin) {
            $responsable = Usuario::query()->findOrFail((int) $data['usuarioid']);
            if (! UsuarioRol::esMinorista($responsable)) {
                return back()
                    ->withInput()
                    ->withErrors(['usuarioid' => 'Debe asignar un minorista válido.']);
            }
            $usuarioidResponsable = (int) $responsable->usuarioid;
        } else {
            $usuarioidResponsable = $punto->usuarioid;
        }

        $punto->update([
            'nombre' => $data['nombre'],
            'direccion' => $data['direccion'] ?? null,
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'activo' => $request->boolean('activo', true),
            'usuarioid' => $usuarioidResponsable,
        ]);

        if ($punto->almacen) {
            $punto->almacen->update([
                'nombre' => 'Almacén — '.$punto->nombre,
                'ubicacion' => $punto->direccion,
            ]);
        }

        return redirect()
            ->route('punto-venta.puntos.show', $punto)
            ->with('success', 'Punto de venta actualizado.');
    }

    public function destroy(PuntoVenta $punto): RedirectResponse
    {
        abort_unless(PuntoVentaAccess::puedeEditarPunto(auth()->user(), $punto), 403);

        $eval = PuntoVentaEliminacionCatalogo::evaluar($punto);
        if (! $eval['ok']) {
            return back()->with([
                'error' => $eval['mensaje'],
                'error_modal' => true,
                'error_modal_titulo' => $eval['titulo'],
            ]);
        }

        PuntoVentaEliminacionCatalogo::cancelarPedidosPendientes($punto);
        PuntoVentaEliminacionCatalogo::eliminarHistorialAsociado($punto);

        if ($punto->almacenid) {
            Insumo::query()->where('almacenid', $punto->almacenid)->delete();
            $punto->almacen?->delete();
        }

        try {
            $punto->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->with([
                'error' => 'No se pudo eliminar «'.$punto->nombre.'» porque aún hay registros vinculados en el sistema.',
                'error_modal' => true,
                'error_modal_titulo' => 'No se puede eliminar',
            ]);
        }

        return redirect()
            ->route('punto-venta.puntos.index')
            ->with('success', 'Punto de venta eliminado.');
    }

    /** @return \Illuminate\Support\Collection<int, Usuario> */
    private function minoristasParaSelector(?Usuario $user)
    {
        if (UsuarioRol::esAdminGlobal($user)) {
            return Usuario::query()
                ->where('role', 'minorista')
                ->where(function ($q) {
                    $q->whereNull('estado_cuenta')
                        ->orWhere('estado_cuenta', CuentaEstado::APROBADO);
                })
                ->where('activo', true)
                ->orderBy('nombre')
                ->orderBy('apellido')
                ->get();
        }

        return collect([$user])->filter();
    }

    /**
     * @return list<array{id: int, nombre: string, direccion: ?string, lat: float, lng: float, usuarioid: ?int}>
     */
    private function puntosParaMapa(?Usuario $user, ?int $excluirPuntoId = null): array
    {
        $query = PuntoVentaAccess::scopePuntosDelUsuario(PuntoVenta::query(), $user)
            ->whereNotNull('latitud')
            ->whereNotNull('longitud');

        if ($excluirPuntoId !== null) {
            $query->where('puntoventaid', '!=', $excluirPuntoId);
        }

        return $query
            ->orderBy('nombre')
            ->get(['puntoventaid', 'nombre', 'direccion', 'latitud', 'longitud', 'usuarioid'])
            ->map(fn (PuntoVenta $p) => [
                'id' => (int) $p->puntoventaid,
                'nombre' => (string) $p->nombre,
                'direccion' => $p->direccion,
                'lat' => (float) $p->latitud,
                'lng' => (float) $p->longitud,
                'usuarioid' => $p->usuarioid !== null ? (int) $p->usuarioid : null,
            ])
            ->values()
            ->all();
    }
}
