<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Support\InsumoCatalogo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsumoController extends Controller
{
    public function index()
    {
        return response()->json(
            Insumo::with(['tipo', 'unidadMedida'])->get()
        );
    }

    public function show($id)
    {
        $insumo = Insumo::with(['tipo', 'unidadMedida', 'loteInsumos'])->findOrFail($id);

        return response()->json($insumo);
    }

    public function store(Request $request)
    {
        $data = $this->validarInsumo($request);
        $data['stockminimo'] = InsumoCatalogo::UMBRAL_ALERTA_STOCK;

        $insumo = Insumo::create($data);

        return response()->json($insumo->load(['tipo', 'unidadMedida']), 201);
    }

    public function update(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);

        $data = $this->validarInsumo($request, partial: true);
        $data['stockminimo'] = InsumoCatalogo::UMBRAL_ALERTA_STOCK;

        $insumo->update($data);

        return response()->json($insumo->load(['tipo', 'unidadMedida']));
    }

    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        $insumo->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }

    private function validarInsumo(Request $request, bool $partial = false): array
    {
        InsumoCatalogo::asegurarCatalogosBase();
        $tiposIds = InsumoCatalogo::tiposOrdenados()->pluck('tipoinsumoid')->all();

        $rules = [
            'nombre' => ($partial ? 'sometimes|' : '').'required|string|max:100',
            'tipoinsumoid' => ($partial ? 'sometimes|' : '').'required|'.Rule::in($tiposIds),
            'unidadmedidaid' => ($partial ? 'sometimes|' : '').'required|exists:unidadmedida,unidadmedidaid',
            'stock' => ($partial ? 'sometimes|' : '').'required|numeric|min:0',
            'descripcion' => 'nullable|string',
        ];

        $data = $request->validate($rules);

        if (isset($data['tipoinsumoid'], $data['unidadmedidaid'])) {
            $this->validarUnidadParaTipo((int) $data['tipoinsumoid'], (int) $data['unidadmedidaid']);
        } elseif (isset($data['unidadmedidaid']) && $request->filled('tipoinsumoid')) {
            $this->validarUnidadParaTipo((int) $request->input('tipoinsumoid'), (int) $data['unidadmedidaid']);
        }

        return $data;
    }

    private function validarUnidadParaTipo(int $tipoinsumoid, int $unidadmedidaid): void
    {
        $tipo = InsumoCatalogo::tiposOrdenados()->firstWhere('tipoinsumoid', $tipoinsumoid);
        $slug = InsumoCatalogo::slugFromNombreTipo($tipo?->nombre);
        $permitidas = collect(InsumoCatalogo::unidadesPorTipoParaJs()[$slug] ?? [])->pluck('id')->all();

        if ($permitidas !== [] && ! in_array($unidadmedidaid, $permitidas, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'unidadmedidaid' => 'La unidad no corresponde al tipo de insumo seleccionado.',
            ]);
        }
    }
}
