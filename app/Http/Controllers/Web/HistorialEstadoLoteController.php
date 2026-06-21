<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Models\Usuario;
use Illuminate\Http\Request;

class HistorialEstadoLoteController extends Controller
{
    public function index(Request $request)
    {
        $query = HistorialEstadoLote::with(['lote', 'estadoTipo', 'usuario']);

        if ($request->filled('buscar')) {
            $buscar = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($buscar) {
                $q->where('observaciones', 'like', $buscar)
                    ->orWhereHas('lote', fn ($l) => $l->where('nombre', 'like', $buscar))
                    ->orWhereHas('estadoTipo', fn ($e) => $e->where('nombre', 'like', $buscar));
            });
        }

        if ($request->filled('loteid')) {
            $query->where('loteid', (int) $request->loteid);
        }

        if ($request->filled('estadolotetipoid')) {
            $query->where('estadolotetipoid', (int) $request->estadolotetipoid);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_cambio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_cambio', '<=', $request->fecha_hasta);
        }

        $historial = $query->orderByDesc('historial_estado_id')->paginate(15)->withQueryString();
        $lotes = Lote::orderBy('nombre')->get(['loteid', 'nombre']);
        $tiposEstado = EstadoLoteTipo::orderBy('nombre')->get(['estadolotetipoid', 'nombre']);

        return view('historial_estados_lote.index', compact('historial', 'lotes', 'tiposEstado'));
    }

    public function create()
    {
        return view('historial_estados_lote.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'estadolotetipoid' => 'required|exists:estadolote_tipo,estadolotetipoid',
            'fecha_cambio' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'imagenurl' => 'nullable|string|max:250',
            'usuarioid' => 'nullable|exists:usuario,usuarioid',
        ]);

        HistorialEstadoLote::create($data);

        return redirect()->route('historial-estados-lote.index')->with('success', 'Historial creado correctamente.');
    }

    public function show(HistorialEstadoLote $historial_estados_lote)
    {
        $historial_estados_lote->load(['lote', 'estadoTipo', 'usuario']);

        return view('historial_estados_lote.show', ['registro' => $historial_estados_lote]);
    }

    public function edit(HistorialEstadoLote $historial_estados_lote)
    {
        return view('historial_estados_lote.edit', array_merge(
            ['registro' => $historial_estados_lote],
            $this->formOptions()
        ));
    }

    public function update(Request $request, HistorialEstadoLote $historial_estados_lote)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'estadolotetipoid' => 'required|exists:estadolote_tipo,estadolotetipoid',
            'fecha_cambio' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'imagenurl' => 'nullable|string|max:250',
            'usuarioid' => 'nullable|exists:usuario,usuarioid',
        ]);

        $historial_estados_lote->update($data);

        return redirect()->route('historial-estados-lote.show', $historial_estados_lote)->with('success', 'Historial actualizado.');
    }

    public function destroy(HistorialEstadoLote $historial_estados_lote)
    {
        $historial_estados_lote->delete();

        return redirect()->route('historial-estados-lote.index')->with('success', 'Historial eliminado.');
    }

    private function formOptions(): array
    {
        return [
            'lotes' => Lote::orderBy('nombre')->get(['loteid', 'nombre']),
            'tiposEstado' => EstadoLoteTipo::orderBy('nombre')->get(['estadolotetipoid', 'nombre']),
            'usuarios' => Usuario::orderBy('nombre')->get(['usuarioid', 'nombre', 'apellido']),
        ];
    }
}
