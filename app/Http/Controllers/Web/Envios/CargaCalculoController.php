<?php

namespace App\Http\Controllers\Web\Envios;

use App\Http\Controllers\Controller;
use App\Services\CargaCalculoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CargaCalculoController extends Controller
{
    public function calcular(Request $request, CargaCalculoService $service): JsonResponse
    {
        if ($request->filled('catalogo_tamano_conteo_id')) {
            $resultado = $service->desdeCatalogos(
                (int) $request->input('catalogo_tamano_conteo_id'),
                $request->filled('tipo_empaque_id') ? (int) $request->input('tipo_empaque_id') : null,
                (string) $request->input('forma_pedido', 'empaques'),
                (float) $request->input('cantidad_pedido', 0)
            );
        } else {
            $resultado = $service->calcular($request->only([
                'conteo_por_empaque',
                'peso_promedio_kg',
                'largo_cm',
                'ancho_cm',
                'alto_cm',
                'tara_kg',
                'capacidad_unidades',
                'unidades_por_pallet',
                'forma_pedido',
                'cantidad_pedido',
            ]));
        }

        return response()->json(['data' => $resultado]);
    }
}
