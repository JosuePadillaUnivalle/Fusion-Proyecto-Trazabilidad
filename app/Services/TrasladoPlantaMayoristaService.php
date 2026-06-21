<?php



namespace App\Services;



use App\Models\Almacen;

use App\Models\AlmacenMovimiento;

use App\Models\DetalleTrasladoPlantaMayorista;

use App\Models\Insumo;

use App\Models\InsumoPresentacion;

use App\Models\InventarioPresentacionLote;
use App\Models\RutaDistribucion;

use App\Models\RutaDistribucionParada;

use App\Models\TipoMovimientoAlmacen;

use App\Models\Usuario;

use App\Models\Vehiculo;

use App\Services\NotificacionUsuarioService;

use App\Support\AlmacenAmbito;

use App\Support\InsumoCatalogo;

use App\Support\MayoristaAccess;

use App\Support\RutaDistribucionCatalogo;

use App\Support\TransportistaFlotaCatalogo;

use App\Support\UbicacionGpsParser;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

use InvalidArgumentException;



class TrasladoPlantaMayoristaService

{

    public function __construct(
        private readonly NotificacionUsuarioService $notificaciones,
        private readonly InventarioPresentacionService $inventarioPresentacion,
        private readonly TransporteCapacidadService $capacidadTransporte,
    ) {}

    public function crear(

        Almacen $plantaOrigen,

        Almacen $mayoristaDestino,

        int $transportistaId,

        ?int $vehiculoId,

        int $creadoPorId,

        array $detalles,

        ?string $nombre = null,

        ?float $costoBs = null

    ): RutaDistribucion {

        if ($plantaOrigen->ambito !== AlmacenAmbito::PLANTA) {

            throw new InvalidArgumentException('El origen debe ser un almacén de planta.');

        }



        if ($mayoristaDestino->ambito !== AlmacenAmbito::MAYORISTA) {

            throw new InvalidArgumentException('El destino debe ser un almacén mayorista.');

        }



        $coordsPlanta = UbicacionGpsParser::resolverAlmacen(

            (int) $plantaOrigen->almacenid,

            $plantaOrigen->nombre,

            $plantaOrigen->ubicacion

        );

        $coordsMayorista = UbicacionGpsParser::resolverAlmacen(

            (int) $mayoristaDestino->almacenid,

            $mayoristaDestino->nombre,

            $mayoristaDestino->ubicacion

        );



        if ($coordsPlanta === null || $coordsMayorista === null) {

            throw new InvalidArgumentException('Origen y destino deben tener ubicación GPS para planificar la ruta.');

        }



        $detallesNormalizados = $this->normalizarDetalles($detalles, (int) $plantaOrigen->almacenid);

        $this->validarFlota($transportistaId, $vehiculoId);

        $this->validarCapacidadVehiculo($vehiculoId, $detallesNormalizados);



        return DB::transaction(function () use (

            $plantaOrigen,

            $mayoristaDestino,

            $transportistaId,

            $vehiculoId,

            $creadoPorId,

            $nombre,

            $costoBs,

            $coordsPlanta,

            $coordsMayorista,

            $detallesNormalizados

        ) {

            $ruta = RutaDistribucion::create([

                'codigo' => RutaDistribucionCatalogo::generarCodigoTraslado(),

                'nombre' => $nombre ?: 'Traslado '.$plantaOrigen->nombre.' → '.$mayoristaDestino->nombre,

                'tipo_ruta' => RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA,

                'almacen_planta_origenid' => $plantaOrigen->almacenid,

                'almacen_mayorista_origenid' => null,

                'almacen_mayorista_destinoid' => $mayoristaDestino->almacenid,

                'transportista_usuarioid' => $transportistaId,

                'vehiculoid' => $vehiculoId,

                'costo_bs' => $costoBs !== null ? round($costoBs, 2) : null,

                'creado_por_usuarioid' => $creadoPorId,

                'estado' => RutaDistribucionCatalogo::ESTADO_PENDIENTE_APROBACION,

                'fecha_salida' => null,

            ]);



            RutaDistribucionParada::create([

                'rutadistribucionid' => $ruta->rutadistribucionid,

                'orden' => 1,

                'tipo' => RutaDistribucionCatalogo::PARADA_CARGA_PLANTA,

                'almacenid' => $plantaOrigen->almacenid,

                'destino' => 'Carga: '.$plantaOrigen->nombre,

                'latitud' => $coordsPlanta['lat'],

                'longitud' => $coordsPlanta['lng'],

                'estado' => 'completada',

            ]);



            RutaDistribucionParada::create([

                'rutadistribucionid' => $ruta->rutadistribucionid,

                'orden' => 2,

                'tipo' => RutaDistribucionCatalogo::PARADA_ENTREGA_MAYORISTA,

                'almacenid' => $mayoristaDestino->almacenid,

                'destino' => 'Entrega: '.$mayoristaDestino->nombre,

                'latitud' => $coordsMayorista['lat'],

                'longitud' => $coordsMayorista['lng'],

                'estado' => 'pendiente',

            ]);



            foreach ($detallesNormalizados as $detalle) {

                DetalleTrasladoPlantaMayorista::create([

                    'rutadistribucionid' => $ruta->rutadistribucionid,

                    'insumoid' => $detalle['insumoid'],

                    'insumo_presentacionid' => $detalle['insumo_presentacionid'] ?? null,

                    'inventario_presentacion_loteid' => $detalle['inventario_presentacion_loteid'] ?? null,

                    'loteproduccionpedidoid' => $detalle['loteproduccionpedidoid'] ?? null,

                    'presentacion_nombre' => $detalle['presentacion_nombre'] ?? null,

                    'producto_nombre' => $detalle['producto_nombre'],

                    'cantidad' => $detalle['cantidad'],

                    'cantidad_unidades' => $detalle['cantidad_unidades'] ?? null,

                    'observaciones' => $detalle['observaciones'] ?? null,

                ]);

            }



            $ruta = $ruta->load([

                'paradas',

                'transportista',

                'vehiculo',

                'almacenPlantaOrigen',

                'almacenMayoristaDestino',

                'detallesTraslado.insumo.unidadMedida',

            ]);

            $this->notificaciones->trasladoPlantaPendienteAprobacion($ruta);

            return $ruta;

        });

    }



