<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\LoteProduccionMateriaPrima;
use App\Models\LoteProduccionPedido;
use App\Models\MateriaPrimaBase;
use App\Models\MateriaPrimaLote;
use App\Models\Pedido;
use App\Models\TipoMovimientoAlmacen;
use App\Models\Usuario;
use App\Support\AlmacenAmbito;
use App\Support\LoteProduccionNombre;
use App\Support\LoteProduccionTrazabilidadService;
use App\Support\ProductoPlantaCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoteProduccionPlantaService
{
    /**
     * @param  list<array{insumoid: int, cantidad: float}>  $lineas
     */
    public function crear(
        Usuario $usuario,
        string $producto,
        ?int $pedidoid,
        ?float $cantidadObjetivo,
        ?int $unidadmedidaid,
        array $lineas,
        ?string $observaciones = null,
        ?int $plantillatransformacionid = null,
        ?string $empaqueCatalogoSlug = null,
        ?string $modoPlanificacion = null,
        ?float $cantidadEmpaquesObjetivo = null,
        ?string $empaqueNombrePersonalizado = null,
        ?float $empaquePesoNetoKg = null,
        ?string $empaqueTipoEnvase = null,
        ?array $parametrosLote = null,
    ): LoteProduccionPedido {
        if ($lineas === []) {
            throw new \InvalidArgumentException('Debe indicar al menos una materia prima del almacén.');
        }

        $producto = LoteProduccionNombre::normalizarProducto($producto);
        if ($producto === '') {
            throw new \InvalidArgumentException('Indique el producto a procesar.');
        }

        $nombre = LoteProduccionNombre::siguienteNombre($producto);

        if ($pedidoid !== null && ! Pedido::query()->whereKey($pedidoid)->exists()) {
            throw new \InvalidArgumentException('El pedido seleccionado no existe.');
        }

        $tipoSalida = $this->tipoMovimientoSalidaProduccion();
        $unidadmedidaid = ProductoPlantaCatalogo::resolverUnidadMedidaId($producto, $unidadmedidaid);

        if ($empaqueCatalogoSlug !== null && ! \App\Support\EmpaquePlantaCatalogo::esSlugValido($empaqueCatalogoSlug)) {
            throw new \InvalidArgumentException('Seleccione un tipo de empaque válido del catálogo.');
        }

        if ($empaqueCatalogoSlug !== null) {
            $unidadmedidaid = ProductoPlantaCatalogo::unidadMedidaIdPorDefecto($producto)
                ?? $unidadmedidaid;
        }

        return DB::transaction(function () use (
            $usuario, $producto, $nombre, $pedidoid, $cantidadObjetivo, $unidadmedidaid, $lineas,
            $observaciones, $tipoSalida, $plantillatransformacionid, $empaqueCatalogoSlug,
            $modoPlanificacion, $cantidadEmpaquesObjetivo, $empaqueNombrePersonalizado,
            $empaquePesoNetoKg, $empaqueTipoEnvase, $parametrosLote
        ) {
            $pedidoIdFinal = $pedidoid ?? $this->crearPedidoInterno($nombre);

            $codigo = 'LOTE-'.str_pad((string) (LoteProduccionPedido::max('loteproduccionpedidoid') + 1), 4, '0', STR_PAD_LEFT).'-'.now()->format('Ymd');

            $loteData = [
                'pedidoid' => $pedidoIdFinal,
                'codigo_lote' => $codigo,
                'nombre' => $nombre,
                'fecha_creacion' => now()->toDateString(),
                'hora_inicio' => now(),
                'cantidad_objetivo' => $cantidadObjetivo,
                'unidadmedidaid' => $unidadmedidaid,
                'observaciones' => $observaciones,
            ];

            if (\Illuminate\Support\Facades\Schema::hasColumn('lote_produccion_pedido', 'producto')) {
                $loteData['producto'] = $producto;
            }

            if ($plantillatransformacionid !== null
                && \Illuminate\Support\Facades\Schema::hasColumn('lote_produccion_pedido', 'plantillatransformacionid')) {
                $loteData['plantillatransformacionid'] = $plantillatransformacionid;
            }

            if ($empaqueCatalogoSlug !== null
                && \Illuminate\Support\Facades\Schema::hasColumn('lote_produccion_pedido', 'empaque_catalogo_slug')) {
                $loteData['empaque_catalogo_slug'] = $empaqueCatalogoSlug;
                $loteData['modo_planificacion'] = $modoPlanificacion;
                $loteData['cantidad_empaques_objetivo'] = $cantidadEmpaquesObjetivo;
                $loteData['empaque_nombre_personalizado'] = $empaqueNombrePersonalizado;
                $loteData['empaque_peso_neto_kg'] = $empaquePesoNetoKg;
                $loteData['empaque_tipo_envase'] = $empaqueTipoEnvase
                    ?: \App\Support\EmpaquePlantaCatalogo::tipoEnvaseDesdePlan($empaqueCatalogoSlug, $empaqueTipoEnvase);
            }

            $lote = LoteProduccionPedido::create($loteData);

            foreach ($lineas as $linea) {
                $this->consumirInsumo(
                    $lote,
                    $usuario,
                    (int) $linea['insumoid'],
                    (float) $linea['cantidad'],
                    $tipoSalida
                );
            }

            if ($plantillatransformacionid !== null) {
                app(\App\Support\LoteProduccionParametrosService::class)->sincronizarDesdeLote(
                    $lote,
                    $plantillatransformacionid,
                    $parametrosLote
                );
                app(\App\Support\LoteProduccionRutaService::class)->inicializarDesdePlantilla(
                    $lote,
                    $plantillatransformacionid
                );
            }

            return $lote->fresh(['pedido', 'materiasPrimas.insumo']);
        });
    }

    private function consumirInsumo(
        LoteProduccionPedido $lote,
        Usuario $usuario,
        int $insumoid,
        float $cantidad,
        TipoMovimientoAlmacen $tipoSalida
    ): void {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }

        $insumo = Insumo::query()->with('almacen')->findOrFail($insumoid);
        $almacen = $insumo->almacen;

        if (! $almacen || ($almacen->ambito ?? '') !== AlmacenAmbito::PLANTA) {
            throw new \InvalidArgumentException("El insumo «{$insumo->nombre}» no pertenece a un almacén de planta.");
        }

        if (! $insumo->tieneStockSuficiente($cantidad)) {
            throw new \InvalidArgumentException(
                "Stock insuficiente de «{$insumo->nombre}». Disponible: ".number_format((float) $insumo->stock, 3)
            );
        }

        AlmacenMovimiento::create([
            'almacenid' => $almacen->almacenid,
            'insumoid' => $insumo->insumoid,
            'tipo_movimiento_almacenid' => $tipoSalida->tipo_movimiento_almacenid,
            'usuarioid' => $usuario->usuarioid,
            'fecha' => now()->toDateString(),
            'cantidad' => $cantidad,
            'referencia' => $lote->codigo_lote,
            'destino_motivo' => 'Lote de producción',
            'observaciones' => '[Consumo lote '.$lote->codigo_lote.'] '.$insumo->nombre,
        ]);

        $insumo->decrementarStock($cantidad);

        $materiaLoteId = $this->resolverMateriaPrimaLoteId($insumo, $cantidad);

        $linea = [
            'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
            'insumoid' => $insumo->insumoid,
            'cantidad_planificada' => $cantidad,
            'cantidad_usada' => $cantidad,
        ];

        if ($materiaLoteId) {
            $linea['materiaprimaloteid'] = $materiaLoteId;
        }

        LoteProduccionMateriaPrima::create($linea);
    }

    private function crearLoteMateriaPrimaDesdeInsumo(Insumo $insumo, float $cantidad): int
    {
        $actorId = \App\Models\ActorAbastecimiento::query()->value('actorid');
        if (! $actorId) {
            throw new \InvalidArgumentException('No hay proveedores configurados para vincular materia prima.');
        }

        $categoriaId = \App\Models\CategoriaMateriaPrima::query()->value('categoriamateriaprimaid')
            ?? \App\Models\CategoriaMateriaPrima::firstOrCreate(
                ['codigo' => 'MP-PLANTA'],
                [
                    'nombre' => 'Materia prima de planta',
                    'descripcion' => 'Insumos del almacén de planta',
                    'activo' => true,
                ]
            )->categoriamateriaprimaid;

        if (! $categoriaId) {
            throw new \InvalidArgumentException('Configure categorías de materia prima antes de crear lotes.');
        }

        $base = MateriaPrimaBase::query()->firstOrCreate(
            ['nombre' => $insumo->nombre],
            [
                'categoriamateriaprimaid' => $categoriaId,
                'codigo' => 'MP-'.$insumo->insumoid,
                'unidadmedidaid' => $insumo->unidadmedidaid,
                'cantidad_disponible' => 0,
                'activo' => true,
            ]
        );

        $loteMp = MateriaPrimaLote::create([
            'materiaprimabaseid' => $base->materiaprimabaseid,
            'proveedor_actorid' => $actorId,
            'fecha_recepcion' => now()->toDateString(),
            'cantidad' => $cantidad,
            'cantidad_disponible' => 0,
            'conformidad_recepcion' => true,
            'observaciones' => 'Generado desde almacén planta (insumo #'.$insumo->insumoid.')',
        ]);

        return (int) $loteMp->materiaprimaloteid;
    }

    private function resolverMateriaPrimaLoteId(Insumo $insumo, float $cantidad): ?int
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('materia_prima_lote')) {
            return null;
        }

        $base = MateriaPrimaBase::query()
            ->where('activo', true)
            ->get()
            ->first(fn (MateriaPrimaBase $b) => Str::lower($b->nombre) === Str::lower($insumo->nombre));

        if (! $base) {
            return null;
        }

        $loteMp = MateriaPrimaLote::query()
            ->where('materiaprimabaseid', $base->materiaprimabaseid)
            ->where('cantidad_disponible', '>=', $cantidad)
            ->orderBy('fecha_recepcion')
            ->first();

        if ($loteMp) {
            $loteMp->cantidad_disponible = max(0, (float) $loteMp->cantidad_disponible - $cantidad);
            $loteMp->save();
            $base->cantidad_disponible = max(0, (float) $base->cantidad_disponible - $cantidad);
            $base->save();

            return $loteMp->materiaprimaloteid;
        }

        return null;
    }

    private function crearPedidoInterno(string $nombreLote): int
    {
        $next = (int) Pedido::max('pedidoid') + 1;
        $numero = 'INT-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT).'-'.now()->format('Ymd');

        $pedido = Pedido::create([
            'numero_solicitud' => $numero,
            'nombre_planta' => 'Producción interna',
            'latitud' => -17.7833,
            'longitud' => -63.1821,
            'direccion_texto' => 'Planta procesadora',
            'estado' => 'en produccion',
            'fechapedido' => now(),
            'observaciones' => 'Pedido interno generado al crear lote: '.$nombreLote,
        ]);

        return (int) $pedido->pedidoid;
    }

    private function tipoMovimientoSalidaProduccion(): TipoMovimientoAlmacen
    {
        $tipo = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'salida')
            ->where('activo', true)
            ->get()
            ->first(fn (TipoMovimientoAlmacen $t) => in_array(
                TipoMovimientoAlmacen::normalizeNombre($t->nombre),
                ['consumo interno', 'produccion', 'producción', 'venta'],
                true
            ));

        return $tipo ?? TipoMovimientoAlmacen::activosPorNaturaleza('salida')->firstOrFail();
    }

    public function puedeEditarMaterias(LoteProduccionPedido $lote): bool
    {
        return app(LoteProduccionTrazabilidadService::class)->resolverFaseActual($lote) === 'creacion';
    }

    public function puedeEliminar(LoteProduccionPedido $lote): bool
    {
        return $this->puedeEditarMaterias($lote);
    }

    /**
     * @param  list<array{insumoid: int, cantidad: float}>|null  $lineas
     */
    public function actualizar(
        Usuario $usuario,
        LoteProduccionPedido $lote,
        ?int $pedidoid,
        ?float $cantidadObjetivo,
        ?int $unidadmedidaid,
        ?string $observaciones,
        ?string $producto = null,
        ?array $lineas = null
    ): LoteProduccionPedido {
        $trz = app(LoteProduccionTrazabilidadService::class);
        $fase = $trz->resolverFaseActual($lote);
        $completado = $fase === 'completado';

        if ($completado) {
            throw new \InvalidArgumentException('No se puede editar un lote completado.');
        }

        if ($pedidoid !== null && ! Pedido::query()->whereKey($pedidoid)->exists()) {
            throw new \InvalidArgumentException('El pedido seleccionado no existe.');
        }

        if ($lineas !== null && ! $this->puedeEditarMaterias($lote)) {
            throw new \InvalidArgumentException('Solo puede modificar materias mientras el lote está en fase «Lote creado».');
        }

        return DB::transaction(function () use ($usuario, $lote, $pedidoid, $cantidadObjetivo, $unidadmedidaid, $observaciones, $producto, $lineas, $fase) {
            $productoReferencia = $producto !== null
                ? LoteProduccionNombre::normalizarProducto($producto)
                : LoteProduccionNombre::productoDesdeLote($lote);

            if ($unidadmedidaid !== null || $producto !== null) {
                $unidadmedidaid = ProductoPlantaCatalogo::resolverUnidadMedidaId(
                    $productoReferencia,
                    $unidadmedidaid ?? $lote->unidadmedidaid
                );
            }

            $payload = [
                'pedidoid' => $pedidoid,
                'cantidad_objetivo' => $cantidadObjetivo,
                'unidadmedidaid' => $unidadmedidaid,
                'observaciones' => $observaciones,
            ];

            if ($producto !== null && $fase === 'creacion') {
                $productoNorm = LoteProduccionNombre::normalizarProducto($producto);
                if ($productoNorm === '') {
                    throw new \InvalidArgumentException('Indique el producto a procesar.');
                }
                $payload['producto'] = $productoNorm;
                if (\Illuminate\Support\Facades\Schema::hasColumn('lote_produccion_pedido', 'producto')) {
                    $actualProducto = LoteProduccionNombre::productoDesdeLote($lote);
                    if (mb_strtolower($actualProducto) !== mb_strtolower($productoNorm)) {
                        $payload['nombre'] = LoteProduccionNombre::siguienteNombre($productoNorm);
                    }
                }
            }

            if ($lineas !== null) {
                if ($lineas === []) {
                    throw new \InvalidArgumentException('Debe indicar al menos una materia prima.');
                }
                $this->revertirConsumoMaterias($lote, $usuario);
                $lote->materiasPrimas()->delete();
                $tipoSalida = $this->tipoMovimientoSalidaProduccion();
                foreach ($lineas as $linea) {
                    $this->consumirInsumo(
                        $lote,
                        $usuario,
                        (int) $linea['insumoid'],
                        (float) $linea['cantidad'],
                        $tipoSalida
                    );
                }
            }

            $lote->update($payload);

            return $lote->fresh(['pedido', 'materiasPrimas.insumo', 'unidadMedida']);
        });
    }

    public function eliminar(Usuario $usuario, LoteProduccionPedido $lote): void
    {
        if (! $this->puedeEliminar($lote)) {
            throw new \InvalidArgumentException(
                'Solo puede eliminar lotes en fase «Lote creado», sin transformación iniciada.'
            );
        }

        DB::transaction(function () use ($usuario, $lote) {
            $this->revertirConsumoMaterias($lote, $usuario);
            $this->limpiarDependenciasAntesDeEliminar($lote);
            $lote->delete();
        });
    }

    private function limpiarDependenciasAntesDeEliminar(LoteProduccionPedido $lote): void
    {
        $loteId = (int) $lote->loteproduccionpedidoid;

        if (\Illuminate\Support\Facades\Schema::hasTable('asignacion_etapa_planta')) {
            \Illuminate\Support\Facades\DB::table('asignacion_etapa_planta')
                ->where('loteproduccionpedidoid', $loteId)
                ->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('registro_proceso_maquina_planta')) {
            \Illuminate\Support\Facades\DB::table('registro_proceso_maquina_planta')
                ->where('loteproduccionpedidoid', $loteId)
                ->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('evaluacion_final_lote_produccion')) {
            \Illuminate\Support\Facades\DB::table('evaluacion_final_lote_produccion')
                ->where('loteproduccionpedidoid', $loteId)
                ->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('almacenaje_lote_produccion')) {
            \Illuminate\Support\Facades\DB::table('almacenaje_lote_produccion')
                ->where('loteproduccionpedidoid', $loteId)
                ->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('certificacion_lote_produccion')) {
            \Illuminate\Support\Facades\DB::table('certificacion_lote_produccion')
                ->where('loteproduccionpedidoid', $loteId)
                ->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('inventario_presentacion_lote')) {
            \Illuminate\Support\Facades\DB::table('inventario_presentacion_lote')
                ->where('loteproduccionpedidoid', $loteId)
                ->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('almacen_movimiento')
            && \Illuminate\Support\Facades\Schema::hasColumn('almacen_movimiento', 'loteproduccionpedidoid')) {
            \Illuminate\Support\Facades\DB::table('almacen_movimiento')
                ->where('loteproduccionpedidoid', $loteId)
                ->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('detalle_traslado_planta_mayorista')
            && \Illuminate\Support\Facades\Schema::hasColumn('detalle_traslado_planta_mayorista', 'loteproduccionpedidoid')) {
            \Illuminate\Support\Facades\DB::table('detalle_traslado_planta_mayorista')
                ->where('loteproduccionpedidoid', $loteId)
                ->update(['loteproduccionpedidoid' => null]);
        }

        $lote->materiasPrimas()->delete();
    }

    private function revertirConsumoMaterias(LoteProduccionPedido $lote, Usuario $usuario): void
    {
        $lote->loadMissing('materiasPrimas.insumo.almacen');
        $tipoEntrada = $this->tipoMovimientoEntradaReversion();

        foreach ($lote->materiasPrimas as $linea) {
            $cantidad = (float) $linea->cantidad_usada;
            if ($cantidad <= 0 || ! $linea->insumo) {
                continue;
            }

            $insumo = $linea->insumo;
            $insumo->incrementarStock($cantidad);

            if ($insumo->almacen) {
                AlmacenMovimiento::create([
                    'almacenid' => $insumo->almacen->almacenid,
                    'insumoid' => $insumo->insumoid,
                    'tipo_movimiento_almacenid' => $tipoEntrada->tipo_movimiento_almacenid,
                    'usuarioid' => $usuario->usuarioid,
                    'fecha' => now()->toDateString(),
                    'cantidad' => $cantidad,
                    'referencia' => $lote->codigo_lote,
                    'destino_motivo' => 'Reversión lote de producción',
                    'observaciones' => '[Reversión '.$lote->codigo_lote.'] '.$insumo->nombre,
                ]);
            }
        }
    }

    private function tipoMovimientoEntradaReversion(): TipoMovimientoAlmacen
    {
        $tipo = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'entrada')
            ->where('activo', true)
            ->get()
            ->first(fn (TipoMovimientoAlmacen $t) => in_array(
                TipoMovimientoAlmacen::normalizeNombre($t->nombre),
                ['ajuste positivo', 'devolucion', 'devolución', 'ingreso', 'compra', 'recepcion', 'recepción'],
                true
            ));

        return $tipo ?? TipoMovimientoAlmacen::activosPorNaturaleza('entrada')->first()
            ?? throw new \InvalidArgumentException(
                'No hay tipos de movimiento de entrada activos para revertir el stock del lote.'
            );
    }
}
