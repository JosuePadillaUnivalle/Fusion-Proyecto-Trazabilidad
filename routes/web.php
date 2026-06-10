<?php

use Illuminate\Support\Facades\Route;

// CONTROLADORES WEB
use App\Http\Controllers\Web\ActividadController;
use App\Http\Controllers\Web\ClimaController;
use App\Http\Controllers\Web\CultivoController;
use App\Http\Controllers\Web\EstadoLoteController;
use App\Http\Controllers\Web\EstadoLoteInsumoController;
use App\Http\Controllers\Web\EstadoLoteTipoController;
use App\Http\Controllers\Web\HistorialEstadoLoteController;
use App\Http\Controllers\Web\InsumoController;
use App\Http\Controllers\Web\LoteController;
use App\Http\Controllers\Web\LoteInsumoController;
use App\Http\Controllers\Web\PrioridadController;
use App\Http\Controllers\Web\ProduccionController;
use App\Http\Controllers\Web\TipoActividadController;
use App\Http\Controllers\Web\TipoInsumoController;
use App\Http\Controllers\Web\UnidadMedidaController;
use App\Http\Controllers\Web\VentaController;
use App\Http\Controllers\Web\GestionUsuariosController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PedidoController;
use App\Http\Controllers\Web\PedidoAgricolaController;
use App\Http\Controllers\Web\PuntoVentaController;
use App\Http\Controllers\Web\PuntoVentaInventarioController;
use App\Http\Controllers\Web\PedidoDistribucionController;
use App\Http\Controllers\Web\TrazabilidadPublicaController;
use App\Http\Controllers\Web\UserProfileController;

// 🔹 nuevos controladores web de almacenamiento
use App\Http\Controllers\Web\TipoAlmacenController;
use App\Http\Controllers\Web\AlmacenController;



// 🔹 Dashboard Controller
use App\Http\Controllers\Web\DashboardController;

// 🔹 Catálogos Controller
use App\Http\Controllers\Web\CatalogoSelectorController;

// 🔹 External API Proxy Controller
use App\Http\Controllers\Web\EnvioDashboardController;
use App\Http\Controllers\Web\EnvioDetalleController;
use App\Http\Controllers\Web\EnvioMandarController;
use App\Http\Controllers\Web\EnvioSeguimientoController;
use App\Http\Controllers\Web\Envios\EnvioDireccionController;
use App\Http\Controllers\Web\Envios\EnvioTransportistaController;
use App\Http\Controllers\Web\Envios\EnvioVehiculoController;
use App\Http\Controllers\Web\ExternalApiProxyController;
use App\Http\Controllers\Web\CertificacionController;
use App\Http\Controllers\Web\ActorAbastecimientoController;
use App\Http\Controllers\Web\ProcesoPlantaController;
use App\Http\Controllers\Web\MaquinaPlantaController;
use App\Http\Controllers\Web\PlantillaTransformacionController;
use App\Http\Controllers\Web\AsignacionMultipleController;
use App\Http\Controllers\Web\RutaMultiEntregaController;
use App\Http\Controllers\Web\IncidenteEnvioController;
use App\Http\Controllers\Web\DocumentoEntregaController;
use App\Http\Controllers\Web\AlmacenMovimientoController;
use App\Http\Controllers\Web\OrgTrack\TransportistaController;
use App\Http\Controllers\Web\OrgTrack\EnvioFusionController;

// ======================================================
// RUTAS PÚBLICAS (SIN LOGIN)


// Página inicial -> redirige al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Formularios auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/registro-enviado', [AuthController::class, 'registroEnviado'])->name('register.enviado');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/trazabilidad/{codigo}', [TrazabilidadPublicaController::class, 'show'])->name('trazabilidad.publica');


// RUTAS PROTEGIDAS (REQUIEREN ESTAR LOGUEADO)