    public function aceptar(RutaDistribucion $ruta, Usuario $usuario): RutaDistribucion
    {
        if (! RutaDistribucionCatalogo::puedeAceptarMayorista($ruta)) {
            throw new InvalidArgumentException('Este traslado ya fue procesado o no está pendiente de aprobación.');
        }

        if (! MayoristaAccess::puedeGestionarTraslado($usuario, $ruta)) {
            throw new InvalidArgumentException('No tiene permiso para aprobar este traslado.');
        }

        $ruta->update([
            'estado' => RutaDistribucionCatalogo::ESTADO_PLANIFICADA,
            'fecha_aprobacion_mayorista' => now(),
            'aprobado_por_usuarioid' => $usuario->usuarioid,
            'motivo_rechazo_mayorista' => null,
        ]);

        $ruta = $ruta->fresh([
            'transportista',
            'almacenPlantaOrigen',
            'almacenMayoristaDestino',
            'detallesTraslado',
        ]);

        $this->notificaciones->trasladoPlantaAceptado($ruta);
        $this->notificaciones->trasladoPlantaListoParaRecoger($ruta);

        return $ruta;
    }



    public function rechazar(RutaDistribucion $ruta, Usuario $usuario, ?string $motivo = null): RutaDistribucion
    {
        if (! RutaDistribucionCatalogo::puedeAceptarMayorista($ruta)) {
            throw new InvalidArgumentException('Este traslado ya fue procesado o no está pendiente de aprobación.');
        }

        if (! MayoristaAccess::puedeGestionarTraslado($usuario, $ruta)) {
            throw new InvalidArgumentException('No tiene permiso para rechazar este traslado.');
        }

        $ruta->update([
            'estado' => RutaDistribucionCatalogo::ESTADO_RECHAZADA,
            'motivo_rechazo_mayorista' => $motivo ? trim($motivo) : null,
        ]);

        $ruta = $ruta->fresh(['almacenPlantaOrigen', 'almacenMayoristaDestino', 'creadoPor']);

        $this->notificaciones->trasladoPlantaRechazado($ruta);

        return $ruta;
    }



    public function transferirInventarioAlCompletar(RutaDistribucion $ruta, Usuario $usuario): void

