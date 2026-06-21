<?php

namespace App\Http\Controllers\Web\Envios;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use App\Services\TransporteCapacidadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class EnvioCapacidadController extends Controller
{
    public function validar(Request $request, TransporteCapacidadService $service): JsonResponse
    {
        $request->validate([
            'vehiculo_id' => 'required|integer',
            'peso_kg' => 'required|numeric|min:0',
            'volumen_m3' => 'nullable|numeric|min:0',
        ]);

        $vehiculo = Vehiculo::query()->findOrFail($request->integer('vehiculo_id'));
        $pesoKg = (float) $request->input('peso_kg');
        $volumenM3 = $request->filled('volumen_m3')
            ? (float) $request->input('volumen_m3')
            : $service->volumenDesdePeso($pesoKg);

        $cap = $service->capacidadEfectiva($vehiculo);
        $ok = true;
        $mensaje = '';

        try {
            $service->validarCarga($vehiculo, $pesoKg, $volumenM3);
        } catch (InvalidArgumentException $e) {
            $ok = false;
            $mensaje = $e->getMessage();
        }

        $pctKg = $cap['kg'] > 0 ? round(($pesoKg / $cap['kg']) * 100, 1) : null;

        return response()->json([
            'ok' => $ok,
            'mensaje' => $mensaje,
            'capacidad_kg' => $cap['kg'],
            'capacidad_m3' => $cap['m3'],
            'peso_kg' => $pesoKg,
            'volumen_m3' => $volumenM3,
            'porcentaje_uso' => $pctKg,
            'vehiculo' => $vehiculo->placa,
        ]);
    }
}
