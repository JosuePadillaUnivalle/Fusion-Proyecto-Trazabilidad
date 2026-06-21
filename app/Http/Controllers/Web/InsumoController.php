<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Support\AlmacenAmbito;
use App\Support\InsumoCatalogo;
use App\Support\InsumoImagenCatalogo;
use App\Support\UsuarioRol;
use App\Services\InsumoEliminacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InsumoController extends Controller
{
    private function rechazarMayoristaCatalogoAgricola(): ?\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        if ($user && UsuarioRol::esMayorista($user) && ! UsuarioRol::esAdminGlobal($user)) {
            return redirect()
                ->route('almacen-mayorista.index')
                ->with('info', 'El catálogo de insumos agrícolas no corresponde al rol mayorista. Use Almacenes mayorista para su inventario.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->rechazarMayoristaCatalogoAgricola()) {
            return $redirect;
        }

        InsumoCatalogo::asegurarInsumosCampo();

        $umbral = InsumoCatalogo::UMBRAL_ALERTA_STOCK;
        $q = InsumoCatalogo::aplicarFiltroOperativo(
            Insumo::with(['tipo', 'unidadMedida'])
        )->orderBy('insumoid', 'desc');

        $stats = [
            'total' => (clone $q)->count(),
            'stock_bajo' => (clone $q)->where('stock', '<=', $umbral)->count(),
            'categorias' => (clone $q)->distinct()->count('tipoinsumoid'),
            'en_alerta' => (clone $q)->where('stock', '<=', $umbral)->count(),
        ];

        $insumos = $q->paginate(15);

        $tiposFiltro = InsumoCatalogo::tiposOrdenados();

        return view('insumos.index', compact('insumos', 'stats', 'umbral', 'tiposFiltro'));
    }

    public function create()
    {
        if ($redirect = $this->rechazarMayoristaCatalogoAgricola()) {
            return $redirect;
        }

        InsumoCatalogo::asegurarCatalogosBase();

        return view('insumos.create', [
            'tipos' => InsumoCatalogo::tiposOrdenados(),
            'unidadesPorTipo' => InsumoCatalogo::unidadesPorTipoParaJs(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validarInsumo($request);
        $data['stockminimo'] = InsumoCatalogo::UMBRAL_ALERTA_STOCK;
        $data = $this->aplicarImagenInsumo($request, $data);

        Insumo::create($data);

        return redirect()->route('insumos.index')->with('success', 'Insumo registrado correctamente.');
    }

    public function show(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
        InsumoCatalogo::asegurarInsumoGestionable($insumo);
        $insumo->load(['tipo', 'unidadMedida', 'almacen']);

        if (InsumoCatalogo::esProductoTerminadoDistribucion($insumo) && $insumo->almacenid) {
            $prefijo = AlmacenAmbito::routePrefix($insumo->almacen->ambito ?? '');

            return redirect()->route($prefijo.'.inventario.show', [$insumo->almacenid, $insumo]);
        }

        return view('insumos.show', [
            'insumo' => $insumo,
            'umbral' => InsumoCatalogo::UMBRAL_ALERTA_STOCK,
            'modoProductoTerminado' => InsumoCatalogo::esProductoTerminadoDistribucion($insumo),
            'urlRetorno' => $this->urlRetornoInventario($insumo),
        ]);
    }

    public function edit(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
        InsumoCatalogo::asegurarInsumoGestionable($insumo);
        $insumo->load(['tipo', 'unidadMedida', 'almacen']);

        if (InsumoCatalogo::esProductoTerminadoDistribucion($insumo) && $insumo->almacenid) {
            $prefijo = AlmacenAmbito::routePrefix($insumo->almacen->ambito ?? '');

            return redirect()->route($prefijo.'.inventario.edit', [$insumo->almacenid, $insumo]);
        }

        InsumoCatalogo::asegurarCatalogosBase();

        return view('insumos.edit', [
            'insumo' => $insumo,
            'tipos' => InsumoCatalogo::tiposOrdenados(),
            'unidadesPorTipo' => InsumoCatalogo::unidadesPorTipoParaJs(),
            'modoProductoTerminado' => false,
            'urlRetorno' => route('insumos.index'),
        ]);
    }

    public function update(Request $request, Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
        InsumoCatalogo::asegurarInsumoGestionable($insumo);

        if (InsumoCatalogo::esProductoTerminadoDistribucion($insumo)) {
            $data = $this->validarProductoTerminado($request);
            $insumo->update($data);

            return redirect($this->urlRetornoInventario($insumo))
                ->with('success', 'Producto actualizado.');
        }

        InsumoCatalogo::asegurarInsumoOperativo($insumo);

        $data = $this->validarInsumo($request);
        $data['stockminimo'] = InsumoCatalogo::UMBRAL_ALERTA_STOCK;
        $data = $this->aplicarImagenInsumo($request, $data, $insumo);

        $insumo->update($data);

        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado.');
    }

    public function destroy(Insumo $insumo, InsumoEliminacionService $eliminacion)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
        InsumoCatalogo::asegurarInsumoGestionable($insumo);

        $retorno = $this->urlRetornoInventario($insumo);

        if (! InsumoCatalogo::esProductoTerminadoDistribucion($insumo)) {
            InsumoCatalogo::asegurarInsumoOperativo($insumo);
        }

        try {
            $this->eliminarImagenSubida($insumo->imagenurl);
            $eliminacion->eliminar($insumo);
        } catch (\Throwable $e) {
            return redirect($retorno)
                ->with('error', 'No se pudo eliminar el insumo. Puede estar vinculado a otros registros del sistema.');
        }

        return redirect($retorno)->with('success', 'Insumo eliminado del inventario.');
    }

    private function urlRetornoInventario(Insumo $insumo): string
    {
        $insumo->loadMissing('almacen');
        $almacenId = $insumo->almacenid;

        return match ($insumo->almacen?->ambito) {
            AlmacenAmbito::MAYORISTA => $almacenId
                ? route('almacen-mayorista.show', $almacenId)
                : route('almacen-mayorista.index'),
            AlmacenAmbito::PLANTA => $almacenId
                ? route('almacen-planta.show', $almacenId)
                : route('almacen-planta.index'),
            default => route('insumos.index'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function validarProductoTerminado(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:100',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'stock' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:500',
        ]) + ['stockminimo' => InsumoCatalogo::UMBRAL_ALERTA_STOCK];
    }

    private function validarInsumo(Request $request): array
    {
        InsumoCatalogo::asegurarCatalogosBase();
        $tiposIds = InsumoCatalogo::tiposOrdenados()->pluck('tipoinsumoid')->all();

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipoinsumoid' => ['required', Rule::in($tiposIds)],
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'stock' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'dosis_por_ha' => 'nullable|numeric|min:0',
            'dosis_unidad' => 'nullable|string|max:20',
            'semillas_por_kg' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:4096',
            'quitar_imagen' => 'nullable|boolean',
        ]);

        $tipo = InsumoCatalogo::tiposOrdenados()->firstWhere('tipoinsumoid', (int) $data['tipoinsumoid']);
        $slug = InsumoCatalogo::slugFromNombreTipo($tipo?->nombre);
        $permitidas = collect(InsumoCatalogo::unidadesPorTipoParaJs()[$slug] ?? [])->pluck('id')->all();

        if ($permitidas !== [] && ! in_array((int) $data['unidadmedidaid'], $permitidas, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'unidadmedidaid' => 'La unidad no corresponde al tipo de insumo seleccionado.',
            ]);
        }

        if ($slug !== 'material_siembra') {
            $data['semillas_por_kg'] = null;
        }

        $um = \App\Models\UnidadMedida::find((int) $data['unidadmedidaid']);
        $data['dosis_unidad'] = $um
            ? InsumoCatalogo::normalizarDosisUnidad($um->abreviatura, $slug)
            : null;

        unset($data['imagen'], $data['quitar_imagen']);

        return $data;
    }

    /** @param  array<string, mixed>  $data */
    private function aplicarImagenInsumo(Request $request, array $data, ?Insumo $insumo = null): array
    {
        $tipo = InsumoCatalogo::tiposOrdenados()->firstWhere('tipoinsumoid', (int) $data['tipoinsumoid']);
        $slug = InsumoCatalogo::slugFromNombreTipo($tipo?->nombre);

        if ($request->boolean('quitar_imagen')) {
            $this->eliminarImagenSubida($insumo?->imagenurl);
            $data['imagenurl'] = InsumoImagenCatalogo::urlPorNombreYTipo($data['nombre'], $slug);

            return $data;
        }

        if ($request->hasFile('imagen')) {
            $this->eliminarImagenSubida($insumo?->imagenurl);
            $data['imagenurl'] = $request->file('imagen')->store('insumos', 'public');

            return $data;
        }

        if ($insumo === null) {
            $data['imagenurl'] = InsumoImagenCatalogo::urlPorNombreYTipo($data['nombre'], $slug);
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

    private function asegurarInsumoDelAlmacenUsuario(Insumo $insumo): void
    {
        // Sin restricción por rol almacén: el agricultor gestiona inventario global.
    }
}
