<?php

namespace App\Http\Controllers\Web\OrgTrack;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Usuario;
use Illuminate\Http\Request;

class EnvioFusionController extends Controller
{
    public function index()
    {
        $envios = EnvioAsignacionMultiple::with(['pedido', 'transportista', 'ruta', 'almacen'])
            ->orderByDesc('envioasignacionmultipleid')
            ->paginate(20);

        return view('orgtrack.envios.index', compact('envios'));
    }

    public function show(EnvioAsignacionMultiple $envio)
    {
        return view('orgtrack.envios.show', compact('envio'));
    }

    public function edit(EnvioAsignacionMultiple $envio)
    {
        $transportistas = Usuario::query()->where('role', 'transportista')->get();
        return view('orgtrack.envios.edit', compact('envio', 'transportistas'));
    }

    public function update(Request $request, EnvioAsignacionMultiple $envio)
    {
        $data = $request->validate([
            'estado' => 'nullable|string|max:50',
            'vehiculo_ref' => 'nullable|string|max:100',
            'transportista_usuarioid' => 'nullable|integer',
        ]);

        $envio->update($data);

        return redirect()->route('orgtrack.envios.index')
            ->with('success', 'Envío actualizado');
    }

    public function destroy(EnvioAsignacionMultiple $envio)
    {
        $envio->delete();
        return redirect()->route('orgtrack.envios.index')
            ->with('success', 'Envío eliminado');
    }
}
