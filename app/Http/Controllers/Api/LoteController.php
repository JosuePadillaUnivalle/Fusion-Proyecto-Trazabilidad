<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Support\LoteCultivoResolver;
use App\Support\LoteDefaults;
use App\Support\UbicacionGpsParser;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    public function index()
    {
        return response()->json(
            Lote::with(['usuario', 'cultivo', 'estadoTipo', 'insumoSemilla'])->get()
                ->makeVisible(['usuario', 'cultivo', 'estadoTipo', 'insumoSemilla'])
        );
    }

    public function show($id)
    {
        return response()->json(
            Lote::with(['usuario', 'cultivo', 'estadoTipo', 'producciones', 'actividades', 'insumoSemilla'])
                ->findOrFail($id)
                ->makeVisible(['usuario', 'cultivo', 'estadoTipo', 'insumoSemilla', 'producciones', 'actividades'])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'usuarioid' => 'required|exists:usuario,usuarioid',
            'nombre' => 'required|string|max:100',
            'ubicacion' => 'nullable|string|max:200',
            'superficie' => 'required|numeric|min:0.01',
            'cultivoid' => 'nullable|exists:cultivo,cultivoid',
            'insumosemallaid' => 'nullable|exists:insumo,insumoid',
            'cantidad_semilla_planificada' => 'nullable|numeric|min:0',
            'fechasiembra' => 'nullable|date',
            'estadolotetipoid' => 'nullable|exists:estadolote_tipo,estadolotetipoid',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'imagenurl' => 'nullable|string|max:250',
            'imagen' => 'nullable|image|max:2048',
        ]);

        try {
            if (empty($data['cultivoid']) && !empty($data['insumosemallaid'])) {
                $data['cultivoid'] = LoteCultivoResolver::resolver((int) $data['insumosemallaid']);
            }

            $data['ubicacion'] = UbicacionGpsParser::normalizarUbicacionLote(
                $data['ubicacion'] ?? null,
                isset($data['latitud']) ? (float) $data['latitud'] : null,
                isset($data['longitud']) ? (float) $data['longitud'] : null
            );
        } catch (\Throwable $e) {
            // Fallback: continuar sin resolver
        }

        if ($request->hasFile('imagen')) {
            try {
                $file = $request->file('imagen');
                $filename = 'lote_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $supabase = new \App\Services\SupabaseStorage();
                $response = $supabase->upload($filename, file_get_contents($file), $file->getMimeType());
                if ($response->successful()) {
                    $data['imagenurl'] = $supabase->getPublicUrl($filename);
                }
            } catch (\Throwable $e) {
                // Silently fail
            }
        }

        unset($data['imagen']);

        try {
            $data = LoteDefaults::enrich($data, true);
        } catch (\Throwable $e) {
            // Fallback: continuar sin defaults
        }

        $lote = Lote::create($data);

        try {
            LoteDefaults::registrarHistorialInicial($lote);
        } catch (\Throwable $e) {
            // Silently fail
        }

        return response()->json(
            $lote->load(['usuario', 'cultivo', 'estadoTipo', 'insumoSemilla'])
                ->makeVisible(['usuario', 'cultivo', 'estadoTipo', 'insumoSemilla']),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $lote = Lote::findOrFail($id);

        $data = $request->validate([
            'usuarioid' => 'sometimes|exists:usuario,usuarioid',
            'nombre' => 'sometimes|string|max:100',
            'ubicacion' => 'nullable|string|max:200',
            'superficie' => 'sometimes|numeric|min:0.01',
            'cultivoid' => 'nullable|exists:cultivo,cultivoid',
            'insumosemallaid' => 'nullable|exists:insumo,insumoid',
            'cantidad_semilla_planificada' => 'nullable|numeric|min:0',
            'fechasiembra' => 'nullable|date',
            'estadolotetipoid' => 'nullable|exists:estadolote_tipo,estadolotetipoid',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'imagenurl' => 'nullable|string|max:250',
        ]);

        try {
            if (isset($data['insumosemallaid'])) {
                $data['cultivoid'] = LoteCultivoResolver::resolver((int) $data['insumosemallaid']);
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        try {
            $data = LoteDefaults::enrich($data, false);
        } catch (\Throwable $e) {
            // Fallback
        }

        $lote->update($data);

        return response()->json(
            $lote->load(['usuario', 'cultivo', 'estadoTipo', 'insumoSemilla'])
                ->makeVisible(['usuario', 'cultivo', 'estadoTipo', 'insumoSemilla'])
        );
    }

    public function destroy($id)
    {
        $lote = Lote::findOrFail($id);
        $lote->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}
