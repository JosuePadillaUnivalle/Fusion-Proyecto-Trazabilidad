<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LoteInsumo;
use App\Models\Lote;
use App\Models\Insumo;
use App\Models\EstadoLoteInsumo;
use App\Services\OperacionAgricolaAutomaticaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoteInsumoController extends Controller
{
    public function index()
    {
        $loteInsumos = LoteInsumo::with(['lote', 'insumo', 'usuario', 'estado'])
            ->orderBy('loteinsumoid', 'desc')
            ->paginate(15);

        return view('lote_insumos.index', compact('loteInsumos'));
    }

    public function create()
    {
        // Solo lotes con usuario asignado
        $lotes = Lote::with('usuario')->get();

        // Solo insumos con stock disponible
        $insumos = Insumo::with('unidadMedida')
            ->where('stock', '>', 0)
            ->get();

        $estados = EstadoLoteInsumo::all();

        return view('lote_insumos.create', compact('lotes', 'insumos', 'estados'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'insumoid' => 'required|exists:insumo,insumoid',
            'cantidadusada' => 'required|numeric|gt:0',
            'estadoloteinsumoid' => 'nullable|exists:estadoloteinsumo,estadoloteinsumoid',
            'observaciones' => 'nullable|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            // Obtener el lote para sacar el usuario responsable
            $lote = Lote::findOrFail($data['loteid']);

            // Obtener el insumo
            $insumo = Insumo::findOrFail($data['insumoid']);

            // Validar que hay suficiente stock
            if ($insumo->stock < $data['cantidadusada']) {
                return back()->withErrors([
                    'cantidadusada' => "Stock insuficiente. Disponible: {$insumo->stock} {$insumo->unidadMedida->abreviatura}"
                ])->withInput();
            }

            // Restar del stock
            $insumo->stock -= $data['cantidadusada'];
            $insumo->save();

            // Calcular costo total automáticamente
            $costoTotal = $data['cantidadusada'] * ($insumo->preciounitario ?? 0);

            // Crear el registro con usuario automático del lote y fecha actual
            $loteInsumo = LoteInsumo::create([
                'loteid' => $data['loteid'],
                'insumoid' => $data['insumoid'],
                'usuarioid' => $lote->usuarioid, // Usuario responsable del lote (automático)
                'cantidadusada' => $data['cantidadusada'],
                'fechauo' => now(), // Fecha actual automática
                'costototal' => $costoTotal,
                'estadoloteinsumoid' => $data['estadoloteinsumoid'] ?? 1, // Por defecto "aplicado"
                'observaciones' => $data['observaciones'],
            ]);

            app(OperacionAgricolaAutomaticaService::class)->desdeLoteInsumo($loteInsumo);

            DB::commit();

            // Mensaje de éxito con información del stock
            $mensaje = "Aplicación registrada. Se descontaron {$data['cantidadusada']} {$insumo->unidadMedida->abreviatura} de {$insumo->nombre}.";

            // Alerta si el stock quedó bajo
            if ($insumo->stock <= $insumo->stockminimo) {
                $mensaje .= " ⚠️ ALERTA: Stock bajo ({$insumo->stock} {$insumo->unidadMedida->abreviatura})";
            }

            return redirect()->route('lote-insumos.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(LoteInsumo $loteInsumo)
    {
        $loteInsumo->load(['lote', 'insumo.unidadMedida', 'usuario', 'estado']);
        return view('lote_insumos.show', compact('loteInsumo'));
    }

    public function edit(LoteInsumo $loteInsumo)
    {
        $lotes = Lote::with('usuario')->get();
        $insumos = Insumo::with('unidadMedida')->get();
        $estados = EstadoLoteInsumo::all();
        $usuarios = \App\Models\Usuario::all(); // Fix: Fetch users

        return view('lote_insumos.edit', compact('loteInsumo', 'lotes', 'insumos', 'estados', 'usuarios'));
    }

    public function update(Request $request, LoteInsumo $loteInsumo)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'insumoid' => 'required|exists:insumo,insumoid',
            'cantidadusada' => 'required|numeric|gt:0',
            'estadoloteinsumoid' => 'nullable|exists:estadoloteinsumo,estadoloteinsumoid',
            'observaciones' => 'nullable|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            $lote = Lote::findOrFail($data['loteid']);
            $insumo = Insumo::findOrFail($data['insumoid']);

            // Si cambió el insumo, devolver stock al anterior
            if ($loteInsumo->insumoid != $data['insumoid']) {
                $insumoAnterior = Insumo::find($loteInsumo->insumoid);
                if ($insumoAnterior) {
                    $insumoAnterior->stock += $loteInsumo->cantidadusada;
                    $insumoAnterior->save();
                }

                // Validar stock del nuevo insumo
                if ($insumo->stock < $data['cantidadusada']) {
                    return back()->withErrors([
                        'cantidadusada' => "Stock insuficiente del nuevo insumo. Disponible: {$insumo->stock}"
                    ])->withInput();
                }

                $insumo->stock -= $data['cantidadusada'];
            } else {
                // Mismo insumo: ajustar diferencia
                $diferencia = $data['cantidadusada'] - $loteInsumo->cantidadusada;

                if ($diferencia > 0 && $insumo->stock < $diferencia) {
                    return back()->withErrors([
                        'cantidadusada' => "Stock insuficiente para aumentar. Disponible: {$insumo->stock}"
                    ])->withInput();
                }

                $insumo->stock -= $diferencia;
            }

            $insumo->save();

            // Calcular nuevo costo total
            $costoTotal = $data['cantidadusada'] * ($insumo->preciounitario ?? 0);

            // Actualizar registro
            $loteInsumo->update([
                'loteid' => $data['loteid'],
                'insumoid' => $data['insumoid'],
                'usuarioid' => $lote->usuarioid, // Usuario del lote
                'cantidadusada' => $data['cantidadusada'],
                'costototal' => $costoTotal,
                'estadoloteinsumoid' => $data['estadoloteinsumoid'],
                'observaciones' => $data['observaciones'],
            ]);

            DB::commit();

            return redirect()->route('lote-insumos.index')->with('success', 'Aplicación actualizada y stock ajustado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(LoteInsumo $loteInsumo)
    {
        DB::beginTransaction();

        try {
            // Devolver el stock al inventario
            $insumo = Insumo::findOrFail($loteInsumo->insumoid);
            $insumo->stock += $loteInsumo->cantidadusada;
            $insumo->save();

            // Eliminar el registro
            $loteInsumo->delete();

            DB::commit();

            return redirect()->route('lote-insumos.index')
                ->with('success', "Aplicación eliminada. Se devolvieron {$loteInsumo->cantidadusada} al stock de {$insumo->nombre}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }
}