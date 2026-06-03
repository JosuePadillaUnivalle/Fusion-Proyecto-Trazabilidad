<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Insumo;
use App\Services\RecepcionPlantaEnvioService;
use App\Support\AlmacenAmbito;
use App\Support\UsuarioRol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecepcionPlantaController extends Controller
{
    public function __construct(
        private readonly RecepcionPlantaEnvioService $recepcionService
    ) {}

    public function index(): View
    {
        $pendientes = EnvioAsignacionMultiple::query()
            ->with(['transportista', 'pedido', 'almacen'])
            ->whereIn('estado', ['en_transporte_planta', 'en_ruta', 'en_transito'])
            ->orderByDesc('updated_at')
            ->paginate(15, ['*'], 'pendientes');

        $recibidos = EnvioAsignacionMultiple::query()
            ->with(['transportista', 'almacen', 'recepcionConfirmadaPor'])
            ->where(function ($q) {
                $q->where('estado', 'recibido_planta')
                    ->orWhereNotNull('fecha_recepcion_planta');
            })
            ->orderByDesc('fecha_recepcion_planta')
            ->paginate(15, ['*'], 'recibidos');

        $almacenes = AlmacenAmbito::scope(Almacen::query(), AlmacenAmbito::PLANTA)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('recepcion_planta.index', compact('pendientes', 'recibidos', 'almacenes'));
    }

    public function confirmar(Request $request, EnvioAsignacionMultiple $asignacion): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeConfirmarRecepcionPlanta($request->user()), 403);

        $data = $request->validate([
            'almacenid' => ['required', 'integer', 'exists:almacen,almacenid'],
            'insumoid' => ['required', 'integer', 'exists:insumo,insumoid'],
            'cantidad' => ['required', 'numeric', 'min:0.001'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->recepcionService->confirmar(
                $asignacion,
                $request->user(),
                (int) $data['almacenid'],
                (int) $data['insumoid'],
                (float) $data['cantidad'],
                $data['observaciones'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('recepcion-planta.index')
            ->with('success', 'Recepción en planta registrada. La cosecha quedó almacenada y se guardó la fecha de confirmación.');
    }

    public function insumosPorAlmacen(Request $request): \Illuminate\Http\JsonResponse
    {
        abort_unless(UsuarioRol::puedeConfirmarRecepcionPlanta($request->user()), 403);

        $almacenid = (int) $request->integer('almacenid');
        $producto = $request->string('producto')->toString();

        $almacen = Almacen::query()->findOrFail($almacenid);
        $insumos = Insumo::query()
            ->where('almacenid', $almacen->almacenid)
            ->orderBy('nombre')
            ->get()
            ->map(fn (Insumo $i) => [
                'id' => $i->insumoid,
                'nombre' => $i->nombre,
                'stock' => (float) $i->stock,
            ]);

        $sugerido = $this->recepcionService->resolverInsumoEnAlmacen($almacen, $producto !== '' ? $producto : null);

        return response()->json([
            'insumos' => $insumos,
            'insumo_sugerido_id' => $sugerido?->insumoid,
        ]);
    }
}