Route::middleware(['auth', 'cuenta.aprobada'])->group(function () {

    // Perfil de Usuario
    Route::get('/perfil', [UserProfileController::class, 'show'])->name('profile.show');
    Route::put('/perfil', [UserProfileController::class, 'update'])->name('profile.update');
    Route::post('/perfil/bienvenida-vista', [UserProfileController::class, 'marcarBienvenidaVista'])->name('profile.bienvenida.vista');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/notificaciones/{notificacion}/leer', [DashboardController::class, 'marcarNotificacionLeida'])->name('notificaciones.leer');
    Route::post('/notificaciones/{notificacion}/descartar', [DashboardController::class, 'descartarNotificacion'])->name('notificaciones.descartar');
    Route::get('/dashboard/panel-planta', [DashboardController::class, 'panelPlanta'])->name('dashboard.panel-planta');
    Route::get('/dashboard/panel-transportista', [DashboardController::class, 'panelTransportista'])->name('dashboard.panel-transportista');
    Route::get('/dashboard/panel-almacen', [DashboardController::class, 'panelAlmacen'])->name('dashboard.panel-almacen');

    Route::prefix('catalogo-selector')->name('catalogo-selector.')->group(function () {
        Route::get('/usuarios', [CatalogoSelectorController::class, 'usuarios'])->name('usuarios');
        Route::get('/vehiculos', [CatalogoSelectorController::class, 'vehiculos'])->name('vehiculos');
        Route::get('/cultivos', [CatalogoSelectorController::class, 'cultivos'])->name('cultivos');
        Route::get('/lotes', [CatalogoSelectorController::class, 'lotes'])->name('lotes');
        Route::get('/insumos', [CatalogoSelectorController::class, 'insumos'])->name('insumos');
        Route::get('/pedidos', [CatalogoSelectorController::class, 'pedidos'])->name('pedidos');
        Route::get('/actores', [CatalogoSelectorController::class, 'actores'])->name('actores');
        Route::get('/almacenes', [CatalogoSelectorController::class, 'almacenes'])->name('almacenes');
        Route::get('/puntos-venta', [CatalogoSelectorController::class, 'puntosVenta'])->name('puntos-venta');
        Route::get('/productos-pedido', [CatalogoSelectorController::class, 'productosPedido'])->name('productos-pedido');
        Route::get('/producciones', [CatalogoSelectorController::class, 'producciones'])->name('producciones');
        Route::get('/producciones-stock-almacen', [CatalogoSelectorController::class, 'produccionesStockAlmacen'])->name('producciones-stock-almacen');
        Route::get('/procesos-planta', [CatalogoSelectorController::class, 'procesosPlanta'])->name('procesos-planta');
        Route::get('/maquinas-planta', [CatalogoSelectorController::class, 'maquinasPlanta'])->name('maquinas-planta');
        Route::get('/plantillas-transformacion', [CatalogoSelectorController::class, 'plantillasTransformacion'])->name('plantillas-transformacion');
    });

    // API endpoints para clima (OpenWeather)
    Route::get('/api/clima', [DashboardController::class, 'getClima'])->name('api.clima');
    Route::get('/api/pronostico', [DashboardController::class, 'getPronostico'])->name('api.pronostico');

    // Actividades: consulta con lotes,read; mutaciones requieren lotes,update (separación planta solo lectura en lotes)
    Route::get('actividades/calendario', [ActividadController::class, 'calendario'])->name('actividades.calendario')->middleware('action.permission:lotes,read');
    Route::get('actividades', [ActividadController::class, 'index'])->name('actividades.index')->middleware('action.permission:lotes,read');
    Route::get('actividades/create', [ActividadController::class, 'create'])->name('actividades.create')->middleware('action.permission:lotes,update');
    Route::post('actividades', [ActividadController::class, 'store'])->name('actividades.store')->middleware('action.permission:lotes,update');
    Route::get('actividades/{actividad}', [ActividadController::class, 'show'])->name('actividades.show')->middleware('action.permission:lotes,read');
    Route::get('actividades/{actividad}/edit', [ActividadController::class, 'edit'])->name('actividades.edit')->middleware('action.permission:lotes,update');
    Route::put('actividades/{actividad}', [ActividadController::class, 'update'])->name('actividades.update')->middleware('action.permission:lotes,update');
    Route::delete('actividades/{actividad}', [ActividadController::class, 'destroy'])->name('actividades.destroy')->middleware('action.permission:lotes,update');
    Route::post('actividades/{actividad}/marcar-realizada', [ActividadController::class, 'marcarRealizada'])->name('actividades.marcar-realizada')->middleware('action.permission:lotes,update');
    Route::get('climas/datos-tiempo', [ClimaController::class, 'datosTiempo'])->name('climas.datos-tiempo');
    Route::get('climas', [ClimaController::class, 'index'])->name('climas.index');
    Route::get('clima', [ClimaController::class, 'index'])->name('clima.index');
    Route::resource('cultivos', CultivoController::class);
    Route::resource('estadolotes', EstadoLoteController::class);
    Route::resource('estado-lote-insumos', EstadoLoteInsumoController::class);
    Route::resource('estado-lote-tipos', EstadoLoteTipoController::class);
    Route::resource('historial-estados-lote', HistorialEstadoLoteController::class);
    Route::get('insumos', [InsumoController::class, 'index'])->name('insumos.index')->middleware('action.permission:inventario,read');
    Route::get('insumos/create', [InsumoController::class, 'create'])->name('insumos.create')->middleware('action.permission:inventario,create');
    Route::post('insumos', [InsumoController::class, 'store'])->name('insumos.store')->middleware('action.permission:inventario,create');
    Route::get('insumos/{insumo}', [InsumoController::class, 'show'])->name('insumos.show')->middleware('action.permission:inventario,read');
    Route::get('insumos/{insumo}/edit', [InsumoController::class, 'edit'])->name('insumos.edit')->middleware('action.permission:inventario,update');
    Route::put('insumos/{insumo}', [InsumoController::class, 'update'])->name('insumos.update')->middleware('action.permission:inventario,update');
    Route::delete('insumos/{insumo}', [InsumoController::class, 'destroy'])->name('insumos.destroy')->middleware('action.permission:inventario,delete');
    Route::get('actores-abastecimiento', [ActorAbastecimientoController::class, 'index'])->name('actores-abastecimiento.index')->middleware('action.permission:inventario,read');
    Route::post('actores-abastecimiento', [ActorAbastecimientoController::class, 'store'])->name('actores-abastecimiento.store')->middleware('action.permission:inventario,update');
    Route::put('actores-abastecimiento/{actores_abastecimiento}', [ActorAbastecimientoController::class, 'update'])->name('actores-abastecimiento.update')->middleware('action.permission:inventario,update');
    Route::delete('actores-abastecimiento/{actores_abastecimiento}', [ActorAbastecimientoController::class, 'destroy'])->name('actores-abastecimiento.destroy')->middleware('action.permission:inventario,update');
    Route::get('lotes/mapa', [LoteController::class, 'mapa'])->name('lotes.mapa')->middleware('action.permission:lotes,read');
    Route::get('lotes', [LoteController::class, 'index'])->name('lotes.index')->middleware('action.permission:lotes,read');
    Route::get('lotes/create', [LoteController::class, 'create'])->name('lotes.create')->middleware('action.permission:lotes,create');
    Route::post('lotes/sincronizar-operacion', [LoteController::class, 'sincronizarOperacion'])->name('lotes.sincronizar-operacion')->middleware('action.permission:lotes,update');
    Route::post('lotes', [LoteController::class, 'store'])->name('lotes.store')->middleware('action.permission:lotes,create');
    Route::get('lotes/{lote}/trazabilidad', [LoteController::class, 'trazabilidad'])->name('lotes.trazabilidad')->middleware('action.permission:lotes,read');
    Route::get('lotes/{lote}/cambiar-estado', [LoteController::class, 'cambiarEstadoForm'])->name('lotes.cambiar-estado')->middleware('action.permission:lotes,update');
    Route::post('lotes/{lote}/cambiar-estado', [LoteController::class, 'cambiarEstadoStore'])->name('lotes.cambiar-estado.store')->middleware('action.permission:lotes,update');
    Route::get('lotes/{lote}/ubicacion', [LoteController::class, 'ubicacion'])->name('lotes.ubicacion')->middleware('action.permission:lotes,read');
    Route::get('lotes/{lote}', [LoteController::class, 'show'])->name('lotes.show')->middleware('action.permission:lotes,read');
    Route::get('lotes/{lote}/edit', [LoteController::class, 'edit'])->name('lotes.edit')->middleware('action.permission:lotes,update');
    Route::put('lotes/{lote}', [LoteController::class, 'update'])->name('lotes.update')->middleware('action.permission:lotes,update');
    Route::delete('lotes/{lote}', [LoteController::class, 'destroy'])->name('lotes.destroy')->middleware('action.permission:lotes,delete');
    Route::get('lote-insumos', [LoteInsumoController::class, 'index'])->name('lote-insumos.index')->middleware('action.permission:inventario,read');
    Route::get('lote-insumos/create', [LoteInsumoController::class, 'create'])->name('lote-insumos.create')->middleware('action.permission:inventario,create');
    Route::post('lote-insumos', [LoteInsumoController::class, 'store'])->name('lote-insumos.store')->middleware('action.permission:inventario,create');
    Route::get('lote-insumos/{lote_insumo}', [LoteInsumoController::class, 'show'])->name('lote-insumos.show')->middleware('action.permission:inventario,read');
    Route::get('lote-insumos/{lote_insumo}/edit', [LoteInsumoController::class, 'edit'])->name('lote-insumos.edit')->middleware('action.permission:inventario,update');
    Route::put('lote-insumos/{lote_insumo}', [LoteInsumoController::class, 'update'])->name('lote-insumos.update')->middleware('action.permission:inventario,update');
    Route::delete('lote-insumos/{lote_insumo}', [LoteInsumoController::class, 'destroy'])->name('lote-insumos.destroy')->middleware('action.permission:inventario,delete');
    Route::resource('prioridades', PrioridadController::class)
        ->parameters(['prioridades' => 'prioridad']);
    Route::resource('producciones', ProduccionController::class)
        ->parameters(['producciones' => 'produccion']);
    Route::resource('procesos-planta', ProcesoPlantaController::class)->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('plantillas-transformacion', PlantillaTransformacionController::class)
        ->parameters(['plantillas-transformacion' => 'plantillas_transformacion']);
    Route::resource('maquinas-planta', MaquinaPlantaController::class)->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('maquinas-planta/{maquinas_plantum}/toggle-activo', [MaquinaPlantaController::class, 'toggleActivo'])
        ->name('maquinas-planta.toggle-activo');

    Route::get('procesamiento', [\App\Http\Controllers\Web\LoteProduccionController::class, 'index'])
        ->name('procesamiento.index')
        ->middleware('action.permission:lote_produccion,read');
    Route::get('procesamiento/siguiente-nombre', [\App\Http\Controllers\Web\LoteProduccionController::class, 'siguienteNombre'])
        ->name('procesamiento.siguiente-nombre')
        ->middleware('action.permission:lote_produccion,create');
    Route::post('procesamiento', [\App\Http\Controllers\Web\LoteProduccionController::class, 'store'])
        ->name('procesamiento.store')
        ->middleware('action.permission:lote_produccion,create');
    Route::get('procesamiento/{loteProduccion}/edit', [\App\Http\Controllers\Web\LoteProduccionController::class, 'edit'])
        ->name('procesamiento.edit')
        ->middleware('action.permission:lote_produccion,create');
    Route::put('procesamiento/{loteProduccion}', [\App\Http\Controllers\Web\LoteProduccionController::class, 'update'])
        ->name('procesamiento.update')
        ->middleware('action.permission:lote_produccion,create');
    Route::delete('procesamiento/{loteProduccion}', [\App\Http\Controllers\Web\LoteProduccionController::class, 'destroy'])
        ->name('procesamiento.destroy')
        ->middleware('action.permission:lote_produccion,create');
    Route::get('procesamiento/{loteProduccion}', [\App\Http\Controllers\Web\LoteProduccionController::class, 'show'])
        ->name('procesamiento.show')
        ->middleware('action.permission:lote_produccion,read');
    Route::post('procesamiento/{loteProduccion}/etapa', [\App\Http\Controllers\Web\LoteProduccionController::class, 'registrarEtapa'])
        ->name('procesamiento.registrar-etapa')
        ->middleware('action.permission:lote_produccion,create');
    Route::post('procesamiento/{loteProduccion}/asignar-etapa', [\App\Http\Controllers\Web\LoteProduccionController::class, 'asignarEtapa'])
        ->name('procesamiento.asignar-etapa')
        ->middleware('action.permission:lote_produccion,create');
    Route::post('procesamiento/{loteProduccion}/asignaciones-etapa/{asignacion}/completar', [\App\Http\Controllers\Web\LoteProduccionController::class, 'completarEtapaAsignada'])
        ->name('procesamiento.completar-etapa-asignada')
        ->middleware('action.permission:lote_produccion,create');
    Route::get('mis-tareas-planta', [\App\Http\Controllers\Web\TareaPlantaController::class, 'index'])
        ->name('tareas-planta.index')
        ->middleware('action.permission:lote_produccion,read');
    Route::get('mis-tareas-planta/{asignacion}', [\App\Http\Controllers\Web\TareaPlantaController::class, 'show'])
        ->name('tareas-planta.show')
        ->middleware('action.permission:lote_produccion,read');
    Route::post('mis-tareas-planta/{asignacion}/completar', [\App\Http\Controllers\Web\TareaPlantaController::class, 'completar'])
        ->name('tareas-planta.completar')
        ->middleware('action.permission:lote_produccion,create');
    Route::post('procesamiento/{loteProduccion}/certificar', [\App\Http\Controllers\Web\LoteProduccionController::class, 'certificar'])
        ->name('procesamiento.certificar')
        ->middleware('action.permission:lote_produccion,create');
    Route::post('procesamiento/{loteProduccion}/almacenar', [\App\Http\Controllers\Web\LoteProduccionController::class, 'almacenar'])
        ->name('procesamiento.almacenar')
        ->middleware('action.permission:lote_produccion,create');
    Route::post('procesamiento/{loteProduccion}/completar', [\App\Http\Controllers\Web\LoteProduccionController::class, 'completar'])
        ->name('procesamiento.completar')
        ->middleware('action.permission:lote_produccion,create');

    Route::resource('tipo-actividad', TipoActividadController::class);
    Route::resource('tipo-insumos', TipoInsumoController::class);
    Route::resource('unidades-medida', UnidadMedidaController::class)
        ->parameters(['unidades-medida' => 'unidad']);
    Route::get('ventas', [VentaController::class, 'index'])->name('ventas.index')->middleware('action.permission:ventas,read');
    Route::get('ventas/create', [VentaController::class, 'create'])->name('ventas.create')->middleware('action.permission:ventas,create');
    Route::post('ventas', [VentaController::class, 'store'])->name('ventas.store')->middleware('action.permission:ventas,create');
    Route::get('ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show')->middleware('action.permission:ventas,read');
    Route::get('ventas/{venta}/edit', [VentaController::class, 'edit'])->name('ventas.edit')->middleware('action.permission:ventas,update');
    Route::put('ventas/{venta}', [VentaController::class, 'update'])->name('ventas.update')->middleware('action.permission:ventas,update');
    Route::delete('ventas/{venta}', [VentaController::class, 'destroy'])->name('ventas.destroy')->middleware('action.permission:ventas,delete');
    Route::resource('tipoalmacenes', TipoAlmacenController::class)
        ->parameters(['tipoalmacenes' => 'tipoalmacen']);
    Route::redirect('almacenes', '/almacen-agricola');
    Route::redirect('almacen-movimientos', '/almacen-agricola/movimientos');
    Route::redirect('almacen-reportes', '/almacen-agricola/movimientos/reportes');

    $registrarModuloAlmacen = function (string $prefijo, string $ambito) {
        Route::prefix($prefijo)
            ->name($prefijo.'.')
            ->middleware(['almacen.ambito:'.$ambito])
            ->group(function () use ($ambito) {
                Route::get('/', [AlmacenController::class, 'index'])->name('index')->middleware('action.permission:inventario,read');
                Route::get('/create', [AlmacenController::class, 'create'])->name('create')->middleware('action.permission:inventario,create');
                Route::get('/selector-ubicacion', [AlmacenController::class, 'selectorUbicacion'])->name('selector-ubicacion')->middleware('action.permission:inventario,read');
                Route::post('/', [AlmacenController::class, 'store'])->name('store')->middleware('action.permission:inventario,create');

                Route::get('/movimientos/referencias-disponibles', [AlmacenMovimientoController::class, 'referenciasDisponibles'])
                    ->name('movimientos.referencias')
                    ->middleware('action.permission:almacen_movimientos,read');
                Route::get('/movimientos/reportes', [AlmacenMovimientoController::class, 'reportes'])
                    ->name('movimientos.reportes')
                    ->middleware('action.permission:almacen_reportes,read');
                Route::get('/movimientos', [AlmacenMovimientoController::class, 'index'])
                    ->name('movimientos.index')
                    ->middleware('action.permission:almacen_movimientos,read');
                Route::get('/movimientos/cosecha/{produccionAlmacenamiento}', [AlmacenMovimientoController::class, 'showCosecha'])
                    ->whereNumber('produccionAlmacenamiento')
                    ->name('movimientos.cosecha.show')
                    ->middleware('action.permission:almacen_movimientos,read');
                Route::get('/movimientos/{almacenMovimiento}', [AlmacenMovimientoController::class, 'show'])
                    ->whereNumber('almacenMovimiento')
                    ->name('movimientos.show')
                    ->middleware('action.permission:almacen_movimientos,read');
                Route::get('/movimientos/{naturaleza}/create', [AlmacenMovimientoController::class, 'create'])
                    ->whereIn('naturaleza', ['ingreso', 'salida'])
                    ->name('movimientos.create')
                    ->middleware('action.permission:almacen_movimientos,read');
                Route::post('/movimientos/{naturaleza}', [AlmacenMovimientoController::class, 'store'])
                    ->whereIn('naturaleza', ['ingreso', 'salida'])
                    ->name('movimientos.store')
                    ->middleware('action.permission:almacen_movimientos,read');

                Route::get('/{almacen}', [AlmacenController::class, 'show'])
                    ->whereNumber('almacen')
                    ->name('show')
                    ->middleware('action.permission:inventario,read');
                Route::get('/{almacen}/edit', [AlmacenController::class, 'edit'])
                    ->whereNumber('almacen')
                    ->name('edit')
                    ->middleware('action.permission:inventario,update');
                Route::put('/{almacen}', [AlmacenController::class, 'update'])
                    ->whereNumber('almacen')
                    ->name('update')
                    ->middleware('action.permission:inventario,update');
                Route::delete('/{almacen}', [AlmacenController::class, 'destroy'])
                    ->whereNumber('almacen')
                    ->name('destroy')
                    ->middleware('action.permission:inventario,delete');
            });
    };

    Route::redirect('/almacen-agricola/almacenamiento-produccion', '/almacen-agricola/movimientos', 301);
    Route::redirect('/almacen-agricola/almacenamiento-produccion/{any}', '/almacen-agricola/movimientos')->where('any', '.*');

    $registrarModuloAlmacen('almacen-agricola', \App\Support\AlmacenAmbito::AGRICOLA);
    $registrarModuloAlmacen('almacen-planta', \App\Support\AlmacenAmbito::PLANTA);
    Route::get('/certificaciones', [CertificacionController::class, 'index'])->name('certificaciones.index')->middleware('action.permission:certificaciones,read');
    Route::post('/certificaciones/masivo', [CertificacionController::class, 'storeBatch'])->name('certificaciones.store-bulk')->middleware('action.permission:certificaciones,create');
    Route::post('/certificaciones', [CertificacionController::class, 'store'])->name('certificaciones.store')->middleware('action.permission:certificaciones,create');
    Route::get('/certificaciones/{certificacion}', [CertificacionController::class, 'show'])->name('certificaciones.show')->middleware('action.permission:certificaciones,read');
    // ==============================
    // PEDIDOS (CLIENTES EXTERNOS)
    // ==============================
    Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index')->middleware('action.permission:pedidos,read');
    Route::get('pedidos/create', [PedidoController::class, 'create'])->name('pedidos.create')->middleware('action.permission:pedidos,create');
    Route::post('pedidos', [PedidoController::class, 'store'])->name('pedidos.store')->middleware('action.permission:pedidos,create');
    Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show')->middleware('action.permission:pedidos,read');
    Route::get('pedidos/{pedido}/edit', [PedidoController::class, 'edit'])->name('pedidos.edit')->middleware('action.permission:pedidos,update');
    Route::put('pedidos/{pedido}', [PedidoController::class, 'update'])->name('pedidos.update')->middleware('action.permission:pedidos,update');
    Route::post('pedidos/{pedido}/asignar-transportista', [PedidoController::class, 'asignarTransportista'])->name('pedidos.asignar-transportista')->middleware('action.permission:pedidos,update');
    Route::post('pedidos/{pedido}/confirmar-carga-envio', [PedidoController::class, 'confirmarCargaEnvio'])->name('pedidos.confirmar-carga-envio')->middleware('action.permission:pedidos,update');
    Route::post('pedidos/{pedido}/confirmar-llegada-planta', [PedidoController::class, 'confirmarLlegadaPlanta'])->name('pedidos.confirmar-llegada-planta')->middleware('action.permission:recepcion_planta,confirm');
    Route::delete('pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('pedidos.destroy')->middleware('action.permission:pedidos,delete');

    // Pedidos recibidos de planta — bandeja producción agrícola
    Route::prefix('produccion-agricola/pedidos')->name('agricola.pedidos.')->group(function () {
        Route::get('/', [PedidoAgricolaController::class, 'index'])->name('index')->middleware('action.permission:pedidos,read');
        Route::get('/{pedido}', [PedidoAgricolaController::class, 'show'])->name('show')->middleware('action.permission:pedidos,read');
        Route::post('/{pedido}/aceptar', [PedidoAgricolaController::class, 'aceptar'])->name('aceptar')->middleware('action.permission:pedidos,update');
        Route::post('/{pedido}/rechazar', [PedidoAgricolaController::class, 'rechazar'])->name('rechazar')->middleware('action.permission:pedidos,update');
        Route::post('/{pedido}/confirmar-carga-envio', [PedidoAgricolaController::class, 'confirmarCargaEnvio'])->name('confirmar-carga-envio')->middleware('action.permission:pedidos,update');
    });


    // Punto de venta / minoristas
    Route::prefix('punto-venta')->name('punto-venta.')->group(function () {
        Route::get('puntos', [PuntoVentaController::class, 'index'])->name('puntos.index')->middleware('action.permission:punto_venta,read');
        Route::get('puntos/create', [PuntoVentaController::class, 'create'])->name('puntos.create')->middleware('action.permission:punto_venta,create');
        Route::post('puntos', [PuntoVentaController::class, 'store'])->name('puntos.store')->middleware('action.permission:punto_venta,create');
        Route::get('puntos/{punto}', [PuntoVentaController::class, 'show'])->name('puntos.show')->middleware('action.permission:punto_venta,read');
        Route::get('puntos/{punto}/edit', [PuntoVentaController::class, 'edit'])->name('puntos.edit')->middleware('action.permission:punto_venta,update');
        Route::put('puntos/{punto}', [PuntoVentaController::class, 'update'])->name('puntos.update')->middleware('action.permission:punto_venta,update');
        Route::delete('puntos/{punto}', [PuntoVentaController::class, 'destroy'])->name('puntos.destroy')->middleware('action.permission:punto_venta,delete');

        Route::get('puntos/{punto}/inventario/{insumo}/edit', [PuntoVentaInventarioController::class, 'edit'])->name('puntos.inventario.edit')->middleware('action.permission:punto_venta,update');
        Route::put('puntos/{punto}/inventario/{insumo}', [PuntoVentaInventarioController::class, 'update'])->name('puntos.inventario.update')->middleware('action.permission:punto_venta,update');
        Route::delete('puntos/{punto}/inventario/{insumo}', [PuntoVentaInventarioController::class, 'destroy'])->name('puntos.inventario.destroy')->middleware('action.permission:punto_venta,delete');
        Route::get('puntos/{punto}/inventario/{insumo}/qr', [PuntoVentaInventarioController::class, 'qr'])->name('puntos.inventario.qr')->middleware('action.permission:punto_venta,read');

        Route::get('pedidos', [PedidoDistribucionController::class, 'index'])->name('pedidos.index')->middleware('action.permission:pedidos_distribucion,read');
        Route::get('pedidos/create', [PedidoDistribucionController::class, 'create'])->name('pedidos.create')->middleware('action.permission:pedidos_distribucion,create');
        Route::post('pedidos', [PedidoDistribucionController::class, 'store'])->name('pedidos.store')->middleware('action.permission:pedidos_distribucion,create');
        Route::get('pedidos/{pedido}', [PedidoDistribucionController::class, 'show'])->name('pedidos.show')->middleware('action.permission:pedidos_distribucion,read');
        Route::post('pedidos/{pedido}/aceptar', [PedidoDistribucionController::class, 'aceptar'])->name('pedidos.aceptar')->middleware('action.permission:pedidos_distribucion,update');
        Route::post('pedidos/{pedido}/rechazar', [PedidoDistribucionController::class, 'rechazar'])->name('pedidos.rechazar')->middleware('action.permission:pedidos_distribucion,update');
        Route::post('pedidos/{pedido}/marcar-enviado', [PedidoDistribucionController::class, 'marcarEnviado'])->name('pedidos.marcar-enviado')->middleware('action.permission:pedidos_distribucion,update');
        Route::post('pedidos/{pedido}/confirmar-recepcion', [PedidoDistribucionController::class, 'confirmarRecepcion'])->name('pedidos.confirmar-recepcion')->middleware('action.permission:pedidos_distribucion,update');
    });


    // GESTIÓN UNIFICADA DE USUARIOS

    Route::get('/gestion-usuarios', [GestionUsuariosController::class, 'index'])
        ->middleware('action.permission:usuarios,read')
        ->name('gestion.index');

    Route::get('/gestion-usuarios/crear', [GestionUsuariosController::class, 'create'])
        ->middleware('action.permission:usuarios,create')
        ->name('gestion.create');

    Route::get('/gestion-usuarios/{usuario}/editar', [GestionUsuariosController::class, 'edit'])
        ->middleware('action.permission:usuarios,update')
        ->name('gestion.edit');

    Route::get('/gestion-usuarios/{usuario}', [GestionUsuariosController::class, 'show'])
        ->middleware('action.permission:usuarios,read')
        ->name('gestion.show');

    // CRUD Usuarios
    Route::post('/gestion-usuarios/usuario', [GestionUsuariosController::class, 'storeUsuario'])
        ->middleware('action.permission:usuarios,create')
        ->name('gestion.usuario.store');

    Route::put('/gestion-usuarios/usuario/{usuario}', [GestionUsuariosController::class, 'updateUsuario'])
        ->middleware('action.permission:usuarios,update')
        ->name('gestion.usuario.update');

    Route::delete('/gestion-usuarios/usuario/{usuario}', [GestionUsuariosController::class, 'destroyUsuario'])
        ->middleware('action.permission:usuarios,delete')
        ->name('gestion.usuario.destroy');

    Route::post('/gestion-usuarios/{usuario}/aprobar', [GestionUsuariosController::class, 'aprobarSolicitud'])
        ->middleware('action.permission:solicitudes,approve')
        ->name('gestion.solicitud.aprobar');

    Route::post('/gestion-usuarios/{usuario}/rechazar', [GestionUsuariosController::class, 'rechazarSolicitud'])
        ->middleware('action.permission:solicitudes,approve')
        ->name('gestion.solicitud.rechazar');

    // CRUD Roles
    Route::post('/gestion-usuarios/rol', [GestionUsuariosController::class, 'storeRol'])
        ->middleware('action.permission:usuarios,admin')
        ->name('gestion.rol.store');

    Route::put('/gestion-usuarios/rol/{role}', [GestionUsuariosController::class, 'updateRol'])
        ->middleware('action.permission:usuarios,admin')
        ->name('gestion.rol.update');

    Route::delete('/gestion-usuarios/rol/{role}', [GestionUsuariosController::class, 'destroyRol'])
        ->middleware('action.permission:usuarios,admin')
        ->name('gestion.rol.destroy');


    // ENVÍOS

    Route::prefix('envios')->name('envios.')->group(function () {
        Route::get('/mandar', [EnvioMandarController::class, 'create'])->name('mandar')->middleware('action.permission:envios,create');
        Route::get('/seguimiento', [EnvioSeguimientoController::class, 'index'])->name('seguimiento')->middleware('action.permission:envios,read');
        Route::get('/admin', [EnvioDashboardController::class, 'index'])->name('admin')->middleware('action.permission:envios,admin');
        Route::resource('transportistas', EnvioTransportistaController::class)
            ->parameters(['transportistas' => 'transportista'])
            ->names([
                'index' => 'transportistas',
                'create' => 'transportistas.create',
                'store' => 'transportistas.store',
                'show' => 'transportistas.show',
                'edit' => 'transportistas.edit',
                'update' => 'transportistas.update',
                'destroy' => 'transportistas.destroy',
            ]);
        Route::resource('vehiculos', EnvioVehiculoController::class)
            ->parameters(['vehiculos' => 'vehiculo'])
            ->names([
                'index' => 'vehiculos',
                'create' => 'vehiculos.create',
                'store' => 'vehiculos.store',
                'show' => 'vehiculos.show',
                'edit' => 'vehiculos.edit',
                'update' => 'vehiculos.update',
                'destroy' => 'vehiculos.destroy',
            ]);
        Route::resource('direcciones', EnvioDireccionController::class)
            ->parameters(['direcciones' => 'direccion'])
            ->names([
                'index' => 'direcciones',
                'create' => 'direcciones.create',
                'store' => 'direcciones.store',
                'show' => 'direcciones.show',
                'edit' => 'direcciones.edit',
                'update' => 'direcciones.update',
                'destroy' => 'direcciones.destroy',
            ]);
        Route::get('/reportes-distribucion', [\App\Http\Controllers\Web\OrgTrackReportController::class, 'index'])->name('reportes-distribucion')->middleware('action.permission:envios,read');
        Route::get('/{id}', [EnvioDetalleController::class, 'show'])->name('detalle')->where('id', '[0-9]+')->middleware('action.permission:envios,read');

        // ==============================
        // PROXY API EXTERNA (evita CORS)
        // ==============================
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/ping', [ExternalApiProxyController::class, 'ping'])->name('ping')->middleware('action.permission:envios,read');
            // Catálogos
            Route::get('/catalogo-categorias', [ExternalApiProxyController::class, 'getCategorias'])->name('categorias')->middleware('action.permission:envios,read');
            Route::get('/catalogo-productos', [ExternalApiProxyController::class, 'getProductos'])->name('productos')->middleware('action.permission:envios,read');
            Route::get('/catalogo-tipos-empaque', [ExternalApiProxyController::class, 'getTiposEmpaque'])->name('tipos-empaque')->middleware('action.permission:envios,read');
            Route::get('/catalogo-tamano-conteo', [ExternalApiProxyController::class, 'getTamanoConteo'])->name('tamano-conteo')->middleware('action.permission:envios,read');
            Route::get('/tipo-transporte', [ExternalApiProxyController::class, 'getTiposTransporte'])->name('tipos-transporte')->middleware('action.permission:envios,read');

            // Envíos
            Route::post('/direccion', [ExternalApiProxyController::class, 'crearDireccion'])->name('direccion')->middleware('action.permission:direcciones,read');
            Route::post('/crear-envio', [ExternalApiProxyController::class, 'crearEnvioProductor'])->name('crear-envio')->middleware('action.permission:envios,create');
            Route::get('/envios', [ExternalApiProxyController::class, 'getEnvios'])->name('envios')->middleware('action.permission:envios,read');
            Route::get('/envios/{id}', [ExternalApiProxyController::class, 'getEnvioDetalle'])->name('envio-detalle')->middleware('action.permission:envios,read');
            Route::get('/transportistas', [ExternalApiProxyController::class, 'getTransportistas'])->name('transportistas')->middleware('action.permission:transportistas,read');
            Route::get('/vehiculos', [ExternalApiProxyController::class, 'getVehiculos'])->name('vehiculos')->middleware('action.permission:vehiculos,read');
        });
    });

    // ==============================
    // ORGTRACK / FUSION - CRUD locales
    Route::prefix('orgtrack')->name('orgtrack.')->group(function () {
        // Transportistas (CRUD sobre `usuario` role=transportista)
        Route::get('transportistas', [TransportistaController::class, 'index'])->name('transportistas.index')->middleware('action.permission:transportistas,read');
        Route::get('transportistas/create', [TransportistaController::class, 'create'])->name('transportistas.create')->middleware('action.permission:transportistas,create');
        Route::post('transportistas', [TransportistaController::class, 'store'])->name('transportistas.store')->middleware('action.permission:transportistas,create');
        Route::get('transportistas/{transportista}/edit', [TransportistaController::class, 'edit'])->name('transportistas.edit')->middleware('action.permission:transportistas,update');
        Route::put('transportistas/{transportista}', [TransportistaController::class, 'update'])->name('transportistas.update')->middleware('action.permission:transportistas,update');
        Route::delete('transportistas/{transportista}', [TransportistaController::class, 'destroy'])->name('transportistas.destroy')->middleware('action.permission:transportistas,delete');

        // Envios - Fusion (CRUD sobre envio_asignacion_multiple)
        Route::get('envios', [EnvioFusionController::class, 'index'])->name('envios.index')->middleware('action.permission:envios,read');
        Route::get('envios/{envio}', [EnvioFusionController::class, 'show'])->name('envios.show')->middleware('action.permission:envios,read');
        Route::get('envios/{envio}/edit', [EnvioFusionController::class, 'edit'])->name('envios.edit')->middleware('action.permission:envios,update');
        Route::put('envios/{envio}', [EnvioFusionController::class, 'update'])->name('envios.update')->middleware('action.permission:envios,update');
        Route::delete('envios/{envio}', [EnvioFusionController::class, 'destroy'])->name('envios.destroy')->middleware('action.permission:envios,delete');
    });
    // LOGISTICA OPERATIVA (SISTEMA PLANTA)
    // ==============================
    Route::prefix('logistica')->name('logistica.')->group(function () {
        Route::get('/asignaciones', [AsignacionMultipleController::class, 'index'])
            ->name('asignaciones.index')
            ->middleware('action.permission:asignaciones,read');
        Route::get('/asignaciones/create', [AsignacionMultipleController::class, 'create'])
            ->name('asignaciones.create')
            ->middleware('action.permission:asignaciones,create');
        Route::post('/asignaciones', [AsignacionMultipleController::class, 'store'])
            ->name('asignaciones.store')
            ->middleware('action.permission:asignaciones,create');
        Route::post('/asignaciones/lote', [AsignacionMultipleController::class, 'storeBatch'])
            ->name('asignaciones.store-batch')
            ->middleware('action.permission:asignaciones,multiple');
        Route::post('/asignaciones/asignar-automatica', [AsignacionMultipleController::class, 'asignarAutomatica'])
            ->name('asignaciones.asignar-automatica')
            ->middleware('action.permission:asignaciones,multiple');
        Route::patch('/asignaciones/{asignacion}/en-transporte', [AsignacionMultipleController::class, 'markEnTransportePlanta'])
            ->name('asignaciones.en-transporte')
            ->middleware('action.permission:asignaciones,update');
        Route::patch('/asignaciones/{asignacion}/recepcion', [AsignacionMultipleController::class, 'markDelivered'])
            ->name('asignaciones.mark-delivered')
            ->middleware('action.permission:asignaciones,create');

        Route::get('/rutas-multi', [RutaMultiEntregaController::class, 'index'])
            ->name('rutas.index')
            ->middleware('action.permission:rutas_multi,read');
        Route::get('/rutas-multi/create', [RutaMultiEntregaController::class, 'create'])
            ->name('rutas.create')
            ->middleware('action.permission:rutas_multi,create');
        Route::post('/rutas-multi', [RutaMultiEntregaController::class, 'store'])
            ->name('rutas.store')
            ->middleware('action.permission:rutas_multi,create');
        Route::get('/rutas-multi/mapa', [RutaMultiEntregaController::class, 'mapa'])
            ->name('rutas.mapa')
            ->middleware('action.permission:rutas_multi,create');
        Route::get('/rutas-multi/generar-automatica/vista-previa', [RutaMultiEntregaController::class, 'previewGenerarAutomatica'])
            ->name('rutas.generar-automatica.preview')
            ->middleware('action.permission:rutas_multi,create');
        Route::post('/rutas-multi/generar-automatica', [RutaMultiEntregaController::class, 'generarAutomatica'])
            ->name('rutas.generar-automatica')
            ->middleware('action.permission:rutas_multi,create');
        Route::get('/rutas-multi/{ruta}/trazado', [RutaMultiEntregaController::class, 'trazado'])
            ->name('rutas.trazado')
            ->middleware('action.permission:rutas_multi,read');
        Route::get('/rutas-multi/{ruta}', [RutaMultiEntregaController::class, 'show'])
            ->name('rutas.show')
            ->middleware('action.permission:rutas_multi,read');
        Route::patch('/rutas-multi/{ruta}', [RutaMultiEntregaController::class, 'update'])
            ->name('rutas.update')
            ->middleware('action.permission:rutas_multi,update');
        Route::patch('/rutas-multi/{ruta}/reordenar', [RutaMultiEntregaController::class, 'reorder'])
            ->name('rutas.reorder')
            ->middleware('action.permission:rutas_multi,reorder');

        Route::get('/incidentes', [IncidenteEnvioController::class, 'index'])
            ->name('incidentes.index')
            ->middleware('action.permission:incidentes,read');
        Route::get('/incidentes/create', [IncidenteEnvioController::class, 'create'])
            ->name('incidentes.create')
            ->middleware('action.permission:incidentes,create');
        Route::post('/incidentes', [IncidenteEnvioController::class, 'store'])
            ->name('incidentes.store')
            ->middleware('action.permission:incidentes,create');
        Route::get('/incidentes/{incidente}', [IncidenteEnvioController::class, 'show'])
            ->name('incidentes.show')
            ->middleware('action.permission:incidentes,read');
        Route::get('/incidentes/{incidente}/edit', [IncidenteEnvioController::class, 'edit'])
            ->name('incidentes.edit')
            ->middleware('action.permission:incidentes,update');
        Route::put('/incidentes/{incidente}', [IncidenteEnvioController::class, 'update'])
            ->name('incidentes.update')
            ->middleware('action.permission:incidentes,update');
        Route::delete('/incidentes/{incidente}', [IncidenteEnvioController::class, 'destroy'])
            ->name('incidentes.destroy')
            ->middleware('action.permission:incidentes,delete');
        Route::patch('/incidentes/{incidente}/resolver', [IncidenteEnvioController::class, 'resolve'])
            ->name('incidentes.resolve')
            ->middleware('action.permission:incidentes,resolve');

        Route::get('/documentos', [DocumentoEntregaController::class, 'index'])
            ->name('documentos.index')
            ->middleware('action.permission:documentos,read');
        Route::post('/documentos', [DocumentoEntregaController::class, 'store'])
            ->name('documentos.store')
            ->middleware('action.permission:documentos,create');
        Route::get('/documentos/{documento}', [DocumentoEntregaController::class, 'show'])
            ->name('documentos.show')
            ->middleware('action.permission:documentos,read');
        Route::get('/documentos/{documento}/edit', [DocumentoEntregaController::class, 'edit'])
            ->name('documentos.edit')
            ->middleware('action.permission:documentos,update');
        Route::put('/documentos/{documento}', [DocumentoEntregaController::class, 'update'])
            ->name('documentos.update')
            ->middleware('action.permission:documentos,update');
        Route::delete('/documentos/{documento}', [DocumentoEntregaController::class, 'destroy'])
            ->name('documentos.destroy')
            ->middleware('action.permission:documentos,delete');
        Route::get('/documentos/{documento}/download', [DocumentoEntregaController::class, 'download'])
            ->name('documentos.download')
            ->middleware('action.permission:documentos,read');
    });

    // ==============================
    // TRANSACCIONES AGRÍCOLAS - ELIMINADO
    // ==============================
});