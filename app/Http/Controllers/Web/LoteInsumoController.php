<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LoteInsumo;
use App\Models\Lote;
use App\Models\Insumo;
use App\Models\EstadoLoteInsumo;
use App\Services\OperacionAgricolaAutomaticaService;
use App\Support\RegistroDemo;
use App\Support\UsuarioRol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoteInsumoController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = $this->queryLoteInsumosVisibles($request);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'lotes' => (clone $baseQuery)->distinct()->count('loteid'),
            'insumos' => (clone $baseQuery)->distinct()->count('insumoid'),
            'cantidad_total' => (float) (clone $baseQuery)->sum('cantidadusada'),
        ];

        $estadosFiltro = (clone $baseQuery)
            ->join('estadoloteinsumo', 'loteinsumo.estadoloteinsumoid', '=', 'estadoloteinsumo.estadoloteinsumoid')
            ->whereNotNull('loteinsumo.estadoloteinsumoid')
            ->distinct()
            ->orderBy('estadoloteinsumo.nombre')
            ->pluck('estadoloteinsumo.nombre');

        $encargadosFiltro = (clone $baseQuery)
            ->join('usuario', 'loteinsumo.usuarioid', '=', 'usuario.usuarioid')
            ->whereNotNull('loteinsumo.usuarioid')
            ->distinct()
            ->orderBy('usuario.nombre')
            ->pluck('usuario.nombre');

        $loteInsumos = (clone $baseQuery)
            ->with(['lote', 'insumo.unidadMedida', 'usuario', 'estado'])
            ->orderBy('loteinsumoid', 'desc')
            ->paginate(15);

        return view('lote_insumos.index', compact('loteInsumos', 'stats', 'estadosFiltro', 'encargadosFiltro'));
    }

    public function create()
    {
        $loteLabel = old('loteid') ? Lote::find(old('loteid'))?->nombre : null;
        $insumoLabel = old('insumoid') ? Insumo::find(old('insumoid'))?->nombre : null;

        return view('lote_insumos.create', compact('loteLabel', 'insumoLabel'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'insumoid' => 'required|exists:insumo,insumoid',
            'cantidadusada' => 'required|numeric|gt:0',
            'observaciones' => 'nullable|string|max:200',
        ], [
            'loteid.required' => 'Primero selecciona un lote',
            'insumoid.required' => 'Primero selecciona un insumo',
        ]);

        $lote = Lote::findOrFail($data['loteid']);
        $this->autorizarLoteInsumo($request, $lote);

        DB::beginTransaction();

        try {
            $insumo = Insumo::findOrFail($data['insumoid']);

            if ($insumo->stock < $data['cantidadusada']) {
                return back()->withErrors([
                    'cantidadusada' => "Stock insuficiente. Disponible: {$insumo->stock} {$insumo->unidadMedida->abreviatura}",
                ])->withInput();
            }

            $insumo->stock -= $data['cantidadusada'];
            $insumo->save();

            $loteInsumo = LoteInsumo::create([
                'loteid' => $data['loteid'],
                'insumoid' => $data['insumoid'],
                'usuarioid' => $lote->usuarioid,
                'cantidadusada' => $data['cantidadusada'],
                'fechauo' => now(),
                'costototal' => 0,
                'estadoloteinsumoid' => $this->idEstadoAplicado(),
                'observaciones' => $data['observaciones'],
            ]);

            app(OperacionAgricolaAutomaticaService::class)->desdeLoteInsumo($loteInsumo);

            DB::commit();

            $mensaje = "Aplicación registrada. Se descontaron {$data['cantidadusada']} {$insumo->unidadMedida->abreviatura} de {$insumo->nombre}.";

            if ($insumo->stockBajo()) {
                $mensaje .= " ⚠️ ALERTA: Stock bajo ({$insumo->stock} {$insumo->unidadMedida->abreviatura})";
            }

            return redirect()->route('lote-insumos.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Error al registrar: '.$e->getMessage()])->withInput();
        }
    }

    public function show(Request $request, LoteInsumo $loteInsumo)
    {
        $this->autorizarLoteInsumoRegistro($request, $loteInsumo);
        $loteInsumo->load(['lote', 'insumo.unidadMedida', 'usuario', 'estado']);

        return view('lote_insumos.show', compact('loteInsumo'));
    }

    public function edit(Request $request, LoteInsumo $loteInsumo)
    {
        $this->autorizarLoteInsumoRegistro($request, $loteInsumo);

        $lotes = $this->queryLotesParaInsumo($request)->get();
        $insumos = Insumo::with('unidadMedida')->get();
        $estados = EstadoLoteInsumo::all();
        $usuarios = \App\Models\Usuario::all();

        return view('lote_insumos.edit', compact('loteInsumo', 'lotes', 'insumos', 'estados', 'usuarios'));
    }

    public function update(Request $request, LoteInsumo $loteInsumo)
    {
        $this->autorizarLoteInsumoRegistro($request, $loteInsumo);

        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'insumoid' => 'required|exists:insumo,insumoid',
            'cantidadusada' => 'required|numeric|gt:0',
            'estadoloteinsumoid' => 'nullable|exists:estadoloteinsumo,estadoloteinsumoid',
            'observaciones' => 'nullable|string|max:200',
        ]);

        $lote = Lote::findOrFail($data['loteid']);
        $this->autorizarLoteInsumo($request, $lote);

        DB::beginTransaction();

        try {
            $insumo = Insumo::findOrFail($data['insumoid']);

            if ($loteInsumo->insumoid != $data['insumoid']) {
                $insumoAnterior = Insumo::find($loteInsumo->insumoid);
                if ($insumoAnterior) {
                    $insumoAnterior->stock += $loteInsumo->cantidadusada;
                    $insumoAnterior->save();
                }

                if ($insumo->stock < $data['cantidadusada']) {
                    return back()->withErrors([
                        'cantidadusada' => "Stock insuficiente del nuevo insumo. Disponible: {$insumo->stock}",
                    ])->withInput();
                }

                $insumo->stock -= $data['cantidadusada'];
            } else {
                $diferencia = $data['cantidadusada'] - $loteInsumo->cantidadusada;

                if ($diferencia > 0 && $insumo->stock < $diferencia) {
                    return back()->withErrors([
                        'cantidadusada' => "Stock insuficiente para aumentar. Disponible: {$insumo->stock}",
                    ])->withInput();
                }

                $insumo->stock -= $diferencia;
            }

            $insumo->save();

            $loteInsumo->update([
                'loteid' => $data['loteid'],
                'insumoid' => $data['insumoid'],
                'usuarioid' => $lote->usuarioid,
                'cantidadusada' => $data['cantidadusada'],
                'costototal' => 0,
                'estadoloteinsumoid' => $data['estadoloteinsumoid'] ?? $this->idEstadoAplicado(),
                'observaciones' => $data['observaciones'],
            ]);

            DB::commit();

            return redirect()->route('lote-insumos.index')->with('success', 'Aplicación actualizada y stock ajustado.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Error al actualizar: '.$e->getMessage()])->withInput();
        }
    }

    public function destroy(Request $request, LoteInsumo $loteInsumo)
    {
        $this->autorizarLoteInsumoRegistro($request, $loteInsumo);

        DB::beginTransaction();

        try {
            $insumo = Insumo::findOrFail($loteInsumo->insumoid);
            $insumo->stock += $loteInsumo->cantidadusada;
            $insumo->save();

            $loteInsumo->delete();

            DB::commit();

            return redirect()->route('lote-insumos.index')
                ->with('success', "Aplicación eliminada. Se devolvieron {$loteInsumo->cantidadusada} al stock de {$insumo->nombre}.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Error al eliminar: '.$e->getMessage()]);
        }
    }

    private function queryLoteInsumosVisibles(Request $request)
    {
        $query = RegistroDemo::aplicarFiltroLoteInsumoOperativo(LoteInsumo::query());
        $user = $request->user();

        if (UsuarioRol::debeAcotarPorAsignacion($user)) {
            $query->whereHas('lote', fn ($q) => $q->where('usuarioid', (int) $user->usuarioid));
        } elseif (UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            $query->whereHas('lote', fn ($q) => $q->whereIn('usuarioid', UsuarioRol::idsUsuariosBajoJefeAgricultor($user)));
        }

        return $query;
    }

    private function queryLotesParaInsumo(Request $request)
    {
        $query = Lote::query()->with('usuario')->orderBy('nombre');
        $user = $request->user();

        if (UsuarioRol::debeAcotarPorAsignacion($user)) {
            $query->where('usuarioid', (int) $user->usuarioid);
        } elseif (UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            $query->whereIn('usuarioid', UsuarioRol::idsUsuariosBajoJefeAgricultor($user));
        }

        return $query;
    }

    private function autorizarLoteInsumoRegistro(Request $request, LoteInsumo $loteInsumo): void
    {
        $loteInsumo->loadMissing('lote');
        if (! $loteInsumo->lote) {
            abort(404);
        }

        $this->autorizarLoteInsumo($request, $loteInsumo->lote);
    }

    private function autorizarLoteInsumo(Request $request, Lote $lote): void
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            if (! in_array((int) $lote->usuarioid, UsuarioRol::idsUsuariosBajoJefeAgricultor($user), true)) {
                abort(403, 'No tienes acceso a este lote.');
            }

            return;
        }

        if (! UsuarioRol::debeAcotarPorAsignacion($user)) {
            return;
        }

        if ((int) $lote->usuarioid !== (int) $user->usuarioid) {
            abort(403, 'No tienes acceso a este lote.');
        }
    }

    private function idEstadoAplicado(): int
    {
        return (int) (EstadoLoteInsumo::whereRaw('LOWER(nombre) = ?', ['aplicado'])->value('estadoloteinsumoid')
            ?? EstadoLoteInsumo::firstOrCreate(['nombre' => 'Aplicado'], ['nombre' => 'Aplicado'])->estadoloteinsumoid);
    }
}
