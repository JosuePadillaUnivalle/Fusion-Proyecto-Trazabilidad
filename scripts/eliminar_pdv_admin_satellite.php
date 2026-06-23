<?php



require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();



use App\Models\Insumo;

use App\Models\PuntoVenta;

use App\Support\PuntoVentaEliminacionCatalogo;



$pv = PuntoVenta::query()

    ->where('nombre', 'like', '%Satélite%')

    ->orWhere('nombre', 'like', '%Satelite%')

    ->first();



if (! $pv) {

    echo "No se encontró Mercado Satélite Norte.\n";

    exit(0);

}



echo "PDV: {$pv->nombre} (id {$pv->puntoventaid})\n";



$eval = PuntoVentaEliminacionCatalogo::evaluar($pv);

if (! $eval['ok']) {

    echo "Bloqueado: {$eval['mensaje']}\n";

    exit(1);

}



PuntoVentaEliminacionCatalogo::cancelarPedidosPendientes($pv);

PuntoVentaEliminacionCatalogo::eliminarHistorialAsociado($pv);



if ($pv->almacenid) {

    Insumo::query()->where('almacenid', $pv->almacenid)->delete();

    $pv->almacen?->delete();

}



$pv->delete();

echo "Punto de venta eliminado correctamente.\n";

