<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Support\AlmacenAmbito;
use App\Support\InsumoCatalogo;
use App\Support\InsumoImagenCatalogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AlmacenInventarioController extends Controller
{
    public function show(Request $request, Almacen $almacen, Insumo $insumo): View
    {
        $ctx = AlmacenAmbito::contexto($request);
        $this->autorizarProducto($almacen, $insumo, $ctx['ambito']);

        $insumo->load(['tipo', 'unidadMedida', 'almacen']);
        $estadoStock = InsumoCatalogo::estadoStockAlmacen($insumo);

        return view('almacenes.inventario.show', array_merge($ctx, [
            'almacen' => $almacen,
            'producto' => $insumo,
            'estadoStock' => $estadoStock,
        ]));
    }

    public function edit(Request $request, Almacen $almacen, Insumo $insumo): View
    {
        $ctx = AlmacenAmbito::contexto($request);
        $this->autorizarProducto($almacen, $insumo, $ctx['ambito']);

        $insumo->load(['tipo', 'unidadMedida', 'almacen']);

        return view('almacenes.inventario.edit', array_merge($ctx, [
            'almacen' => $almacen,
            'producto' => $insumo,
            'unidades' => UnidadMedida::query()->orderBy('nombre')->get(),
        ]));
    }

    public function update(Request $request, Almacen $almacen, Insumo $insumo): RedirectResponse
    {
        $ctx = AlmacenAmbito::contexto($request);
        $this->autorizarProducto($almacen, $insumo, $ctx['ambito']);

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'stock' => 'required|numeric|min:0',
            'stockminimo' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string|max:500',
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:4096',
            'quitar_imagen' => 'nullable|boolean',
        ]);

        $payload = [
            'nombre' => $data['nombre'],
            'unidadmedidaid' => $data['unidadmedidaid'],
            'stock' => (float) $data['stock'],
            'stockminimo' => (float) ($data['stockminimo'] ?? $insumo->stockminimo ?? InsumoCatalogo::UMBRAL_ALERTA_STOCK),
            'descripcion' => $data['descripcion'] ?? $insumo->descripcion,
        ];

        $payload = $this->aplicarImagenProducto($request, $payload, $insumo);

        $insumo->update($payload);

        return redirect()
            ->route($ctx['rutaPrefijo'].'.show', $almacen);
    }

    public function destroy(Request $request, Almacen $almacen, Insumo $insumo): RedirectResponse
    {
        $ctx = AlmacenAmbito::contexto($request);
        $this->autorizarProducto($almacen, $insumo, $ctx['ambito']);

        $this->eliminarImagenSubida($insumo->imagenurl);
        $insumo->delete();

        return redirect()
            ->route($ctx['rutaPrefijo'].'.show', $almacen);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function aplicarImagenProducto(Request $request, array $data, Insumo $insumo): array
    {
        if ($request->boolean('quitar_imagen')) {
            $this->eliminarImagenSubida($insumo->imagenurl);
            $data['imagenurl'] = InsumoImagenCatalogo::urlProductoTerminado($data['nombre']);

            return $data;
        }

        if ($request->hasFile('imagen')) {
            $this->eliminarImagenSubida($insumo->imagenurl);
            $data['imagenurl'] = $request->file('imagen')->store('insumos', 'public');
        }

        return $data;
    }

    private function eliminarImagenSubida(?string $imagenurl): void
    {
        $ruta = InsumoImagenCatalogo::rutaAlmacenamiento($imagenurl);
        if ($ruta !== null && Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }

    private function autorizarProducto(Almacen $almacen, Insumo $insumo, string $ambito): void
    {
        abort_unless(
            in_array($ambito, [AlmacenAmbito::MAYORISTA, AlmacenAmbito::PLANTA], true),
            404
        );

        abort_unless(
            ($almacen->ambito ?? '') === $ambito
            && (int) $insumo->almacenid === (int) $almacen->almacenid,
            404
        );

        InsumoCatalogo::asegurarInsumoGestionable($insumo);

        abort_unless(
            InsumoCatalogo::esProductoTerminadoDistribucion($insumo),
            404,
            'El producto no pertenece al inventario de este almacén.'
        );
    }
}
