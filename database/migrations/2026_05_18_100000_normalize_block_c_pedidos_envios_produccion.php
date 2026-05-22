<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bloque C — pedidos, ventas, lote/producción, certificación y envíos (rutas, seguimiento):
 * índices para filtros habituales y reglas de cantidades/montos no negativos (CHECK en SQLite).
 */
return new class extends Migration {
    public function up(): void
    {
        $this->indexesPedido();
        $this->indexesDetallePedido();
        $this->indexesVenta();
        $this->indexesProduccion();
        $this->indexesLote();
        $this->indexesLoteInsumo();
        $this->indexesClima();
        $this->indexesCertificacionLote();
        $this->indexesEnviosPendientes();
        $this->indexesIncidenteDocumentoEnvio();
        $this->indexesRutaMultiYParada();
        $this->indexesEnvioAsignacionMultiple();
        $this->indexesSeguimientoHistorialChecklist();
        $this->indexesPedidoDestinoSeguimiento();
        $this->indexesLoteProduccionPedido();
        $this->indexesRegistroProcesoMaquina();
        $this->indexesNotificacionUsuario();
        $this->indexesCargaEnvio();
        $this->checksCantidadesMontosSqlite();
    }

    public function down(): void
    {
        $this->dropCheckSqlite('carga_envio', 'carga_envio_cantidades_chk');
        $this->dropCheckSqlite('calificacion_envio', 'calificacion_envio_puntuacion_chk');
        $this->dropCheckSqlite('loteinsumo', 'loteinsumo_cantidades_chk');
        $this->dropCheckSqlite('produccion', 'produccion_cantidades_chk');
        $this->dropCheckSqlite('venta', 'venta_montos_chk');
        $this->dropCheckSqlite('detallepedido', 'detallepedido_cantidad_chk');

        $this->dropIndexIfExists('carga_envio', 'carga_envio_catalogo_idx');
        $this->dropIndexIfExists('notificacion_usuario', 'notif_usuario_leida_fecha_idx');
        $this->dropIndexIfExists('registro_proceso_maquina_planta', 'reg_procmaq_lote_fecha_idx');
        $this->dropIndexIfExists('lote_produccion_pedido', 'lote_prod_ped_pedido_idx');
        $this->dropIndexIfExists('seguimiento_envio_pedido', 'seg_env_ped_estado_idx');
        $this->dropIndexIfExists('seguimiento_envio_pedido', 'seg_env_ped_pedido_idx');
        $this->dropIndexIfExists('pedido_destino', 'pedido_destino_almacen_dest_idx');
        $this->dropIndexIfExists('historial_estado_envio', 'hist_estado_env_asig_fecha_idx');
        $this->dropIndexIfExists('checklist_condicion_logistica', 'checklist_cond_almacen_idx');
        $this->dropIndexIfExists('seguimiento_envio_gps', 'seg_gps_envio_registrado_idx');
        $this->dropIndexIfExists('envio_asignacion_multiple', 'envio_asign_pedido_idx');
        $this->dropIndexIfExists('envio_asignacion_multiple', 'envio_asign_ruta_idx');
        $this->dropIndexIfExists('ruta_parada', 'ruta_parada_pedido_idx');
        $this->dropIndexIfExists('ruta_multi_entrega', 'ruta_multi_transp_fecha_idx');
        $this->dropIndexIfExists('documento_entrega', 'documento_entrega_pedido_idx');
        $this->dropIndexIfExists('incidente_envio', 'incidente_envio_pedido_idx');
        $this->dropIndexIfExists('envios_pendientes', 'envios_pendientes_usuario_idx');
        $this->dropIndexIfExists('envios_pendientes', 'envios_pendientes_estado_idx');
        $this->dropIndexIfExists('certificacion_lote', 'certificacion_lote_usuario_idx');
        $this->dropIndexIfExists('certificacion_lote', 'certificacion_lote_lote_idx');
        $this->dropIndexIfExists('clima', 'clima_lote_fecha_idx');
        $this->dropIndexIfExists('loteinsumo', 'loteinsumo_insumo_idx');
        $this->dropIndexIfExists('lote', 'lote_estado_idx');
        $this->dropIndexIfExists('lote', 'lote_cultivo_idx');
        $this->dropIndexIfExists('lote', 'lote_usuario_idx');
        $this->dropIndexIfExists('produccion', 'produccion_fechacosecha_idx');
        $this->dropIndexIfExists('produccion', 'produccion_lote_idx');
        $this->dropIndexIfExists('venta', 'venta_fecha_idx');
        $this->dropIndexIfExists('venta', 'venta_produccion_idx');
        $this->dropIndexIfExists('detallepedido', 'detallepedido_cultivo_idx');
        $this->dropIndexIfExists('pedido', 'pedido_clientecomercial_idx');
        $this->dropIndexIfExists('pedido', 'pedido_estado_fecha_idx');
    }

    private function indexesPedido(): void
    {
        if (! Schema::hasTable('pedido')) {
            return;
        }
        Schema::table('pedido', function (Blueprint $table) {
            if (! $this->indexExists('pedido', 'pedido_estado_fecha_idx')) {
                $table->index(['estado', 'fechapedido'], 'pedido_estado_fecha_idx');
            }
            if (Schema::hasColumn('pedido', 'clientecomercialid')
                && ! $this->indexExists('pedido', 'pedido_clientecomercial_idx')) {
                $table->index('clientecomercialid', 'pedido_clientecomercial_idx');
            }
        });
    }

    private function indexesDetallePedido(): void
    {
        if (! Schema::hasTable('detallepedido') || ! Schema::hasColumn('detallepedido', 'cultivo_personalizado')) {
            return;
        }
        Schema::table('detallepedido', function (Blueprint $table) {
            if (! $this->indexExists('detallepedido', 'detallepedido_cultivo_idx')) {
                $table->index('cultivo_personalizado', 'detallepedido_cultivo_idx');
            }
        });
    }

    private function indexesVenta(): void
    {
        if (! Schema::hasTable('venta')) {
            return;
        }
        Schema::table('venta', function (Blueprint $table) {
            if (! $this->indexExists('venta', 'venta_produccion_idx')) {
                $table->index('produccionid', 'venta_produccion_idx');
            }
            if (! $this->indexExists('venta', 'venta_fecha_idx')) {
                $table->index('fechaventa', 'venta_fecha_idx');
            }
        });
    }

    private function indexesProduccion(): void
    {
        if (! Schema::hasTable('produccion')) {
            return;
        }
        Schema::table('produccion', function (Blueprint $table) {
            if (! $this->indexExists('produccion', 'produccion_lote_idx')) {
                $table->index('loteid', 'produccion_lote_idx');
            }
            if (! $this->indexExists('produccion', 'produccion_fechacosecha_idx')) {
                $table->index('fechacosecha', 'produccion_fechacosecha_idx');
            }
        });
    }

    private function indexesLote(): void
    {
        if (! Schema::hasTable('lote')) {
            return;
        }
        Schema::table('lote', function (Blueprint $table) {
            if (! $this->indexExists('lote', 'lote_usuario_idx')) {
                $table->index('usuarioid', 'lote_usuario_idx');
            }
            if (! $this->indexExists('lote', 'lote_cultivo_idx')) {
                $table->index('cultivoid', 'lote_cultivo_idx');
            }
            if (! $this->indexExists('lote', 'lote_estado_idx')) {
                $table->index('estadolotetipoid', 'lote_estado_idx');
            }
        });
    }

    private function indexesLoteInsumo(): void
    {
        if (! Schema::hasTable('loteinsumo')) {
            return;
        }
        Schema::table('loteinsumo', function (Blueprint $table) {
            if (! $this->indexExists('loteinsumo', 'loteinsumo_insumo_idx')) {
                $table->index('insumoid', 'loteinsumo_insumo_idx');
            }
        });
    }

    private function indexesClima(): void
    {
        if (! Schema::hasTable('clima')) {
            return;
        }
        Schema::table('clima', function (Blueprint $table) {
            if (! $this->indexExists('clima', 'clima_lote_fecha_idx')) {
                $table->index(['loteid', 'fecha'], 'clima_lote_fecha_idx');
            }
        });
    }

    private function indexesCertificacionLote(): void
    {
        if (! Schema::hasTable('certificacion_lote')) {
            return;
        }
        Schema::table('certificacion_lote', function (Blueprint $table) {
            if (! $this->indexExists('certificacion_lote', 'certificacion_lote_lote_idx')) {
                $table->index('loteid', 'certificacion_lote_lote_idx');
            }
            if (! $this->indexExists('certificacion_lote', 'certificacion_lote_usuario_idx')) {
                $table->index('usuarioid', 'certificacion_lote_usuario_idx');
            }
        });
    }

    private function indexesEnviosPendientes(): void
    {
        if (! Schema::hasTable('envios_pendientes')) {
            return;
        }
        Schema::table('envios_pendientes', function (Blueprint $table) {
            if (! $this->indexExists('envios_pendientes', 'envios_pendientes_estado_idx')) {
                $table->index('estado', 'envios_pendientes_estado_idx');
            }
            if (Schema::hasColumn('envios_pendientes', 'usuarioid')
                && ! $this->indexExists('envios_pendientes', 'envios_pendientes_usuario_idx')) {
                $table->index('usuarioid', 'envios_pendientes_usuario_idx');
            }
        });
    }

    private function indexesIncidenteDocumentoEnvio(): void
    {
        if (Schema::hasTable('incidente_envio') && Schema::hasColumn('incidente_envio', 'pedidoid')) {
            Schema::table('incidente_envio', function (Blueprint $table) {
                if (! $this->indexExists('incidente_envio', 'incidente_envio_pedido_idx')) {
                    $table->index('pedidoid', 'incidente_envio_pedido_idx');
                }
            });
        }
        if (Schema::hasTable('documento_entrega') && Schema::hasColumn('documento_entrega', 'pedidoid')) {
            Schema::table('documento_entrega', function (Blueprint $table) {
                if (! $this->indexExists('documento_entrega', 'documento_entrega_pedido_idx')) {
                    $table->index('pedidoid', 'documento_entrega_pedido_idx');
                }
            });
        }
    }

    private function indexesRutaMultiYParada(): void
    {
        if (Schema::hasTable('ruta_multi_entrega')
            && Schema::hasColumn('ruta_multi_entrega', 'transportista_usuarioid')) {
            Schema::table('ruta_multi_entrega', function (Blueprint $table) {
                if (! $this->indexExists('ruta_multi_entrega', 'ruta_multi_transp_fecha_idx')) {
                    $table->index(['transportista_usuarioid', 'fecha_salida'], 'ruta_multi_transp_fecha_idx');
                }
            });
        }
        if (Schema::hasTable('ruta_parada') && Schema::hasColumn('ruta_parada', 'pedidoid')) {
            Schema::table('ruta_parada', function (Blueprint $table) {
                if (! $this->indexExists('ruta_parada', 'ruta_parada_pedido_idx')) {
                    $table->index('pedidoid', 'ruta_parada_pedido_idx');
                }
            });
        }
    }

    private function indexesEnvioAsignacionMultiple(): void
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return;
        }
        Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
            if (Schema::hasColumn('envio_asignacion_multiple', 'pedidoid')
                && ! $this->indexExists('envio_asignacion_multiple', 'envio_asign_pedido_idx')) {
                $table->index('pedidoid', 'envio_asign_pedido_idx');
            }
            if (Schema::hasColumn('envio_asignacion_multiple', 'rutamultientregaid')
                && ! $this->indexExists('envio_asignacion_multiple', 'envio_asign_ruta_idx')) {
                $table->index('rutamultientregaid', 'envio_asign_ruta_idx');
            }
        });
    }

    private function indexesSeguimientoHistorialChecklist(): void
    {
        if (Schema::hasTable('seguimiento_envio_gps')
            && Schema::hasColumn('seguimiento_envio_gps', 'envioasignacionmultipleid')
            && Schema::hasColumn('seguimiento_envio_gps', 'registrado_en')) {
            Schema::table('seguimiento_envio_gps', function (Blueprint $table) {
                if (! $this->indexExists('seguimiento_envio_gps', 'seg_gps_envio_registrado_idx')) {
                    $table->index(['envioasignacionmultipleid', 'registrado_en'], 'seg_gps_envio_registrado_idx');
                }
            });
        }
        if (Schema::hasTable('checklist_condicion_logistica') && Schema::hasColumn('checklist_condicion_logistica', 'almacenid')) {
            Schema::table('checklist_condicion_logistica', function (Blueprint $table) {
                if (! $this->indexExists('checklist_condicion_logistica', 'checklist_cond_almacen_idx')) {
                    $table->index('almacenid', 'checklist_cond_almacen_idx');
                }
            });
        }
        if (Schema::hasTable('historial_estado_envio')
            && Schema::hasColumn('historial_estado_envio', 'envioasignacionmultipleid')
            && Schema::hasColumn('historial_estado_envio', 'fecha')) {
            Schema::table('historial_estado_envio', function (Blueprint $table) {
                if (! $this->indexExists('historial_estado_envio', 'hist_estado_env_asig_fecha_idx')) {
                    $table->index(['envioasignacionmultipleid', 'fecha'], 'hist_estado_env_asig_fecha_idx');
                }
            });
        }
    }

    private function indexesPedidoDestinoSeguimiento(): void
    {
        if (Schema::hasTable('pedido_destino') && Schema::hasColumn('pedido_destino', 'almacen_destinoid')) {
            Schema::table('pedido_destino', function (Blueprint $table) {
                if (! $this->indexExists('pedido_destino', 'pedido_destino_almacen_dest_idx')) {
                    $table->index('almacen_destinoid', 'pedido_destino_almacen_dest_idx');
                }
            });
        }
        if (Schema::hasTable('seguimiento_envio_pedido')) {
            Schema::table('seguimiento_envio_pedido', function (Blueprint $table) {
                if (Schema::hasColumn('seguimiento_envio_pedido', 'pedidoid')
                    && ! $this->indexExists('seguimiento_envio_pedido', 'seg_env_ped_pedido_idx')) {
                    $table->index('pedidoid', 'seg_env_ped_pedido_idx');
                }
                if (Schema::hasColumn('seguimiento_envio_pedido', 'estado')
                    && ! $this->indexExists('seguimiento_envio_pedido', 'seg_env_ped_estado_idx')) {
                    $table->index('estado', 'seg_env_ped_estado_idx');
                }
            });
        }
    }

    private function indexesLoteProduccionPedido(): void
    {
        if (! Schema::hasTable('lote_produccion_pedido') || ! Schema::hasColumn('lote_produccion_pedido', 'pedidoid')) {
            return;
        }
        Schema::table('lote_produccion_pedido', function (Blueprint $table) {
            if (! $this->indexExists('lote_produccion_pedido', 'lote_prod_ped_pedido_idx')) {
                $table->index('pedidoid', 'lote_prod_ped_pedido_idx');
            }
        });
    }

    private function indexesRegistroProcesoMaquina(): void
    {
        if (! Schema::hasTable('registro_proceso_maquina_planta')
            || ! Schema::hasColumn('registro_proceso_maquina_planta', 'loteid')
            || ! Schema::hasColumn('registro_proceso_maquina_planta', 'fecha_registro')) {
            return;
        }
        Schema::table('registro_proceso_maquina_planta', function (Blueprint $table) {
            if (! $this->indexExists('registro_proceso_maquina_planta', 'reg_procmaq_lote_fecha_idx')) {
                $table->index(['loteid', 'fecha_registro'], 'reg_procmaq_lote_fecha_idx');
            }
        });
    }

    private function indexesNotificacionUsuario(): void
    {
        if (! Schema::hasTable('notificacion_usuario')
            || ! Schema::hasColumn('notificacion_usuario', 'usuarioid')
            || ! Schema::hasColumn('notificacion_usuario', 'leida')
            || ! Schema::hasColumn('notificacion_usuario', 'fecha')) {
            return;
        }
        Schema::table('notificacion_usuario', function (Blueprint $table) {
            if (! $this->indexExists('notificacion_usuario', 'notif_usuario_leida_fecha_idx')) {
                $table->index(['usuarioid', 'leida', 'fecha'], 'notif_usuario_leida_fecha_idx');
            }
        });
    }

    private function indexesCargaEnvio(): void
    {
        if (! Schema::hasTable('carga_envio') || ! Schema::hasColumn('carga_envio', 'catalogocargaid')) {
            return;
        }
        Schema::table('carga_envio', function (Blueprint $table) {
            if (! $this->indexExists('carga_envio', 'carga_envio_catalogo_idx')) {
                $table->index('catalogocargaid', 'carga_envio_catalogo_idx');
            }
        });
    }

    private function checksCantidadesMontosSqlite(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }

        $this->tryAddCheck('detallepedido', 'detallepedido_cantidad_chk', '(cantidad >= 0)');
        $this->tryAddCheck('venta', 'venta_montos_chk',
            '(cantidad >= 0) AND (preciounitario >= 0) AND (total >= 0)');
        $this->tryAddCheck('produccion', 'produccion_cantidades_chk',
            '(cantidad >= 0) AND (cantidad_base IS NULL OR cantidad_base >= 0)');
        $this->tryAddCheck('loteinsumo', 'loteinsumo_cantidades_chk',
            '(cantidadusada >= 0) AND (costototal IS NULL OR costototal >= 0)');
        $this->tryAddCheck('carga_envio', 'carga_envio_cantidades_chk', '(cantidad >= 0) AND (peso >= 0)');

        if (Schema::hasTable('calificacion_envio') && Schema::hasColumn('calificacion_envio', 'puntuacion')) {
            $this->tryAddCheck('calificacion_envio', 'calificacion_envio_puntuacion_chk',
                '(puntuacion >= 0) AND (puntuacion <= 5)');
        }
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