    {

        $ruta->loadMissing([

            'detallesTraslado.insumo.unidadMedida',

            'almacenPlantaOrigen',

            'almacenMayoristaDestino',

        ]);



        if (! $ruta->esTrasladoPlantaMayorista()) {

            throw new InvalidArgumentException('La ruta no es un traslado planta → mayorista.');

        }



        $almacenMayorista = $ruta->almacenMayoristaDestino;

        if ($almacenMayorista === null) {

            throw new InvalidArgumentException('El traslado no tiene almacén mayorista destino.');

        }



        if ($ruta->detallesTraslado->isEmpty()) {

            throw new InvalidArgumentException('El traslado no tiene productos registrados.');

        }



        $tipoIngreso = TipoMovimientoAlmacen::activosPorNaturaleza('ingreso')->firstOrFail();

        $tipoSalida = TipoMovimientoAlmacen::activosPorNaturaleza('salida')->firstOrFail();

        $ref = $ruta->codigo;



        DB::transaction(function () use ($ruta, $usuario, $almacenMayorista, $tipoIngreso, $tipoSalida, $ref) {

            foreach ($ruta->detallesTraslado as $detalle) {

                $this->transferirDetalle(

                    $detalle,

                    $almacenMayorista,

                    $usuario,

                    $tipoIngreso,

                    $tipoSalida,

                    $ref

                );

            }

        });

    }



    public function trayectoTexto(RutaDistribucion $ruta): ?string

    {

        $ruta->loadMissing(['almacenPlantaOrigen', 'almacenMayoristaDestino']);



        if (! $ruta->esTrasladoPlantaMayorista()) {

            return null;

        }



        $origen = $ruta->almacenPlantaOrigen?->nombre ?? 'Planta';

        $destino = $ruta->almacenMayoristaDestino?->nombre ?? 'Almacén mayorista';



        return $origen.' → '.$destino;

    }



    /** @return list<array{insumoid: int, insumo_presentacionid: ?int, inventario_presentacion_loteid: ?int, loteproduccionpedidoid: ?int, presentacion_nombre: ?string, producto_nombre: string, cantidad: float, cantidad_unidades: ?float, observaciones: ?string}> */

    private function normalizarDetalles(array $detalles, int $almacenPlantaId): array

    {

        if ($detalles === []) {

            throw new InvalidArgumentException('Indique al menos un producto a trasladar desde planta.');

        }



        $normalizados = [];

        $vistos = [];



        foreach ($detalles as $detalle) {

            $insumoId = (int) ($detalle['insumoid'] ?? 0);

            $presentacionId = (int) ($detalle['insumo_presentacionid'] ?? 0);

            $inventarioId = (int) ($detalle['inventario_presentacion_loteid'] ?? 0);

            $cantidadUnidades = (float) ($detalle['cantidad_unidades'] ?? 0);

            $cantidad = (float) ($detalle['cantidad'] ?? 0);



            if ($insumoId <= 0) {

                throw new InvalidArgumentException('Cada línea debe incluir un producto válido.');

            }



            $claveLinea = $insumoId.'-'.$presentacionId.'-'.$inventarioId;

            if (isset($vistos[$claveLinea])) {

                throw new InvalidArgumentException('No repita la misma presentación y lote en el traslado.');

            }



            $insumo = Insumo::query()

                ->with('unidadMedida')

                ->where('insumoid', $insumoId)

                ->where('almacenid', $almacenPlantaId)

                ->first();



            if ($insumo === null) {

                throw new InvalidArgumentException('Uno de los productos no pertenece al almacén de planta seleccionado.');

            }

            if ((int) $insumo->tipoinsumoid !== InsumoCatalogo::tipoProductoTerminadoId()) {

                throw new InvalidArgumentException(

                    '«'.$insumo->nombre.'» no es un producto terminado. Solo puede enviar productos ya procesados en planta al mayorista (no cosecha cruda).'

                );

            }



            $presentacion = null;

            $presentacionNombre = null;

            $inventarioLote = null;

            $loteProduccionId = null;

            $tienePresentaciones = InsumoPresentacion::query()

                ->where('insumoid', $insumoId)

                ->where('activo', true)

                ->exists();



            if ($presentacionId > 0) {

                $presentacion = InsumoPresentacion::query()

                    ->where('insumo_presentacionid', $presentacionId)

                    ->where('insumoid', $insumoId)

                    ->where('activo', true)

                    ->first();



                if ($presentacion === null) {

                    throw new InvalidArgumentException('La presentación seleccionada no es válida para «'.$insumo->nombre.'».');

                }



                if ($cantidadUnidades <= 0) {

                    throw new InvalidArgumentException('Indique cuántas unidades envía de «'.$presentacion->nombre.'».');

                }



                $cantidad = round($cantidadUnidades * $presentacion->pesoNetoKg(), 4);

                $presentacionNombre = $presentacion->nombre;



                $lotesDisponibles = $this->inventarioPresentacion->lotesDisponibles($almacenPlantaId, $presentacionId);

                if ($lotesDisponibles->isEmpty()) {
                    $this->inventarioPresentacion->asegurarInventarioDesdeStock($almacenPlantaId, $insumoId);
                    $lotesDisponibles = $this->inventarioPresentacion->lotesDisponibles($almacenPlantaId, $presentacionId);
                }

                if ($lotesDisponibles->isEmpty()) {
                    throw new InvalidArgumentException(
                        'No hay stock por lote para «'.$presentacion->nombre.'». Registre inventario envasado en planta.'
                    );
                }

                if ($inventarioId <= 0) {
                    throw new InvalidArgumentException('Seleccione el lote de «'.$presentacion->nombre.'» a despachar.');
                }

                $inventarioLote = $this->inventarioPresentacion->obtenerLote($inventarioId, $almacenPlantaId, $presentacionId);
                $this->inventarioPresentacion->validarDisponibilidad($inventarioLote, $cantidadUnidades, $cantidad);
                $loteProduccionId = $inventarioLote->loteproduccionpedidoid;

            } elseif ($tienePresentaciones) {

                throw new InvalidArgumentException('Seleccione la presentación comercial de «'.$insumo->nombre.'».');

            } elseif ($cantidad <= 0) {

                throw new InvalidArgumentException('Cada producto debe tener cantidad mayor a cero.');

            } elseif (! $insumo->tieneStockSuficiente($cantidad)) {

                $unidad = $insumo->unidadMedida?->abreviatura ?? '';

                throw new InvalidArgumentException(

                    'Stock insuficiente para «'.$insumo->nombre.'»: solicitado '.$cantidad.' '.$unidad

                    .', disponible '.number_format((float) $insumo->stock, 2).' '.$unidad.'.'

                );

            }



            $vistos[$claveLinea] = true;

            $normalizados[] = [

                'insumoid' => $insumoId,

                'insumo_presentacionid' => $presentacion?->insumo_presentacionid,

                'inventario_presentacion_loteid' => $inventarioLote?->inventario_presentacion_loteid,

                'loteproduccionpedidoid' => $loteProduccionId,

                'presentacion_nombre' => $presentacionNombre,

                'producto_nombre' => $insumo->nombre,

                'cantidad' => $cantidad,

                'cantidad_unidades' => $presentacion !== null ? $cantidadUnidades : null,

                'observaciones' => isset($detalle['observaciones']) ? trim((string) $detalle['observaciones']) : null,

            ];

        }



        return $normalizados;

    }



