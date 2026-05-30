<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Models\RegistroProcesoMaquinaPlanta;
use App\Models\VariableEstandar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistroPlantaController extends Controller
{
    /** Dashboard: todos los registros agrupados por lote */
    public function index(): View
    {
        $registros = RegistroProcesoMaquinaPlanta::with([
            'lote.cultivo',
            'procesoMaquina.proceso',
            'procesoMaquina.maquina',
            'usuario',
        ])
        ->orderByDesc('fecha_registro')
        ->get();

        // Group by lote
        $porLote = $registros->groupBy('loteid');

        $stats = [
            'lotes_en_proceso' => $porLote->count(),
            'pasos_completados' => $registros->count(),
            'cumplen_estandar'  => $registros->where('cumple_estandar', true)->count(),
            'no_cumplen'        => $registros->where('cumple_estandar', false)->count(),
        ];

        // Recent lotes with progress
        $lotesConRegistro = Lote::with(['cultivo', 'estadoTipo'])
            ->whereIn('loteid', $porLote->keys())
            ->get()
            ->map(function ($lote) use ($porLote) {
                $regs = $porLote[$lote->loteid];
                $totalPasos = ProcesoMaquinaPlanta::count();
                $lote->pasos_completados = $regs->count();
                $lote->ultimo_registro   = $regs->sortByDesc('fecha_registro')->first();
                $lote->cumple_todos      = $regs->every(fn ($r) => $r->cumple_estandar);
                return $lote;
            });

        $procesos  = ProcesoPlanta::where('activo', true)->get();
        $lotesPend = Lote::with('cultivo')
            ->orderByDesc('loteid')
            ->get();

        return view('registro_planta.index', compact('lotesConRegistro', 'stats', 'procesos', 'lotesPend'));
    }

    /** Formulario de nuevo registro */
    public function create(): View
    {
        $procesos = ProcesoPlanta::with(['pasos.maquina'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        $variables = VariableEstandar::where('activo', true)->get();
        $loteLabel = old('loteid') ? Lote::with('cultivo')->find(old('loteid'))?->nombre : null;

        return view('registro_planta.create', compact('procesos', 'variables', 'loteLabel'));
    }

    /** Guardar uno o varios pasos de registro */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'loteid'                     => 'required|integer|exists:lote,loteid',
            'pasos'                      => 'required|array|min:1',
            'pasos.*.procesomaquinaplantaid' => 'required|integer|exists:proceso_maquina_planta,procesomaquinaplantaid',
            'pasos.*.cumple_estandar'    => 'required|boolean',
            'pasos.*.hora_inicio'        => 'nullable|date',
            'pasos.*.hora_fin'           => 'nullable|date',
            'pasos.*.observaciones'      => 'nullable|string|max:500',
        ]);

        foreach ($request->pasos as $paso) {
            // Build variables_ingresadas JSON from dynamic fields
            $variables = [];
            foreach ($paso as $key => $val) {
                if (str_starts_with($key, 'var_')) {
                    $variables[substr($key, 4)] = $val;
                }
            }

            RegistroProcesoMaquinaPlanta::create([
                'procesomaquinaplantaid' => $paso['procesomaquinaplantaid'],
                'loteid'                 => $request->loteid,
                'usuarioid'              => auth()->id(),
                'variables_ingresadas'   => json_encode($variables),
                'cumple_estandar'        => (bool) $paso['cumple_estandar'],
                'observaciones'          => $paso['observaciones'] ?? null,
                'hora_inicio'            => $paso['hora_inicio'] ?? null,
                'hora_fin'               => $paso['hora_fin'] ?? null,
                'fecha_registro'         => now(),
            ]);
        }

        return redirect()
            ->route('registro-planta.index')
            ->with('success', 'Registro de planta guardado correctamente.');
    }

    /** Detalle de un lote en planta */
    public function show(int $loteId): View
    {
        $lote = Lote::with(['cultivo', 'estadoTipo', 'usuario'])->findOrFail($loteId);

        $registros = RegistroProcesoMaquinaPlanta::with([
            'procesoMaquina.proceso',
            'procesoMaquina.maquina',
            'usuario',
        ])
        ->where('loteid', $loteId)
        ->orderBy('fecha_registro')
        ->get();

        // All possible pasos grouped by process
        $todosLosPasos = ProcesoMaquinaPlanta::with(['proceso', 'maquina'])
            ->orderBy('procesoplantaid')
            ->orderBy('orden_paso')
            ->get()
            ->groupBy('procesoplantaid');

        $procesosConPasos = ProcesoPlanta::with(['pasos.maquina'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function ($proceso) use ($registros) {
                $proceso->pasos_completados = $registros
                    ->filter(fn ($r) => $r->procesoMaquina->procesoplantaid === $proceso->procesoplantaid)
                    ->count();
                $proceso->total_pasos = $proceso->pasos->count();
                return $proceso;
            });

        return view('registro_planta.show', compact('lote', 'registros', 'procesosConPasos'));
    }
}
