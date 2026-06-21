<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PuntoVenta;
use App\Models\Usuario;
use App\Services\PuntoVentaAlmacenService;
use App\Support\CuentaEstado;
use App\Support\PuntoVentaAccess;
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

        return view('punto_venta.puntos.create', compact('minoristas'));
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
            $rules['usuarioid'] = 'nullable|integer|exists:usuario,usuarioid';
        }

        $data = $request->validate($rules);

        $usuarioidResponsable = $esAdmin
            ? (int) ($data['usuarioid'] ?? $user->usuarioid)
            : (int) $user->usuarioid;

        if ($esAdmin && $usuarioidResponsable !== (int) $user->usuarioid) {
            $responsable = Usuario::query()->find($usuarioidResponsable);
            if (! $responsable || ! UsuarioRol::esMinorista($responsable)) {
                return back()
                    ->withInput()
                    ->withErrors(['usuarioid' => 'Seleccione un minorista válido o deje la asignación por defecto.']);
            }
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

        return view('punto_venta.puntos.edit', compact('punto', 'minoristas'));
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
            $rules['usuarioid'] = 'nullable|integer|exists:usuario,usuarioid';
        }

        $data = $request->validate($rules);

        $usuarioidResponsable = $esAdmin
            ? (int) ($data['usuarioid'] ?? $punto->usuarioid)
            : $punto->usuarioid;

        if ($esAdmin && $usuarioidResponsable !== (int) $request->user()->usuarioid) {
            $responsable = Usuario::query()->find($usuarioidResponsable);
            if (! $responsable || ! UsuarioRol::esMinorista($responsable)) {
                return back()
                    ->withInput()
                    ->withErrors(['usuarioid' => 'Seleccione un minorista válido.']);
            }
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

        if ($punto->pedidosDistribucion()->whereNotIn('estado', ['recibido', 'rechazado', 'cancelado'])->exists()) {
            return back()->with('error', 'No se puede eliminar: tiene pedidos de distribución activos.');
        }

        $punto->delete();

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
}
