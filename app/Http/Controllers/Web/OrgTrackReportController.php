<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AlmacenProducto;
use App\Models\EnvioAsignacionMultiple;
use App\Models\InventarioAlmacenEnvio;
use App\Support\LocalOrgTrackFallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OrgTrackReportController extends Controller
{
    public function index(Request $request): View
    {
        $counts = [
            'total' => EnvioAsignacionMultiple::count(),
            'pendientes' => EnvioAsignacionMultiple::where('estado', 'pendiente')->count(),
            'asignados' => EnvioAsignacionMultiple::where('estado', 'asignado')->count(),
            'en_ruta' => EnvioAsignacionMultiple::where('estado', 'en_ruta')->count(),
            'entregados' => EnvioAsignacionMultiple::where('estado', 'entregado')->count(),
            'stock_productos_todas_bodegas' => Schema::hasTable('almacen_producto')
                ? (float) AlmacenProducto::query()->sum('stock')
                : 0.0,
            'lineas_inventario_envio' => Schema::hasTable('inventario_almacen_envio')
                ? (int) InventarioAlmacenEnvio::query()->count()
                : 0,
        ];

        $topTransportistas = EnvioAsignacionMultiple::selectRaw('transportista_usuarioid, count(*) as c')
            ->whereNotNull('transportista_usuarioid')
            ->groupBy('transportista_usuarioid')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        $payload = LocalOrgTrackFallback::enviosPayload(500);
        $envios = $payload['data'] ?? [];

        $porEstado = [];
        $porDestino = [];
        $enviosPorEstado = [];
        $enviosPorDestino = [];

        foreach ($envios as $envio) {
            $estado = strtolower(trim((string) ($envio['estado'] ?? $envio['estado_actual'] ?? 'sin estado')));
            $destino = trim((string) ($envio['destino'] ?? $envio['direccion_destino'] ?? 'sin destino'));
            $porEstado[$estado] = ($porEstado[$estado] ?? 0) + 1;
            $porDestino[$destino] = ($porDestino[$destino] ?? 0) + 1;
            $enviosPorEstado[$estado][] = $envio;
            $enviosPorDestino[$destino][] = $envio;
        }
        arsort($porEstado);
        arsort($porDestino);

        $enviosPorTransportistaId = [];
        $asignaciones = EnvioAsignacionMultiple::query()
            ->with(['transportista', 'pedido'])
            ->whereNotNull('transportista_usuarioid')
            ->orderByDesc('envioasignacionmultipleid')
            ->limit(500)
            ->get();

        foreach ($asignaciones as $a) {
            $detalles = is_array($a->detalles_productos) ? $a->detalles_productos : [];
            $tid = (int) $a->transportista_usuarioid;
            $enviosPorTransportistaId[$tid][] = [
                'id' => (int) $a->envioasignacionmultipleid,
                'externo_envio_id' => $a->externo_envio_id,
                'nombre_remitente' => $detalles['remitente'] ?? ($a->transportista?->nombre ?? '—'),
                'estado' => $a->estado,
                'destino' => $a->pedido?->nombre_planta ?? $a->pedido?->direccion_texto ?? '',
            ];
        }

        return view('envios.reportes-distribucion', compact(
            'counts',
            'topTransportistas',
            'porEstado',
            'porDestino',
            'enviosPorEstado',
            'enviosPorDestino',
            'enviosPorTransportistaId',
        ));
    }
}