    private function transferirDetalle(

        DetalleTrasladoPlantaMayorista $detalle,

        Almacen $almacenMayorista,

        Usuario $usuario,

        TipoMovimientoAlmacen $tipoIngreso,

        TipoMovimientoAlmacen $tipoSalida,

        string $ref

    ): void {

        $cantidad = (float) $detalle->cantidad;

        $cantidadUnidades = (float) ($detalle->cantidad_unidades ?? 0);

        $insumoOrigen = $detalle->insumo;

        $detalle->loadMissing(['presentacion', 'inventarioLote']);



        if ($insumoOrigen === null) {

            throw new InvalidArgumentException('Producto de planta no encontrado en el traslado.');

        }



        if ($detalle->inventario_presentacion_loteid && $detalle->inventarioLote) {

            $this->inventarioPresentacion->validarDisponibilidad(

                $detalle->inventarioLote,

                $cantidadUnidades,

                $cantidad

            );

        } elseif (! $insumoOrigen->tieneStockSuficiente($cantidad)) {

            throw new InvalidArgumentException(

                "Stock insuficiente en planta para «{$insumoOrigen->nombre}». Disponible: {$insumoOrigen->stock}."

            );

        }



        $insumoDestino = Insumo::query()

            ->where('almacenid', $almacenMayorista->almacenid)

            ->whereRaw('LOWER(TRIM(nombre)) = ?', [Str::lower(trim($insumoOrigen->nombre))])

            ->first();



        if ($insumoDestino === null) {

            $insumoDestino = Insumo::create([

                'nombre' => $insumoOrigen->nombre,

                'codigo_trazabilidad' => 'TRZ-MAY-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6)),

                'tipoinsumoid' => $insumoOrigen->tipoinsumoid,

                'unidadmedidaid' => $insumoOrigen->unidadmedidaid,

                'stock' => 0,

                'stockminimo' => InsumoCatalogo::UMBRAL_ALERTA_STOCK,

                'descripcion' => 'Producto recibido desde planta — '.$ref,

                'almacenid' => $almacenMayorista->almacenid,

            ]);

        }



        AlmacenMovimiento::create([

            'almacenid' => $insumoOrigen->almacenid,

            'insumoid' => $insumoOrigen->insumoid,

            'insumo_presentacionid' => $detalle->insumo_presentacionid,

            'loteproduccionpedidoid' => $detalle->loteproduccionpedidoid,

            'tipo_movimiento_almacenid' => $tipoSalida->tipo_movimiento_almacenid,

            'usuarioid' => $usuario->usuarioid,

            'fecha' => now()->toDateString(),

            'cantidad' => $cantidad,

            'cantidad_unidades' => $cantidadUnidades > 0 ? $cantidadUnidades : null,

            'referencia' => $ref,

            'destino_motivo' => $almacenMayorista->nombre,

            'observaciones' => '[Traslado planta → mayorista — salida] '.$ref,

        ]);



        AlmacenMovimiento::create([

            'almacenid' => $almacenMayorista->almacenid,

            'insumoid' => $insumoDestino->insumoid,

            'insumo_presentacionid' => $detalle->insumo_presentacionid,

            'loteproduccionpedidoid' => $detalle->loteproduccionpedidoid,

            'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,

            'usuarioid' => $usuario->usuarioid,

            'fecha' => now()->toDateString(),

            'cantidad' => $cantidad,

            'cantidad_unidades' => $cantidadUnidades > 0 ? $cantidadUnidades : null,

            'referencia' => $ref,

            'destino_motivo' => $almacenMayorista->nombre,

            'observaciones' => '[Traslado planta → mayorista — ingreso] '.$ref,

        ]);



        if ($detalle->inventarioLote && $cantidadUnidades > 0) {

            $this->inventarioPresentacion->descontar($detalle->inventarioLote, $cantidadUnidades, $cantidad);

            if ($detalle->presentacion) {

                $presentacionDestino = $this->inventarioPresentacion->replicarPresentacionEnInsumo(

                    $detalle->presentacion,

                    $insumoDestino

                );

                $this->inventarioPresentacion->ingresar(

                    (int) $almacenMayorista->almacenid,

                    (int) $insumoDestino->insumoid,

                    (int) $presentacionDestino->insumo_presentacionid,

                    $detalle->loteproduccionpedidoid,

                    $detalle->inventarioLote->referencia_lote,

                    $cantidadUnidades,

                    $cantidad

                );

            }

        } else {

            $insumoOrigen->decrementarStock($cantidad);

            $insumoDestino->incrementarStock($cantidad);

        }

    }



