<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Support\InsumoCatalogo;
use App\Support\InsumoImagenCatalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InsumoController extends Controller
{
    public function index()
    {
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
        InsumoCatalogo::asegurarInsumoOperativo($insumo);
        $insumo->load(['tipo', 'unidadMedida']);

        return view('insumos.show', [
            'insumo' => $insumo,
            'umbral' => InsumoCatalogo::UMBRAL_ALERTA_STOCK,
        ]);
    }

    public function edit(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
        InsumoCatalogo::asegurarInsumoOperativo($insumo);
        InsumoCatalogo::asegurarCatalogosBase();
        $insumo->load(['tipo', 'unidadMedida']);

        return view('insumos.edit', [
            'insumo' => $insumo,
            'tipos' => InsumoCatalogo::tiposOrdenados(),
            'unidadesPorTipo' => InsumoCatalogo::unidadesPorTipoParaJs(),
        ]);
    }

    public function update(Request $request, Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
        InsumoCatalogo::asegurarInsumoOperativo($insumo);

        $data = $this->validarInsumo($request);
        $data['stockminimo'] = InsumoCatalogo::UMBRAL_ALERTA_STOCK;
        $data = $this->aplicarImagenInsumo($request, $data, $insumo);

        $insumo->update($data);

        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado.');
    }

    public function destroy(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
        InsumoCatalogo::asegurarInsumoOperativo($insumo);

        $this->eliminarImagenSubida($insumo->imagenurl);
        $insumo->delete();

        return redirect()->route('insumos.index')->with('success', 'Insumo eliminado.');
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
