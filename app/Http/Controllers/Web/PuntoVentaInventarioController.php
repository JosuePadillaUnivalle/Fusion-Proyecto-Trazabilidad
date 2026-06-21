<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\PuntoVenta;
use App\Support\PuntoVentaAccess;
use App\Support\TrazabilidadProductoPdvService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PuntoVentaInventarioController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $puntos = PuntoVentaAccess::scopePuntosDelUsuario(
            PuntoVenta::query()->where('activo', true)->orderBy('nombre'),
            $user
        )->get();

        $almacenPorPunto = $puntos->pluck('almacenid', 'puntoventaid');

        $query = Insumo::query()
            ->with(['unidadMedida', 'almacen'])
            ->whereIn('almacenid', $almacenPorPunto->filter()->values());

        if ($request->filled('puntoventaid')) {
            $almId = $almacenPorPunto->get((int) $request->puntoventaid);
            if ($almId) {
                $query->where('almacenid', $almId);
            }
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(function (Builder $w) use ($term) {
                $w->where('nombre', 'like', $term)
                    ->orWhere('codigo_trazabilidad', 'like', $term);
            });
        }

        $insumos = $query->orderBy('nombre')->get();

        $insumos = $insumos->map(function (Insumo $insumo) use ($puntos) {
            $punto = $puntos->firstWhere('almacenid', $insumo->almacenid);
            $insumo->setAttribute('punto_venta', $punto);

            return $insumo;
        });

        return view('punto_venta.inventario.index', [
            'puntos' => $puntos,
            'insumos' => $insumos,
            'filtroPdv' => $request->integer('puntoventaid') ?: null,
            'filtroPdvNombre' => $request->filled('puntoventaid')
                ? ($puntos->firstWhere('puntoventaid', (int) $request->puntoventaid)?->nombre ?? '')
                : '',
            'filtroQ' => $request->string('q')->toString(),
        ]);
    }

    public function edit(PuntoVenta $punto, Insumo $insumo): View
    {
        $this->autorizarInsumo($punto, $insumo);

        return view('punto_venta.puntos.inventario.edit', compact('punto', 'insumo'));
    }

    public function update(Request $request, PuntoVenta $punto, Insumo $insumo): RedirectResponse
    {
        $this->autorizarInsumo($punto, $insumo);

        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'stock' => 'required|numeric|min:0',
            'stockminimo' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $insumo->update([
            'nombre' => $data['nombre'],
            'stock' => (float) $data['stock'],
            'stockminimo' => (float) ($data['stockminimo'] ?? $insumo->stockminimo ?? 0),
            'descripcion' => $data['descripcion'] ?? $insumo->descripcion,
        ]);

        $destino = $request->input('return') === 'inventario'
            ? route('punto-venta.inventario.index', ['puntoventaid' => $punto->puntoventaid])
            : route('punto-venta.puntos.show', $punto);

        return redirect()
            ->to($destino)
            ->with('success', 'Producto del inventario actualizado.');
    }

    public function destroy(PuntoVenta $punto, Insumo $insumo): RedirectResponse
    {
        $this->autorizarInsumo($punto, $insumo);

        $insumo->delete();

        $destino = request()->input('return') === 'inventario'
            ? route('punto-venta.inventario.index', ['puntoventaid' => $punto->puntoventaid])
            : route('punto-venta.puntos.show', $punto);

        return redirect()
            ->to($destino)
            ->with('success', 'Producto eliminado del inventario.');
    }

    public function qr(PuntoVenta $punto, Insumo $insumo, TrazabilidadProductoPdvService $service): JsonResponse
    {
        $this->autorizarInsumo($punto, $insumo);

        $url = $service->urlPublica($insumo);
        $insumo->refresh();

        return response()->json([
            'url' => $url,
            'codigo' => $insumo->codigo_trazabilidad,
            'producto' => $insumo->nombre,
        ]);
    }

    private function autorizarInsumo(PuntoVenta $punto, Insumo $insumo): void
    {
        abort_unless(PuntoVentaAccess::puedeEditarPunto(auth()->user(), $punto), 403);
        abort_unless(
            $punto->almacenid && (int) $insumo->almacenid === (int) $punto->almacenid,
            404
        );
    }
}
