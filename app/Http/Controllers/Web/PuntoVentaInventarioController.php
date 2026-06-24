<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\PuntoVenta;
use App\Services\InventarioAlmacenProductoService;
use App\Services\PuntoVentaInventarioPresentacionService;
use App\Support\EliminacionSegura;
use App\Support\PuntoVentaAccess;
use App\Support\TrazabilidadProductoPdvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PuntoVentaInventarioController extends Controller
{
    public function index(Request $request, PuntoVentaInventarioPresentacionService $presentaciones): View
    {
        $user = $request->user();

        $puntos = PuntoVentaAccess::scopePuntosDelUsuario(
            PuntoVenta::query()->where('activo', true)->orderBy('nombre'),
            $user
        )->get();

        $puntosFiltrados = $puntos;
        if ($request->filled('puntoventaid')) {
            $puntosFiltrados = $puntos->where('puntoventaid', (int) $request->puntoventaid)->values();
        }

        $termino = $request->filled('q') ? $request->string('q')->trim()->toString() : null;
        $lineas = $presentaciones->lineasParaPuntos($puntosFiltrados, $termino);

        return view('punto_venta.inventario.index', [
            'puntos' => $puntos,
            'lineas' => $lineas,
            'esAdmin' => $user && \App\Support\UsuarioRol::esAdminGlobal($user),
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

    public function destroy(
        PuntoVenta $punto,
        Insumo $insumo,
        InventarioAlmacenProductoService $inventarioAlmacen
    ): RedirectResponse {
        $this->autorizarInsumo($punto, $insumo);

        $almacen = $punto->almacen;
        abort_unless($almacen !== null, 404);

        $insumoObjetivo = Insumo::query()
            ->whereKey((int) $insumo->insumoid)
            ->where('almacenid', (int) $almacen->almacenid)
            ->firstOrFail();

        EliminacionSegura::ejecutar(
            fn () => $inventarioAlmacen->eliminarProducto($almacen, $insumoObjetivo),
            'No se pudo eliminar el producto. Revise movimientos o referencias vinculadas.'
        );

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
