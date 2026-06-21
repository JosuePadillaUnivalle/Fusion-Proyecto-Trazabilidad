<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CertificacionLote;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CertificacionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            CertificacionLote::with(['lote', 'usuario'])
                ->orderByDesc('fecha_certificacion')
                ->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'resultado' => ['nullable', 'string', Rule::in(CertificacionLote::RAZONES)],
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $resultado = $data['resultado'] ?? CertificacionLote::RAZON_CERTIFICADO;

        if ($resultado === CertificacionLote::RAZON_NO_CONFORME && blank($data['observaciones'] ?? null)) {
            return response()->json([
                'message' => 'Indique el motivo del no conforme (daños, plagas, calidad, etc.).',
                'errors' => ['observaciones' => ['El campo observaciones es obligatorio para No conforme.']],
            ], 422);
        }

        $lote = Lote::findOrFail($data['loteid']);

        $prefijo = $resultado === CertificacionLote::RAZON_NO_CONFORME ? 'NCONF' : 'CERT';
        $codigo = $prefijo.'-'.now()->format('Y').'-'.str_pad((string) $lote->loteid, 4, '0', STR_PAD_LEFT);

        $estadoNombre = $resultado === CertificacionLote::RAZON_CERTIFICADO
            ? 'Certificado'
            : 'No conforme';
        $estadoDescripcion = $resultado === CertificacionLote::RAZON_CERTIFICADO
            ? 'Lote validado para despacho y trazabilidad'
            : 'Lote no apto para ingreso a almacén';

        $estado = EstadoLoteTipo::firstOrCreate(
            ['nombre' => $estadoNombre],
            ['descripcion' => $estadoDescripcion]
        );

        $cert = CertificacionLote::updateOrCreate(
            ['loteid' => $lote->loteid],
            [
                'usuarioid' => auth()->id(),
                'codigo_certificado' => $codigo,
                'resultado' => $resultado,
                'observaciones' => $data['observaciones'] ?? null,
                'fecha_certificacion' => now(),
            ]
        );

        $lote->update(['estadolotetipoid' => $estado->estadolotetipoid]);

        HistorialEstadoLote::create([
            'loteid' => $lote->loteid,
            'estadolotetipoid' => $estado->estadolotetipoid,
            'fecha_cambio' => now(),
            'observaciones' => $resultado.': '.$cert->codigo_certificado
                .(($data['observaciones'] ?? null) ? ' — '.$data['observaciones'] : ''),
            'usuarioid' => auth()->id(),
        ]);

        return response()->json($cert->load(['lote', 'usuario']), 201);
    }
}
