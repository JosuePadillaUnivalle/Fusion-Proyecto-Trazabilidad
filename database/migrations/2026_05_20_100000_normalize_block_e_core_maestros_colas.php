<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bloque E — núcleo operativo (usuario, almacén), maestros geográficos/comerciales, cabeceras planta,
 * catálogos simples por nombre, estadolote, pivote carga–envío y colas (jobs / failed_jobs).
 */
return new class extends Migration {
    public function up(): void
    {
        $this->indexesUsuario();
        $this->indexesAlmacen();
        $this->indexesUnidadMedida();
        $this->indexesDatosPlantaYDireccionLogistica();
        $this->indexesClienteComercial();
        $this->indexesRecogidaEntrega();
        $this->indexesProcesoPlantaCabeceras();
        $this->uniqueProcesoPlantaNombreSiAplica();
        $this->uniqueMaquinaPlantaCodigoSiAplica();
        $this->indexesMaquinaPlantaActivos();
        $this->uniqueNombreEnCatalogosSimples();
        $this->indexesAsignacionCarga();
        $this->indexesEstadolote();
        $this->indexesJobsYFailedJobs();
        $this->indexesFirmasEnvio();
    }

    public function down(): void
    {
        $this->dropIndexIfExists('firma_transportista_envio', 'firma_transp_fecha_idx');
        $this->dropIndexIfExists('firma_recepcion_envio', 'firma_recep_fecha_idx');
        $this->dropIndexIfExists('failed_jobs', 'failed_jobs_failed_at_idx');
        $this->dropIndexIfExists('failed_jobs', 'failed_jobs_queue_idx');
        $this->dropIndexIfExists('jobs', 'jobs_queue_reserved_idx');
        $this->dropIndexIfExists('estadolote', 'estadolote_lote_fecha_idx');
        $this->dropIndexIfExists('asignacion_carga', 'asign_carga_carga_idx');
        $this->dropUniqueByIndexNameIfExists('estadolote_tipo', 'estadolote_tipo_nombre_unique');
        $this->dropUniqueByIndexNameIfExists('tipoalmacen', 'tipoalmacen_nombre_unique');
        $this->dropUniqueByIndexNameIfExists('tipoactividad', 'tipoactividad_nombre_unique');
        $this->dropUniqueByIndexNameIfExists('destinoproduccion', 'destinoproduccion_nombre_unique');
        $this->dropUniqueByIndexNameIfExists('estadoloteinsumo', 'estadoloteinsumo_nombre_unique');
        $this->dropUniqueByIndexNameIfExists('prioridad', 'prioridad_nombre_unique');
        $this->dropUniqueByIndexNameIfExists('cultivo', 'cultivo_nombre_unique');
        $this->dropUniqueByIndexNameIfExists('tipoinsumo', 'tipoinsumo_nombre_unique');
        $this->dropIndexIfExists('maquina_planta', 'maquina_planta_activo_idx');
        $this->dropUniqueByIndexNameIfExists('maquina_planta', 'maquina_planta_codigo_unique');
        $this->dropIndexIfExists('proceso_planta', 'proceso_planta_activo_idx');
        $this->dropUniqueByIndexNameIfExists('proceso_planta', 'proceso_planta_nombre_unique');
        $this->dropIndexIfExists('recogida_entrega', 'recogida_entrega_fecha_idx');
        $this->dropIndexIfExists('cliente_comercial', 'cliente_comercial_activo_idx');
        $this->dropIndexIfExists('cliente_comercial', 'cliente_comercial_nit_idx');
        $this->dropIndexIfExists('direccion_logistica', 'dir_logistica_activo_idx');
        $this->dropIndexIfExists('direccion_logistica', 'dir_logistica_ciudad_idx');
        $this->dropIndexIfExists('datos_planta', 'datos_planta_depto_idx');
        $this->dropIndexIfExists('datos_planta', 'datos_planta_ciudad_idx');
        $this->dropIndexIfExists('unidadmedida', 'unidadmedida_categoria_idx');
        $this->dropIndexIfExists('almacen', 'almacen_direccion_log_idx');
        $this->dropIndexIfExists('almacen', 'almacen_activo_idx');
        $this->dropIndexIfExists('almacen', 'almacen_tipo_idx');
        $this->dropIndexIfExists('usuario', 'usuario_activo_idx');
        $this->dropIndexIfExists('usuario', 'usuario_role_idx');
    }

    private function indexesUsuario(): void
    {
        if (! Schema::hasTable('usuario')) {
            return;
        }
        Schema::table('usuario', function (Blueprint $table) {
            if (Schema::hasColumn('usuario', 'role') && ! $this->indexExists('usuario', 'usuario_role_idx')) {
                $table->index('role', 'usuario_role_idx');
            }
            if (! $this->indexExists('usuario', 'usuario_activo_idx')) {
                $table->index('activo', 'usuario_activo_idx');
            }
        });
    }

    private function indexesAlmacen(): void
    {
        if (! Schema::hasTable('almacen')) {
            return;
        }
        Schema::table('almacen', function (Blueprint $table) {
            if (! $this->indexExists('almacen', 'almacen_tipo_idx')) {
                $table->index('tipoalmacenid', 'almacen_tipo_idx');
            }
            if (! $this->indexExists('almacen', 'almacen_activo_idx')) {
                $table->index('activo', 'almacen_activo_idx');
            }
            if (Schema::hasColumn('almacen', 'direccionlogisticaid')
                && ! $this->indexExists('almacen', 'almacen_direccion_log_idx')) {
                $table->index('direccionlogisticaid', 'almacen_direccion_log_idx');
            }
        });
    }

    private function indexesUnidadMedida(): void
    {
        if (! Schema::hasTable('unidadmedida') || ! Schema::hasColumn('unidadmedida', 'categoria')) {
            return;
        }
        Schema::table('unidadmedida', function (Blueprint $table) {
            if (! $this->indexExists('unidadmedida', 'unidadmedida_categoria_idx')) {
                $table->index('categoria', 'unidadmedida_categoria_idx');
            }
        });
    }

    private function indexesDatosPlantaYDireccionLogistica(): void
    {
        if (Schema::hasTable('datos_planta')) {
            Schema::table('datos_planta', function (Blueprint $table) {
                if (Schema::hasColumn('datos_planta', 'ciudad')
                    && ! $this->indexExists('datos_planta', 'datos_planta_ciudad_idx')) {
                    $table->index('ciudad', 'datos_planta_ciudad_idx');
                }
                if (Schema::hasColumn('datos_planta', 'departamento')
                    && ! $this->indexExists('datos_planta', 'datos_planta_depto_idx')) {
                    $table->index('departamento', 'datos_planta_depto_idx');
                }
            });
        }
        if (Schema::hasTable('direccion_logistica')) {
            Schema::table('direccion_logistica', function (Blueprint $table) {
                if (Schema::hasColumn('direccion_logistica', 'ciudad')
                    && ! $this->indexExists('direccion_logistica', 'dir_logistica_ciudad_idx')) {
                    $table->index('ciudad', 'dir_logistica_ciudad_idx');
                }
                if (Schema::hasColumn('direccion_logistica', 'activo')
                    && ! $this->indexExists('direccion_logistica', 'dir_logistica_activo_idx')) {
                    $table->index('activo', 'dir_logistica_activo_idx');
                }
            });
        }
    }

    private function indexesClienteComercial(): void
    {
        if (! Schema::hasTable('cliente_comercial')) {
            return;
        }
        Schema::table('cliente_comercial', function (Blueprint $table) {
            if (Schema::hasColumn('cliente_comercial', 'nit')
                && ! $this->indexExists('cliente_comercial', 'cliente_comercial_nit_idx')) {
                $table->index('nit', 'cliente_comercial_nit_idx');
            }
            if (Schema::hasColumn('cliente_comercial', 'activo')
                && ! $this->indexExists('cliente_comercial', 'cliente_comercial_activo_idx')) {
                $table->index('activo', 'cliente_comercial_activo_idx');
            }
        });
    }

    private function indexesRecogidaEntrega(): void
    {
        if (! Schema::hasTable('recogida_entrega') || ! Schema::hasColumn('recogida_entrega', 'fecha_recogida')) {
            return;
        }
        Schema::table('recogida_entrega', function (Blueprint $table) {
            if (! $this->indexExists('recogida_entrega', 'recogida_entrega_fecha_idx')) {
                $table->index('fecha_recogida', 'recogida_entrega_fecha_idx');
            }
        });
    }

    private function indexesProcesoPlantaCabeceras(): void
    {
        if (Schema::hasTable('proceso_planta') && Schema::hasColumn('proceso_planta', 'activo')) {
            Schema::table('proceso_planta', function (Blueprint $table) {
                if (! $this->indexExists('proceso_planta', 'proceso_planta_activo_idx')) {
                    $table->index('activo', 'proceso_planta_activo_idx');
                }
            });
        }
    }

    private function uniqueProcesoPlantaNombreSiAplica(): void
    {
        if (! Schema::hasTable('proceso_planta') || ! Schema::hasColumn('proceso_planta', 'nombre')) {
            return;
        }
        $idx = 'proceso_planta_nombre_unique';
        if ($this->indexExists('proceso_planta', $idx)) {
            return;
        }
        if (! $this->columnValuesUnique('proceso_planta', 'nombre')) {
            return;
        }
        Schema::table('proceso_planta', function (Blueprint $table) use ($idx) {
            $table->unique('nombre', $idx);
        });
    }

    private function uniqueMaquinaPlantaCodigoSiAplica(): void
    {
        if (! Schema::hasTable('maquina_planta') || ! Schema::hasColumn('maquina_planta', 'codigo')) {
            return;
        }
        $idx = 'maquina_planta_codigo_unique';
        if ($this->indexExists('maquina_planta', $idx)) {
            return;
        }
        if (! $this->codigoNonNullUniqueSafe('maquina_planta', 'codigo')) {
            return;
        }
        Schema::table('maquina_planta', function (Blueprint $table) use ($idx) {
            $table->unique('codigo', $idx);
        });
    }

    private function indexesMaquinaPlantaActivos(): void
    {
        if (! Schema::hasTable('maquina_planta') || ! Schema::hasColumn('maquina_planta', 'activo')) {
            return;
        }
        Schema::table('maquina_planta', function (Blueprint $table) {
            if (! $this->indexExists('maquina_planta', 'maquina_planta_activo_idx')) {
                $table->index('activo', 'maquina_planta_activo_idx');
            }
        });
    }

    private function uniqueNombreEnCatalogosSimples(): void
    {
        foreach (
            [
                'tipoinsumo',
                'cultivo',
                'prioridad',
                'estadoloteinsumo',
                'destinoproduccion',
                'tipoactividad',
                'tipoalmacen',
                'estadolote_tipo',
            ] as $table
        ) {
            $this->uniqueNombreIfApplicable($table);
        }
    }

    private function uniqueNombreIfApplicable(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'nombre')) {
            return;
        }
        $idx = $table.'_nombre_unique';
        if ($this->indexExists($table, $idx)) {
            return;
        }
        if (! $this->columnValuesUnique($table, 'nombre')) {
            return;
        }
        Schema::table($table, function (Blueprint $t) use ($idx) {
            $t->unique('nombre', $idx);
        });
    }

    private function indexesAsignacionCarga(): void
    {
        if (! Schema::hasTable('asignacion_carga') || ! Schema::hasColumn('asignacion_carga', 'cargaenvioid')) {
            return;
        }
        Schema::table('asignacion_carga', function (Blueprint $table) {
            if (! $this->indexExists('asignacion_carga', 'asign_carga_carga_idx')) {
                $table->index('cargaenvioid', 'asign_carga_carga_idx');
            }
        });
    }

    private function indexesEstadolote(): void
    {
        if (! Schema::hasTable('estadolote')
            || ! Schema::hasColumn('estadolote', 'loteid')
            || ! Schema::hasColumn('estadolote', 'fecharegistro')) {
            return;
        }
        Schema::table('estadolote', function (Blueprint $table) {
            if (! $this->indexExists('estadolote', 'estadolote_lote_fecha_idx')) {
                $table->index(['loteid', 'fecharegistro'], 'estadolote_lote_fecha_idx');
            }
        });
    }

    private function indexesJobsYFailedJobs(): void
    {
        if (Schema::hasTable('jobs')
            && Schema::hasColumn('jobs', 'queue')
            && Schema::hasColumn('jobs', 'reserved_at')) {
            Schema::table('jobs', function (Blueprint $table) {
                if (! $this->indexExists('jobs', 'jobs_queue_reserved_idx')) {
                    $table->index(['queue', 'reserved_at'], 'jobs_queue_reserved_idx');
                }
            });
        }
        if (Schema::hasTable('failed_jobs')) {
            Schema::table('failed_jobs', function (Blueprint $table) {
                if (Schema::hasColumn('failed_jobs', 'queue')
                    && ! $this->indexExists('failed_jobs', 'failed_jobs_queue_idx')) {
                    $table->index('queue', 'failed_jobs_queue_idx');
                }
                if (Schema::hasColumn('failed_jobs', 'failed_at')
                    && ! $this->indexExists('failed_jobs', 'failed_jobs_failed_at_idx')) {
                    $table->index('failed_at', 'failed_jobs_failed_at_idx');
                }
            });
        }
    }

    private function indexesFirmasEnvio(): void
    {
        if (Schema::hasTable('firma_recepcion_envio') && Schema::hasColumn('firma_recepcion_envio', 'fechafirma')) {
            Schema::table('firma_recepcion_envio', function (Blueprint $table) {
                if (! $this->indexExists('firma_recepcion_envio', 'firma_recep_fecha_idx')) {
                    $table->index('fechafirma', 'firma_recep_fecha_idx');
                }
            });
        }
        if (Schema::hasTable('firma_transportista_envio') && Schema::hasColumn('firma_transportista_envio', 'fechafirma')) {
            Schema::table('firma_transportista_envio', function (Blueprint $table) {
                if (! $this->indexExists('firma_transportista_envio', 'firma_transp_fecha_idx')) {
                    $table->index('fechafirma', 'firma_transp_fecha_idx');
                }
            });
        }
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

    private function codigoNonNullUniqueSafe(string $table, string $column): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return false;
        }
        $values = DB::table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->pluck($column);

        return $values->count() === $values->unique()->count();
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