    private function validarFlota(int $transportistaId, ?int $vehiculoId): void

    {

        $transportista = Usuario::query()

            ->with('perfilTransportista')

            ->where('usuarioid', $transportistaId)

            ->where('role', 'transportista')

            ->where('activo', true)

            ->first();



        if ($transportista === null) {

            throw new InvalidArgumentException('El transportista seleccionado no está disponible.');

        }



        $ambito = $transportista->perfilTransportista?->ambito_flota ?? TransportistaFlotaCatalogo::AGRICOLA;

        if ($ambito !== TransportistaFlotaCatalogo::PLANTA) {

            throw new InvalidArgumentException('Seleccione un chofer de flota planta.');

        }



        if ($vehiculoId === null) {

            return;

        }



        $vehiculo = Vehiculo::query()

            ->where('vehiculoid', $vehiculoId)

            ->where('activo', true)

            ->first();



        if ($vehiculo === null) {

            throw new InvalidArgumentException('El vehículo seleccionado no está disponible.');

        }



        if ($vehiculo->ambito_flota !== null && $vehiculo->ambito_flota !== TransportistaFlotaCatalogo::PLANTA) {

            throw new InvalidArgumentException('Seleccione un vehículo de flota planta.');

        }

    }



    /** @param  list<array{cantidad: float}>  $detalles */

    private function validarCapacidadVehiculo(?int $vehiculoId, array $detalles): void

    {

        if ($vehiculoId === null) {

            throw new InvalidArgumentException('Seleccione un vehículo para validar la capacidad de carga.');

        }



        $pesoTotal = array_sum(array_map(fn (array $d) => (float) $d['cantidad'], $detalles));

        if ($pesoTotal <= 0) {

            return;

        }



        $vehiculo = Vehiculo::query()

            ->where('vehiculoid', $vehiculoId)

            ->where('activo', true)

            ->first();



        if ($vehiculo === null) {

            throw new InvalidArgumentException('El vehículo seleccionado no está disponible.');

        }



        $this->capacidadTransporte->validarCarga($vehiculo, $pesoTotal);

    }

}


