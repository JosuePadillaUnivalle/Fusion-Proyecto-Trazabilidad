<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Support\InsumoCatalogo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsumoController extends Controller
{
    public function index()
    {
        $umbral = InsumoCatalogo::UMBRAL_ALERTA_STOCK;
        $q = Insumo::with(['tipo', 'unidadMedida'])->orderBy('insumoid', 'desc');

        $stats = [
            'total' => (clone $q)->count(),
            'stock_bajo' => (clone $q)->where('stock', '<=', $umbral)->count(),
            'categorias' => (clone $q)->distinct()->count('tipoinsumoid'),
            'en_alerta' => (clone $q)->where('stock', '<=', $umbral)->count(),
        ];

        $insumos = $q->paginate(15);

        return view('insumos.index', compact('insumos', 'stats', 'umbral'));
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

        Insumo::create($data);

        return redirect()->route('insumos.index')->with('success', 'Insumo registrado correctamente.');
    }

    public function show(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
        $insumo->load(['tipo', 'unidadMedida']);

        return view('insumos.show', [
            'insumo' => $insumo,
            'umbral' => InsumoCatalogo::UMBRAL_ALERTA_STOCK,
        ]);
    }

    public function edit(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
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

        $data = $this->validarInsumo($request);
        $data['stockminimo'] = InsumoCatalogo::UMBRAL_ALERTA_STOCK;

        $insumo->update($data);

        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado.');
    }

    public function destroy(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);

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
        ]);

        $tipo = InsumoCatalogo::tiposOrdenados()->firstWhere('tipoinsumoid', (int) $data['tipoinsumoid']);
        $slug = InsumoCatalogo::slugFromNombreTipo($tipo?->nombre);
        $permitidas = collect(InsumoCatalogo::unidadesPorTipoParaJs()[$slug] ?? [])->pluck('id')->all();

        if ($permitidas !== [] && ! in_array((int) $data['unidadmedidaid'], $permitidas, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'unidadmedidaid' => 'La unidad no corresponde al tipo de insumo seleccionado.',
            ]);
        }

        return $data;
    }

    private function asegurarInsumoDelAlmacenUsuario(Insumo $insumo): void
    {
        // Sin restricción por rol almacén: el agricultor gestiona inventario global.
    }
}
