<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\IntegracionEnviosService;
use App\Models\EnvioPendiente;
use App\Models\Produccion;
use App\Models\Venta;
use Illuminate\Http\Request;

class EnvioController extends Controller
{
    private IntegracionEnviosService $envioService;

    public function __construct(IntegracionEnviosService $envioService)
    {
        $this->envioService = $envioService;
    }

    /**
     * Vista principal de envíos
     */
    public function index()
    {
        $estadisticas = $this->envioService->getEstadisticasCola();
        $pendientes = EnvioPendiente::where('estado', '!=', 'enviado')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('envios.index', compact('estadisticas', 'pendientes'));
    }

    /**
     * Formulario para crear envío
     */
    public function create()
    {
        $tiposTransporte = $this->envioService->getTiposTransporte();
        $producciones = Produccion::with(['lote.cultivo'])
            ->whereNull('enviado_at')
            ->orderBy('fechacosecha', 'desc')
            ->get();
        
        $apiConectada = $this->envioService->estaConectado();

        return view('envios.create', compact('tiposTransporte', 'producciones', 'apiConectada'));
    }

    /**
     * Procesar envío (con tolerancia a fallos)
     */
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:500',
            'peso' => 'required|numeric|min:0.1',
            'tipo_transporte_id' => 'nullable|integer',
            'direccion_origen' => 'required|string',
            'direccion_destino' => 'required|string',
            'fecha_recogida' => 'required|date',
            'produccion_id' => 'nullable|integer',
        ]);

        $datos = [
            'descripcion' => $request->descripcion,
            'peso' => $request->peso,
            'tipo_transporte_id' => $request->tipo_transporte_id,
            'direccion_origen' => $request->direccion_origen,
            'direccion_destino' => $request->direccion_destino,
            'fecha_recogida' => $request->fecha_recogida,
            'hora_recogida' => $request->hora_recogida ?? '08:00',
            'observaciones' => $request->observaciones,
            'origen_sistema' => 'AgroNexus',
            'produccion_id' => $request->produccion_id,
        ];

        $resultado = $this->envioService->enviar($datos, auth()->id());

        if ($resultado['modo'] === 'offline') {
            return redirect()->route('envios.index')
                ->with('warning', $resultado['message']);
        }

        return redirect()->route('envios.index')
            ->with('success', $resultado['message']);
    }

    /**
     * Verificar estado de conexión (AJAX)
     */
    public function verificarConexion()
    {
        $conectado = $this->envioService->verificarConexion();
        $estadisticas = $this->envioService->getEstadisticasCola();

        return response()->json([
            'conectado' => $conectado,
            'estadisticas' => $estadisticas,
            'mensaje' => $conectado 
                ? 'Conexión con servidor de envíos establecida' 
                : 'Sin conexión con servidor de envíos - Modo offline activo'
        ]);
    }

    /**
     * Sincronizar envíos pendientes manualmente
     */
    public function sincronizar()
    {
        $resultado = $this->envioService->sincronizarPendientes();

        if ($resultado['sincronizados'] > 0) {
            return redirect()->route('envios.index')
                ->with('success', "Se sincronizaron {$resultado['sincronizados']} envíos correctamente");
        }

        return redirect()->route('envios.index')
            ->with('info', $resultado['message']);
    }

    /**
     * Ver detalle de envío pendiente
     */
    public function show(EnvioPendiente $envio)
    {
        return view('envios.show', compact('envio'));
    }

    /**
     * Reintentar envío fallido
     */
    public function reintentar(EnvioPendiente $envio)
    {
        if ($envio->estado === 'enviado') {
            return redirect()->route('envios.index')
                ->with('info', 'Este envío ya fue procesado');
        }

        $resultado = $this->envioService->enviar(
            $envio->datos_envio, 
            $envio->usuarioid
        );

        if ($resultado['modo'] === 'online' && $resultado['success']) {
            $envio->update([
                'estado' => 'enviado',
                'enviado_at' => now(),
            ]);
            return redirect()->route('envios.index')
                ->with('success', 'Envío procesado correctamente');
        }

        $envio->update([
            'intentos' => $envio->intentos + 1,
            'ultimo_intento' => now(),
            'ultimo_error' => $resultado['message'] ?? 'Error desconocido',
        ]);

        return redirect()->route('envios.index')
            ->with('warning', 'No se pudo procesar. Se reintentará automáticamente.');
    }

    /**
     * Eliminar envío pendiente
     */
    public function destroy(EnvioPendiente $envio)
    {
        if ($envio->estado === 'enviado') {
            return redirect()->route('envios.index')
                ->with('error', 'No se puede eliminar un envío ya procesado');
        }

        $envio->delete();

        return redirect()->route('envios.index')
            ->with('success', 'Envío eliminado de la cola');
    }
}