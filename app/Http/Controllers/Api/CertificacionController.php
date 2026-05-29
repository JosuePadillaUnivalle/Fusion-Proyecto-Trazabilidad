<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CertificacionLote;
use Illuminate\Http\Request;

class CertificacionController extends Controller
{
    public function index()
    {
        return response()->json(
            CertificacionLote::with(['lote', 'usuario'])
                ->orderByDesc('fecha_certificacion')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $data['usuarioid'] = auth()->id();
        $data['fecha_certificacion'] = now();
        $data['codigo_certificado'] = 'CERT-' . now()->format('Ymd-His') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));

        $cert = CertificacionLote::create($data);

        return response()->json($cert->load(['lote', 'usuario']), 201);
    }
}

