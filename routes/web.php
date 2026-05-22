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
use App\Http\Controllers\Web\UserProfileController;

// 🔹 nuevos controladores web de almacenamiento
use App\Http\Controllers\Web\TipoAlmacenController;
use App\Http\Controllers\Web\AlmacenController;
use App\Http\Controllers\Web\ProduccionAlmacenamientoController;



// 🔹 Dashboard Controller
use App\Http\Controllers\Web\DashboardController;

// 🔹 Reportes Controller
use App\Http\Controllers\Web\ReporteController;

// 🔹 Catálogos Controller
use App\Http\Controllers\Web\CatalogoController;

// 🔹 External API Proxy Controller
use App\Http\Controllers\Web\ExternalApiProxyController;
use App\Http\Controllers\Web\CertificacionController;
use App\Http\Controllers\Web\ActorAbastecimientoController;
use App\Http\Controllers\Web\RecursoProductivoController;
use App\Http\Controllers\Web\ProcesoPlantaController;
use App\Http\Controllers\Web\MaquinaPlantaController;
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

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// RUTAS PROTEGIDAS (REQUIEREN ESTAR LOGUEADO)

Route::middleware('auth')->group(function () {

    // Perfil de Usuario
    Route::get('/perfil', [UserProfileController::class, 'show'])->name('profile.show');
    Route::put('/perfil', [UserProfileController::class, 'update'])->name('profile.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/panel-planta', [DashboardController::class, 'panelPlanta'])->name('dashboard.panel-planta');
    Route::get('/dashboard/panel-transportista', [DashboardController::class, 'panelTransportista'])->name('dashboard.panel-transportista');
    Route::get('/dashboard/panel-almacen', [DashboardController::class, 'panelAlmacen'])->name('dashboard.panel-almacen');

    // Catálogos centralizados
    Route::get('/catalogos', [CatalogoController::class, 'index'])
        ->name('catalogos.index')
        ->middleware('action.permission:catalogos,read');

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
    Route::get('recursos-productivos', [RecursoProductivoController::class, 'index'])->name('recursos-productivos.index')->middleware('action.permission:inventario,read');
    Route::get('lotes/mapa', [LoteController::class, 'mapa'])->name('lotes.mapa')->middleware('action.permission:lotes,read');
    Route::get('lotes', [LoteController::class, 'index'])->name('lotes.index')->middleware('action.permission:lotes,read');
    Route::get('lotes/create', [LoteController::class, 'create'])->name('lotes.create')->middleware('action.permission:lotes,create');
    Route::post('lotes/sincronizar-operacion', [LoteController::class, 'sincronizarOperacion'])->name('lotes.sincronizar-operacion')->middleware('action.permission:lotes,update');
    Route::post('lotes', [LoteController::class, 'store'])->name('lotes.store')->middleware('action.permission:lotes,create');
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
    Route::resource('procesos-planta', ProcesoPlantaController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('maquinas-planta', MaquinaPlantaController::class)->only(['index', 'store', 'update', 'destroy']);
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
    // Evita conflicto /almacenes/create vs /almacenes/{almacen} (404 por model binding)
    Route::get('almacenes', [AlmacenController::class, 'index'])->name('almacenes.index')->middleware('action.permission:inventario,read');
    Route::get('almacenes/create', [AlmacenController::class, 'create'])->name('almacenes.create')->middleware('action.permission:inventario,create');
    Route::post('almacenes', [AlmacenController::class, 'store'])->name('almacenes.store')->middleware('action.permission:inventario,create');
    Route::get('almacenes/{almacen}', [AlmacenController::class, 'show'])->name('almacenes.show')->middleware('action.permission:inventario,read');
    Route::get('almacenes/{almacen}/edit', [AlmacenController::class, 'edit'])->name('almacenes.edit')->middleware('action.permission:inventario,update');
    Route::put('almacenes/{almacen}', [AlmacenController::class, 'update'])->name('almacenes.update')->middleware('action.permission:inventario,update');
    Route::delete('almacenes/{almacen}', [AlmacenController::class, 'destroy'])->name('almacenes.destroy')->middleware('action.permission:inventario,delete');
    Route::resource('producciones_almacenamiento', ProduccionAlmacenamientoController::class)->only(['index', 'show'])->middleware('action.permission:inventario,read');
    Route::resource('producciones_almacenamiento', ProduccionAlmacenamientoController::class)->only(['create', 'store'])->middleware('action.permission:inventario,create');
    Route::resource('producciones_almacenamiento', ProduccionAlmacenamientoController::class)->only(['edit', 'update'])->middleware('action.permission:inventario,update');
    Route::resource('producciones_almacenamiento', ProduccionAlmacenamientoController::class)->only(['destroy'])->middleware('action.permission:inventario,delete');
    Route::get('almacen-movimientos', [AlmacenMovimientoController::class, 'index'])
        ->name('almacen-movimientos.index')
        ->middleware('action.permission:almacen_movimientos,read');
    Route::get('almacen-movimientos/{naturaleza}/create', [AlmacenMovimientoController::class, 'create'])
        ->name('almacen-movimientos.create')
        ->middleware('action.permission:almacen_movimientos,read');
    Route::post('almacen-movimientos/{naturaleza}', [AlmacenMovimientoController::class, 'store'])
        ->name('almacen-movimientos.store')
        ->middleware('action.permission:almacen_movimientos,read');
    Route::get('almacen-reportes', [AlmacenMovimientoController::class, 'reportes'])
        ->name('almacen-movimientos.reportes')
        ->middleware('action.permission:almacen_reportes,read');
    Route::get('/certificaciones', [CertificacionController::class, 'index'])->name('certificaciones.index')->middleware('action.permission:certificaciones,read');
    Route::post('/certificaciones', [CertificacionController::class, 'store'])->name('certificaciones.store')->middleware('action.permission:certificaciones,create');
    // ==============================
    // PEDIDOS (CLIENTES EXTERNOS)
    // ==============================
    Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index')->middleware('action.permission:pedidos,read');
    Route::get('pedidos/create', [PedidoController::class, 'create'])->name('pedidos.create')->middleware('action.permission:pedidos,create');
    Route::post('pedidos', [PedidoController::class, 'store'])->name('pedidos.store')->middleware('action.permission:pedidos,create');
    Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show')->middleware('action.permission:pedidos,read');
    Route::get('pedidos/{pedido}/edit', [PedidoController::class, 'edit'])->name('pedidos.edit')->middleware('action.permission:pedidos,update');
    Route::put('pedidos/{pedido}', [PedidoController::class, 'update'])->name('pedidos.update')->middleware('action.permission:pedidos,update');
    Route::delete('pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('pedidos.destroy')->middleware('action.permission:pedidos,delete');


    // GESTIÓN UNIFICADA DE USUARIOS

    Route::get('/gestion-usuarios', [GestionUsuariosController::class, 'index'])
        ->middleware('action.permission:usuarios,read')
        ->name('gestion.index');

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


    // REPORTES

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas');
        Route::get('/inventario', [ReporteController::class, 'inventario'])->name('inventario');
        Route::get('/produccion', [ReporteController::class, 'produccion'])->name('produccion');
        Route::get('/climatico', [ReporteController::class, 'climatico'])->name('climatico');
        Route::get('/actividades', [ReporteController::class, 'actividades'])->name('actividades');
        Route::get('/exportar/{tipo}', [ReporteController::class, 'exportar'])->name('exportar');
    });


    // ENVÍOS

    Route::prefix('envios')->name('envios.')->group(function () {
        Route::get('/mandar', fn() => view('envios.mandar-envio'))->name('mandar')->middleware('action.permission:envios,create');
        Route::get('/seguimiento', fn() => view('envios.seguimiento'))->name('seguimiento')->middleware('action.permission:envios,read');
        Route::get('/{id}', fn($id) => view('envios.detalle', ['id' => $id]))->name('detalle')->where('id', '[0-9]+')->middleware('action.permission:envios,read');
        Route::get('/admin', fn() => view('envios.admin'))->name('admin')->middleware('action.permission:envios,admin');
        Route::get('/transportistas', fn() => view('envios.transportistas'))->name('transportistas')->middleware('action.permission:transportistas,read');
        Route::get('/vehiculos', fn() => view('envios.vehiculos'))->name('vehiculos')->middleware('action.permission:vehiculos,read');
        Route::get('/direcciones', fn() => view('envios.direcciones'))->name('direcciones')->middleware('action.permission:direcciones,read');
        Route::get('/reportes-distribucion', [\App\Http\Controllers\Web\OrgTrackReportController::class, 'index'])->name('reportes-distribucion')->middleware('action.permission:reportes,read');

        // ==============================
        // PROXY API EXTERNA (evita CORS)
        // ==============================
        Route::prefix('api')->name('api.')->group(function () {
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
        Route::patch('/incidentes/{incidente}/resolver', [IncidenteEnvioController::class, 'resolve'])
            ->name('incidentes.resolve')
            ->middleware('action.permission:incidentes,resolve');

        Route::get('/documentos', [DocumentoEntregaController::class, 'index'])
            ->name('documentos.index')
            ->middleware('action.permission:documentos,read');
        Route::post('/documentos', [DocumentoEntregaController::class, 'store'])
            ->name('documentos.store')
            ->middleware('action.permission:documentos,create');
        Route::get('/documentos/{documento}/download', [DocumentoEntregaController::class, 'download'])
            ->name('documentos.download')
            ->middleware('action.permission:documentos,read');
    });

    // ==============================
    // TRANSACCIONES AGRÍCOLAS - ELIMINADO
    // ==============================
});