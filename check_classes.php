<?php
require '/var/www/vendor/autoload.php';
$app = require_once '/var/www/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$classes = [
    // Models - Almacen
    'App\Models\AlmacenajeLoteProduccion',
    'App\Models\AlmacenProducto',
    'App\Models\AlmacenUsuario',
    // Models - Envios/Logistica
    'App\Models\AsignacionCarga',
    'App\Models\CalificacionEnvio',
    'App\Models\CargaEnvio',
    'App\Models\CatalogoCarga',
    'App\Models\CondicionTransporte',
    'App\Models\DireccionGeoEnvio',
    'App\Models\DireccionGeoSegmento',
    'App\Models\DireccionLogistica',
    'App\Models\EstadoEnvioCatalogo',
    'App\Models\FirmaRecepcionEnvio',
    'App\Models\FirmaTransportistaEnvio',
    'App\Models\HistorialEstadoEnvio',
    'App\Models\InventarioAlmacenEnvio',
    'App\Models\MotivoCancelacionEnvio',
    'App\Models\RecogidaEntrega',
    'App\Models\SeguimientoEnvioGps',
    'App\Models\SeguimientoEnvioPedido',
    // Models - Distribucion
    'App\Models\DistribucionDetalleIngreso',
    'App\Models\DistribucionDetallePedidoAlmacen',
    'App\Models\DistribucionDetalleSalida',
    'App\Models\DistribucionIngreso',
    'App\Models\DistribucionPedidoAlmacen',
    'App\Models\DistribucionSalida',
    'App\Models\ProductoDistribucion',
    // Models - Catalogos
    'App\Models\CategoriaMateriaPrima',
    'App\Models\CategoriaProducto',
    'App\Models\ClienteComercial',
    'App\Models\EstadoAsignacionMultipleCatalogo',
    'App\Models\EstadoQrtoken',
    'App\Models\EstadoTransportista',
    'App\Models\EstadoVehiculo',
    'App\Models\TipoEmpaque',
    'App\Models\TipoIncidenteTransporte',
    'App\Models\TipoMovimientoMateria',
    'App\Models\TipoTransporte',
    'App\Models\TipoVehiculo',
    // Models - Planta
    'App\Models\DatosPlanta',
    'App\Models\OperadorPlanta',
    'App\Models\ProcesoMaquinaPlanta',
    'App\Models\RegistroProcesoMaquinaPlanta',
    'App\Models\VariableEstandar',
    'App\Models\VariableProcesoMaquinaPlanta',
    // Models - Materia Prima
    'App\Models\MateriaPrimaBase',
    'App\Models\MateriaPrimaLote',
    'App\Models\LoteProduccionMateriaPrima',
    'App\Models\RegistroMovimientoMateria',
    // Models - Pedidos/Produccion
    'App\Models\DetalleSolicitudMaterial',
    'App\Models\EvaluacionFinalLoteProduccion',
    'App\Models\LoteProduccionPedido',
    'App\Models\PedidoDestino',
    'App\Models\ProductoDestinoPedido',
    'App\Models\RespuestaProveedorSolicitud',
    'App\Models\SolicitudMaterialPedido',
    // Models - Transporte
    'App\Models\PerfilTransportista',
    'App\Models\QrTokenAsignacion',
    'App\Models\Vehiculo',
    // Controllers
    'App\Http\Controllers\Web\EnvioDashboardController',
    'App\Http\Controllers\Web\EnvioDetalleController',
    'App\Http\Controllers\Web\EnvioMandarController',
    'App\Http\Controllers\Web\EnvioSeguimientoController',
    // Services
    'App\Services\DestinosMotivoAlmacenService',
    'App\Services\OperacionAgricolaAutomaticaService',
    'App\Services\ReferenciasAlmacenDisponiblesService',
    'App\Services\UbicacionesAlmacenService',
    // Support
    'App\Support\DashboardPresentacion',
    'App\Support\EnvioAsignacionEstadoCatalogo',
    'App\Support\LoteDefaults',
    // Command
    'App\Console\Commands\OperacionAgricolaSyncCommand',
];

$ok = 0;
$fail = [];
foreach ($classes as $class) {
    try {
        if (class_exists($class)) {
            $ok++;
        } else {
            $fail[] = 'NOT FOUND: ' . $class;
        }
    } catch (Throwable $e) {
        $fail[] = 'ERROR: ' . basename(str_replace('\\', '/', $class)) . ' -> ' . $e->getMessage();
    }
}

echo "=== CLASS LOAD RESULTS ===\n";
echo "OK: $ok/" . count($classes) . "\n";
if ($fail) {
    echo "\nFAILURES:\n";
    foreach ($fail as $f) echo "  $f\n";
} else {
    echo "All classes loaded successfully!\n";
}
