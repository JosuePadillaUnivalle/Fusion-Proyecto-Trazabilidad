<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bloque D — planta (proceso/variables), materia prima y movimientos, producto distribución,
 * solicitudes a proveedor, operador/vehículo/transportista, geo y checklist detalle:
 * índices, unicidad lógica donde aplica y CHECK en SQLite para cantidades/stock no negativos.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->indexesRegistroMovimientoMateria();
        $this->indexesMateriaPrimaLote();
        $this->indexesMateriaPrimaBase();
        $this->indexesProductoDistribucion();
        $this->indexesAlmacenUsuario();
        $this->indexesSolicitudMaterial();
        $this->indexesDetalleSolicitudMaterial();
        $this->indexesRespuestaProveedor();
        $this->indexesLoteProduccionMateriaPrima();
        $this->indexesProductoDestinoPedido();
        $this->indexesEvaluacionYAlmacenajeLote();
        $this->indexesOperadorPlanta();
        $this->indexesVehiculoYPerfilTransportista();
        $this->indexesDireccionGeo();
        $this->indexesProcesoMaquinaYVariables();
        $this->uniqueVariableProcesoSiAplica();
        $this->normalizeVariableEstandarCodigo();
        $this->indexesActorAbastecimiento();
        $this->indexesProduccionAlmacenamientoHistorialActividad();
        $this->indexesQrtokenYChecklistDetalles();
        $this->indexesInsumoTipo();
        $this->checksCantidadesStockSqlite();
    }

    public function down(): void
    {
        $this->dropCheckSqlite('insumo', 'insumo_stock_chk');
        $this->dropCheckSqlite('produccionalmacenamiento', 'prod_alm_cantidad_chk');
        $this->dropCheckSqlite('almacenaje_lote_produccion', 'almacenaje_lote_cantidad_chk');
        $this->dropCheckSqlite('producto_destino_pedido', 'prod_dest_ped_cantidad_chk');
        $this->dropCheckSqlite('lote_produccion_materia_prima', 'lote_prod_mat_cantidades_chk');
        $this->dropCheckSqlite('respuesta_proveedor_solicitud', 'resp_prov_precio_cant_chk');
        $this->dropCheckSqlite('detalle_solicitud_material', 'det_sol_mat_cantidades_chk');
        $this->dropCheckSqlite('materia_prima_base', 'mp_base_stock_chk');
        $this->dropCheckSqlite('materia_prima_lote', 'mp_lote_cantidades_chk');

        $this->dropIndexIfExists('insumo', 'insumo_tipo_idx');
        $this->dropIndexIfExists('checklist_incidente_envio_detalle', 'chk_inc_det_parent_idx');
        $this->dropIndexIfExists('checklist_condicion_logistica_detalle', 'chk_cond_det_parent_idx');
        $this->dropIndexIfExists('qrtoken_asignacion', 'qrtoken_estado_idx');
        $this->dropIndexIfExists('actividad', 'actividad_tipo_idx');
        $this->dropIndexIfExists('actividad', 'actividad_lote_inicio_idx');
        $this->dropIndexIfExists('historial_estados_lote', 'hist_lote_lote_fecha_idx');
        $this->dropIndexIfExists('produccionalmacenamiento', 'prod_alm_prod_idx');
        $this->dropIndexIfExists('produccionalmacenamiento', 'prod_alm_alm_idx');
        $this->dropIndexIfExists('actor_abastecimiento', 'actor_abast_activo_idx');
        $this->dropIndexIfExists('actor_abastecimiento', 'actor_abast_tipo_idx');
        $this->dropUniqueByIndexNameIfExists('variable_proceso_maquina_planta', 'var_proc_maq_var_unique');
        $this->dropIndexIfExists('variable_proceso_maquina_planta', 'var_proc_maq_var_std_idx');
        $this->dropIndexIfExists('variable_proceso_maquina_planta', 'var_proc_maq_procmaq_idx');
        $this->dropIndexIfExists('proceso_maquina_planta', 'proc_maq_maq_idx');
        $this->dropIndexIfExists('proceso_maquina_planta', 'proc_maq_proc_idx');
        $this->dropIndexIfExists('direccion_geo_segmento', 'dir_geo_seg_parent_idx');
        $this->dropIndexIfExists('direccion_geo_envio', 'dir_geo_envio_usr_idx');
        $this->dropIndexIfExists('perfil_transportista', 'perfil_transp_estado_idx');
        $this->dropIndexIfExists('vehiculo', 'vehiculo_estado_idx');
        $this->dropIndexIfExists('vehiculo', 'vehiculo_tipo_idx');
        $this->dropIndexIfExists('operador_planta', 'operador_planta_usr_idx');
        $this->dropIndexIfExists('almacenaje_lote_produccion', 'almacenaje_lpp_idx');
        $this->dropIndexIfExists('evaluacion_final_lote_produccion', 'eval_fin_lpp_idx');
        $this->dropIndexIfExists('producto_destino_pedido', 'prod_dest_ped_dest_idx');
        $this->dropIndexIfExists('lote_produccion_materia_prima', 'lote_prod_mat_lpp_idx');
        $this->dropIndexIfExists('lote_produccion_materia_prima', 'lote_prod_mat_lote_idx');
        $this->dropIndexIfExists('respuesta_proveedor_solicitud', 'resp_prov_sol_actor_idx');
        $this->dropIndexIfExists('respuesta_proveedor_solicitud', 'resp_prov_sol_sol_idx');
        $this->dropIndexIfExists('detalle_solicitud_material', 'det_sol_mat_base_idx');
        $this->dropIndexIfExists('detalle_solicitud_material', 'det_sol_mat_sol_idx');
        $this->dropIndexIfExists('solicitud_material_pedido', 'sol_mat_fecha_req_idx');
        $this->dropIndexIfExists('solicitud_material_pedido', 'sol_mat_pedido_idx');
        $this->dropIndexIfExists('almacen_usuario', 'almacen_usuario_almacen_idx');
        $this->dropIndexIfExists('producto_distribucion', 'prod_dist_cat_idx');
        $this->dropIndexIfExists('materia_prima_base', 'mp_base_cat_idx');
        $this->dropIndexIfExists('materia_prima_lote', 'mp_lote_fecha_rec_idx');
        $this->dropIndexIfExists('materia_prima_lote', 'mp_lote_prov_idx');
        $this->dropIndexIfExists('materia_prima_lote', 'mp_lote_base_idx');
        $this->dropIndexIfExists('registro_movimiento_materia', 'reg_mov_mat_tipo_idx');
        $this->dropIndexIfExists('registro_movimiento_materia', 'reg_mov_mat_base_fecha_idx');

        $this->dropUniqueByIndexNameIfExists('variable_estandar', 'variable_estandar_codigo_unique');
    }

    private function indexesRegistroMovimientoMateria(): void
    {
        if (! Schema::hasTable('registro_movimiento_materia')) {
            return;
        }
        Schema::table('registro_movimiento_materia', function (Blueprint $table) {
            if (Schema::hasColumn('registro_movimiento_materia', 'materiaprimabaseid')
                && Schema::hasColumn('registro_movimiento_materia', 'fecha_movimiento')
                && ! $this->indexExists('registro_movimiento_materia', 'reg_mov_mat_base_fecha_idx')) {
                $table->index(['materiaprimabaseid', 'fecha_movimiento'], 'reg_mov_mat_base_fecha_idx');
            }
            if (Schema::hasColumn('registro_movimiento_materia', 'tipomovimientomateriaid')
                && ! $this->indexExists('registro_movimiento_materia', 'reg_mov_mat_tipo_idx')) {
                $table->index('tipomovimientomateriaid', 'reg_mov_mat_tipo_idx');
            }
        });
    }

    private function indexesMateriaPrimaLote(): void
    {
        if (! Schema::hasTable('materia_prima_lote')) {
            return;
        }
        Schema::table('materia_prima_lote', function (Blueprint $table) {
            if (! $this->indexExists('materia_prima_lote', 'mp_lote_base_idx')) {
                $table->index('materiaprimabaseid', 'mp_lote_base_idx');
            }
            if (Schema::hasColumn('materia_prima_lote', 'proveedor_actorid')
                && ! $this->indexExists('materia_prima_lote', 'mp_lote_prov_idx')) {
                $table->index('proveedor_actorid', 'mp_lote_prov_idx');
            }
            if (Schema::hasColumn('materia_prima_lote', 'fecha_recepcion')
                && ! $this->indexExists('materia_prima_lote', 'mp_lote_fecha_rec_idx')) {
                $table->index('fecha_recepcion', 'mp_lote_fecha_rec_idx');
            }
        });
    }

    private function indexesMateriaPrimaBase(): void
    {
        if (! Schema::hasTable('materia_prima_base')) {
            return;
        }
        Schema::table('materia_prima_base', function (Blueprint $table) {
            if (Schema::hasColumn('materia_prima_base', 'categoriamateriaprimaid')
                && ! $this->indexExists('materia_prima_base', 'mp_base_cat_idx')) {
                $table->index('categoriamateriaprimaid', 'mp_base_cat_idx');
            }
        });
    }

    private function indexesProductoDistribucion(): void
    {
        if (! Schema::hasTable('producto_distribucion')) {
            return;
        }
        Schema::table('producto_distribucion', function (Blueprint $table) {
            if (Schema::hasColumn('producto_distribucion', 'categoriaproductoid')
                && ! $this->indexExists('producto_distribucion', 'prod_dist_cat_idx')) {
                $table->index('categoriaproductoid', 'prod_dist_cat_idx');
            }
        });
    }

    private function indexesAlmacenUsuario(): void
    {
        if (! Schema::hasTable('almacen_usuario')) {
            return;
        }
        Schema::table('almacen_usuario', function (Blueprint $table) {
            if (! $this->indexExists('almacen_usuario', 'almacen_usuario_almacen_idx')) {
                $table->index('almacenid', 'almacen_usuario_almacen_idx');
            }
        });
    }

    private function indexesSolicitudMaterial(): void
    {
        if (! Schema::hasTable('solicitud_material_pedido')) {
            return;
        }
        Schema::table('solicitud_material_pedido', function (Blueprint $table) {
            if (! $this->indexExists('solicitud_material_pedido', 'sol_mat_pedido_idx')) {
                $table->index('pedidoid', 'sol_mat_pedido_idx');
            }
            if (Schema::hasColumn('solicitud_material_pedido', 'fecha_requerida')
                && ! $this->indexExists('solicitud_material_pedido', 'sol_mat_fecha_req_idx')) {
                $table->index('fecha_requerida', 'sol_mat_fecha_req_idx');
            }
        });
    }

    private function indexesDetalleSolicitudMaterial(): void
    {
        if (! Schema::hasTable('detalle_solicitud_material')) {
            return;
        }
        Schema::table('detalle_solicitud_material', function (Blueprint $table) {
            if (! $this->indexExists('detalle_solicitud_material', 'det_sol_mat_sol_idx')) {
                $table->index('solicitudmaterialid', 'det_sol_mat_sol_idx');
            }
            if (! $this->indexExists('detalle_solicitud_material', 'det_sol_mat_base_idx')) {
                $table->index('materiaprimabaseid', 'det_sol_mat_base_idx');
            }
        });
    }

    private function indexesRespuestaProveedor(): void
    {
        if (! Schema::hasTable('respuesta_proveedor_solicitud')) {
            return;
        }
        Schema::table('respuesta_proveedor_solicitud', function (Blueprint $table) {
            if (! $this->indexExists('respuesta_proveedor_solicitud', 'resp_prov_sol_sol_idx')) {
                $table->index('solicitudmaterialid', 'resp_prov_sol_sol_idx');
            }
            if (! $this->indexExists('respuesta_proveedor_solicitud', 'resp_prov_sol_actor_idx')) {
                $table->index('proveedor_actorid', 'resp_prov_sol_actor_idx');
            }
        });
    }

    private function indexesLoteProduccionMateriaPrima(): void
    {
        if (! Schema::hasTable('lote_produccion_materia_prima')) {
            return;
        }
        Schema::table('lote_produccion_materia_prima', function (Blueprint $table) {
            if (! $this->indexExists('lote_produccion_materia_prima', 'lote_prod_mat_lpp_idx')) {
                $table->index('loteproduccionpedidoid', 'lote_prod_mat_lpp_idx');
            }
            if (! $this->indexExists('lote_produccion_materia_prima', 'lote_prod_mat_lote_idx')) {
                $table->index('materiaprimaloteid', 'lote_prod_mat_lote_idx');
            }
        });
    }

    private function indexesProductoDestinoPedido(): void
    {
        if (! Schema::hasTable('producto_destino_pedido')) {
            return;
        }
        Schema::table('producto_destino_pedido', function (Blueprint $table) {
            if (Schema::hasColumn('producto_destino_pedido', 'pedidodestinoid')
                && ! $this->indexExists('producto_destino_pedido', 'prod_dest_ped_dest_idx')) {
                $table->index('pedidodestinoid', 'prod_dest_ped_dest_idx');
            }
        });
    }

    private function indexesEvaluacionYAlmacenajeLote(): void
    {
        if (Schema::hasTable('evaluacion_final_lote_produccion')) {
            Schema::table('evaluacion_final_lote_produccion', function (Blueprint $table) {
                if (! $this->indexExists('evaluacion_final_lote_produccion', 'eval_fin_lpp_idx')) {
                    $table->index('loteproduccionpedidoid', 'eval_fin_lpp_idx');
                }
            });
        }
        if (Schema::hasTable('almacenaje_lote_produccion')) {
            Schema::table('almacenaje_lote_produccion', function (Blueprint $table) {
                if (! $this->indexExists('almacenaje_lote_produccion', 'almacenaje_lpp_idx')) {
                    $table->index('loteproduccionpedidoid', 'almacenaje_lpp_idx');
                }
            });
        }
    }

    private function indexesOperadorPlanta(): void
    {
        if (! Schema::hasTable('operador_planta') || ! Schema::hasColumn('operador_planta', 'usuarioid')) {
            return;
        }
        Schema::table('operador_planta', function (Blueprint $table) {
            if (! $this->indexExists('operador_planta', 'operador_planta_usr_idx')) {
                $table->index('usuarioid', 'operador_planta_usr_idx');
            }
        });
    }

    private function indexesVehiculoYPerfilTransportista(): void
    {
        if (Schema::hasTable('vehiculo')) {
            Schema::table('vehiculo', function (Blueprint $table) {
                if (Schema::hasColumn('vehiculo', 'tipovehiculoid')
                    && ! $this->indexExists('vehiculo', 'vehiculo_tipo_idx')) {
                    $table->index('tipovehiculoid', 'vehiculo_tipo_idx');
                }
                if (Schema::hasColumn('vehiculo', 'estadovehiculoid')
                    && ! $this->indexExists('vehiculo', 'vehiculo_estado_idx')) {
                    $table->index('estadovehiculoid', 'vehiculo_estado_idx');
                }
            });
        }
        if (Schema::hasTable('perfil_transportista') && Schema::hasColumn('perfil_transportista', 'estadotransportistaid')) {
            Schema::table('perfil_transportista', function (Blueprint $table) {
                if (! $this->indexExists('perfil_transportista', 'perfil_transp_estado_idx')) {
                    $table->index('estadotransportistaid', 'perfil_transp_estado_idx');
                }
            });
        }
    }

    private function indexesDireccionGeo(): void
    {
        if (Schema::hasTable('direccion_geo_envio') && Schema::hasColumn('direccion_geo_envio', 'usuarioid')) {
            Schema::table('direccion_geo_envio', function (Blueprint $table) {
                if (! $this->indexExists('direccion_geo_envio', 'dir_geo_envio_usr_idx')) {
                    $table->index('usuarioid', 'dir_geo_envio_usr_idx');
                }
            });
        }
        if (Schema::hasTable('direccion_geo_segmento')) {
            Schema::table('direccion_geo_segmento', function (Blueprint $table) {
                if (! $this->indexExists('direccion_geo_segmento', 'dir_geo_seg_parent_idx')) {
                    $table->index('direcciongeoenvioid', 'dir_geo_seg_parent_idx');
                }
            });
        }
    }

    private function indexesProcesoMaquinaYVariables(): void
    {
        if (Schema::hasTable('proceso_maquina_planta')) {
            Schema::table('proceso_maquina_planta', function (Blueprint $table) {
                if (Schema::hasColumn('proceso_maquina_planta', 'procesoplantaid')
                    && ! $this->indexExists('proceso_maquina_planta', 'proc_maq_proc_idx')) {
                    $table->index('procesoplantaid', 'proc_maq_proc_idx');
                }
                if (Schema::hasColumn('proceso_maquina_planta', 'maquinaplantaid')
                    && ! $this->indexExists('proceso_maquina_planta', 'proc_maq_maq_idx')) {
                    $table->index('maquinaplantaid', 'proc_maq_maq_idx');
                }
            });
        }
        if (Schema::hasTable('variable_proceso_maquina_planta')) {
            Schema::table('variable_proceso_maquina_planta', function (Blueprint $table) {
                if (Schema::hasColumn('variable_proceso_maquina_planta', 'procesomaquinaplantaid')
                    && ! $this->indexExists('variable_proceso_maquina_planta', 'var_proc_maq_procmaq_idx')) {
                    $table->index('procesomaquinaplantaid', 'var_proc_maq_procmaq_idx');
                }
                if (Schema::hasColumn('variable_proceso_maquina_planta', 'variableestandarid')
                    && ! $this->indexExists('variable_proceso_maquina_planta', 'var_proc_maq_var_std_idx')) {
                    $table->index('variableestandarid', 'var_proc_maq_var_std_idx');
                }
            });
        }
    }

    private function uniqueVariableProcesoSiAplica(): void
    {
        if (! Schema::hasTable('variable_proceso_maquina_planta')) {
            return;
        }
        if ($this->indexExists('variable_proceso_maquina_planta', 'var_proc_maq_var_unique')) {
            return;
        }
        if (! $this->parejaUnica('variable_proceso_maquina_planta', 'procesomaquinaplantaid', 'variableestandarid')) {
            return;
        }
        Schema::table('variable_proceso_maquina_planta', function (Blueprint $table) {
            $table->unique(['procesomaquinaplantaid', 'variableestandarid'], 'var_proc_maq_var_unique');
        });
    }

    private function normalizeVariableEstandarCodigo(): void
    {
        if (! Schema::hasTable('variable_estandar') || ! Schema::hasColumn('variable_estandar', 'codigo')) {
            return;
        }
        $idx = 'variable_estandar_codigo_unique';
        if ($this->indexExists('variable_estandar', $idx)) {
            return;
        }
        if (! $this->columnValuesUnique('variable_estandar', 'codigo')) {
            return;
        }
        Schema::table('variable_estandar', function (Blueprint $table) use ($idx) {
            $table->unique('codigo', $idx);
        });
    }

    private function indexesActorAbastecimiento(): void
    {
        if (! Schema::hasTable('actor_abastecimiento')) {
            return;
        }
        Schema::table('actor_abastecimiento', function (Blueprint $table) {
            if (Schema::hasColumn('actor_abastecimiento', 'tipo_actor')
                && ! $this->indexExists('actor_abastecimiento', 'actor_abast_tipo_idx')) {
                $table->index('tipo_actor', 'actor_abast_tipo_idx');
            }
            if (Schema::hasColumn('actor_abastecimiento', 'activo')
                && ! $this->indexExists('actor_abastecimiento', 'actor_abast_activo_idx')) {
                $table->index('activo', 'actor_abast_activo_idx');
            }
        });
    }

    private function indexesProduccionAlmacenamientoHistorialActividad(): void
    {
        if (Schema::hasTable('produccionalmacenamiento')) {
            Schema::table('produccionalmacenamiento', function (Blueprint $table) {
                if (! $this->indexExists('produccionalmacenamiento', 'prod_alm_alm_idx')) {
                    $table->index('almacenid', 'prod_alm_alm_idx');
                }
                if (! $this->indexExists('produccionalmacenamiento', 'prod_alm_prod_idx')) {
                    $table->index('produccionid', 'prod_alm_prod_idx');
                }
            });
        }
        if (Schema::hasTable('historial_estados_lote')
            && Schema::hasColumn('historial_estados_lote', 'fecha_cambio')) {
            Schema::table('historial_estados_lote', function (Blueprint $table) {
                if (! $this->indexExists('historial_estados_lote', 'hist_lote_lote_fecha_idx')) {
                    $table->index(['loteid', 'fecha_cambio'], 'hist_lote_lote_fecha_idx');
                }
            });
        }
        if (Schema::hasTable('actividad')) {
            Schema::table('actividad', function (Blueprint $table) {
                if (Schema::hasColumn('actividad', 'fechainicio')
                    && ! $this->indexExists('actividad', 'actividad_lote_inicio_idx')) {
                    $table->index(['loteid', 'fechainicio'], 'actividad_lote_inicio_idx');
                }
                if (Schema::hasColumn('actividad', 'tipoactividadid')
                    && ! $this->indexExists('actividad', 'actividad_tipo_idx')) {
                    $table->index('tipoactividadid', 'actividad_tipo_idx');
                }
            });
        }
    }

    private function indexesQrtokenYChecklistDetalles(): void
    {
        if (Schema::hasTable('qrtoken_asignacion') && Schema::hasColumn('qrtoken_asignacion', 'estadoqrtokenid')) {
            Schema::table('qrtoken_asignacion', function (Blueprint $table) {
                if (! $this->indexExists('qrtoken_asignacion', 'qrtoken_estado_idx')) {
                    $table->index('estadoqrtokenid', 'qrtoken_estado_idx');
                }
            });
        }
        if (Schema::hasTable('checklist_condicion_logistica_detalle')) {
            Schema::table('checklist_condicion_logistica_detalle', function (Blueprint $table) {
                if (! $this->indexExists('checklist_condicion_logistica_detalle', 'chk_cond_det_parent_idx')) {
                    $table->index('checklistcondicionid', 'chk_cond_det_parent_idx');
                }
            });
        }
        if (Schema::hasTable('checklist_incidente_envio_detalle')) {
            Schema::table('checklist_incidente_envio_detalle', function (Blueprint $table) {
                if (! $this->indexExists('checklist_incidente_envio_detalle', 'chk_inc_det_parent_idx')) {
                    $table->index('checklistincidenteenvioid', 'chk_inc_det_parent_idx');
                }
            });
        }
    }

    private function indexesInsumoTipo(): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasColumn('insumo', 'tipoinsumoid')) {
            return;
        }
        Schema::table('insumo', function (Blueprint $table) {
            if (! $this->indexExists('insumo', 'insumo_tipo_idx')) {
                $table->index('tipoinsumoid', 'insumo_tipo_idx');
            }
        });
    }

    private function checksCantidadesStockSqlite(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }

        $this->tryAddCheck('materia_prima_lote', 'mp_lote_cantidades_chk',
            '(cantidad >= 0) AND (cantidad_disponible >= 0)');
        $this->tryAddCheck('materia_prima_base', 'mp_base_stock_chk',
            '(cantidad_disponible >= 0) AND (stock_minimo >= 0) AND (stock_maximo IS NULL OR stock_maximo >= 0)');
        $this->tryAddCheck('detalle_solicitud_material', 'det_sol_mat_cantidades_chk',
            '(cantidad_solicitada >= 0) AND (cantidad_aprobada IS NULL OR cantidad_aprobada >= 0)');
        $this->tryAddCheck('respuesta_proveedor_solicitud', 'resp_prov_precio_cant_chk',
            '(cantidad_confirmada IS NULL OR cantidad_confirmada >= 0) AND (precio IS NULL OR precio >= 0)');
        $this->tryAddCheck('lote_produccion_materia_prima', 'lote_prod_mat_cantidades_chk',
            '(cantidad_planificada >= 0) AND (cantidad_usada IS NULL OR cantidad_usada >= 0)');
        $this->tryAddCheck('producto_destino_pedido', 'prod_dest_ped_cantidad_chk', '(cantidad >= 0)');
        $this->tryAddCheck('almacenaje_lote_produccion', 'almacenaje_lote_cantidad_chk', '(cantidad >= 0)');
        $this->tryAddCheck('produccionalmacenamiento', 'prod_alm_cantidad_chk', '(cantidad >= 0)');

        if (Schema::hasTable('insumo') && Schema::hasColumn('insumo', 'stock') && Schema::hasColumn('insumo', 'stockminimo')) {
            $this->tryAddCheck('insumo', 'insumo_stock_chk', '(stock >= 0) AND (stockminimo >= 0)');
        }
    }

    private function parejaUnica(string $table, string $colA, string $colB): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $colA) || ! Schema::hasColumn($table, $colB)) {
            return false;
        }

        return ! DB::table($table)
            ->select([$colA, $colB])
            ->groupBy($colA, $colB)
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    private function columnValuesUnique(string $table, string $column): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return false;
        }

        return ! DB::table($table)
            ->select($column)
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    private function tryAddCheck(string $table, string $constraintName, string $expression): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        try {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraintName} CHECK ({$expression})");
        } catch (\Throwable) {
            //
        }
    }

    private function dropCheckSqlite(string $table, string $constraintName): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite' || ! Schema::hasTable($table)) {
            return;
        }
        try {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraintName}");
        } catch (\Throwable) {
            //
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }
        Schema::table($table, function (Blueprint $t) use ($indexName) {
            $t->dropIndex($indexName);
        });
    }

    private function dropUniqueByIndexNameIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }
        Schema::table($table, function (Blueprint $t) use ($indexName) {
            $t->dropUnique($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        if ($connection->getDriverName() === 'sqlite') {
            $rows = $connection->select(
                "SELECT 1 FROM sqlite_master WHERE type='index' AND tbl_name = ? AND name = ?",
                [$table, $indexName]
            );

            return count($rows) > 0;
        }

        $db = $connection->getDatabaseName();
        $result = $connection->selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$db, $table, $indexName]
        );

        return isset($result->c) && (int) $result->c > 0;
    }
};
