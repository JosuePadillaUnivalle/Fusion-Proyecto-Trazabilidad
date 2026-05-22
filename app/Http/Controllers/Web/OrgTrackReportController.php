<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AlmacenProducto;
use App\Models\EnvioAsignacionMultiple;
use App\Models\InventarioAlmacenEnvio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class OrgTrackReportController extends Controller
{
    public function index(Request $request)
    {
        // dashboard counts
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

        // top transportistas by asignaciones
        $topTransportistas = EnvioAsignacionMultiple::selectRaw('transportista_usuarioid, count(*) as c')
            ->whereNotNull('transportista_usuarioid')
            ->groupBy('transportista_usuarioid')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        return view('envios.reportes-distribucion', compact('counts','topTransportistas'));
    }
}
