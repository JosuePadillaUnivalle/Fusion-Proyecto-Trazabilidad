<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enlaza catálogos y recogida_entrega que quedaban sin FK en el diagrama ER.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->ensureEstadoAsignacionCatalogValues();
        $this->linkEnvioAsignacionMultipleCatalogs();
        $this->linkCargaEnvioTipoEmpaque();
    }

    public function down(): void
    {
        $this->dropCargaEnvioTipoEmpaque();
        $this->dropEnvioAsignacionMultipleCatalogs();
    }

    private function ensureEstadoAsignacionCatalogValues(): void
    {
        if (! Schema::hasTable('estado_asignacion_multiple_catalogo')) {
            return;
        }

        foreach (['pendiente', 'asignado', 'en_ruta', 'entregado'] as $nombre) {
            if (! DB::table('estado_asignacion_multiple_catalogo')->where('nombre', $nombre)->exists()) {
                DB::table('estado_asignacion_multiple_catalogo')->insert(['nombre' => $nombre]);
            }
        }
    }

    private function linkEnvioAsignacionMultipleCatalogs(): void
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return;
        }

        Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
            if (! Schema::hasColumn('envio_asignacion_multiple', 'estadoasignacioncatalogoid')) {
                $table->unsignedBigInteger('estadoasignacioncatalogoid')->nullable()->after('estado');
            }
            if (! Schema::hasColumn('envio_asignacion_multiple', 'motivocancelacionid')) {
                $table->unsignedBigInteger('motivocancelacionid')->nullable()->after('estadoasignacioncatalogoid');
            }
            if (! Schema::hasColumn('envio_asignacion_multiple', 'tipotransporteid')) {
                $table->unsignedBigInteger('tipotransporteid')->nullable()->after('motivocancelacionid');
            }
            if (! Schema::hasColumn('envio_asignacion_multiple', 'recogidaentregaid')) {
                $table->unsignedBigInteger('recogidaentregaid')->nullable()->after('tipotransporteid');
            }
        });

        $this->backfillEstadoAsignacionCatalogIds();

        Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
            if (Schema::hasTable('estado_asignacion_multiple_catalogo')
                && Schema::hasColumn('envio_asignacion_multiple', 'estadoasignacioncatalogoid')) {
                $this->addForeignIfMissing(
                    $table,
                    'envio_asignacion_multiple',
                    'estadoasignacioncatalogoid',
                    'estado_asignacion_multiple_catalogo',
                    'estadoasignacioncatalogoid'
                );
            }
            if (Schema::hasTable('motivo_cancelacion_envio')
                && Schema::hasColumn('envio_asignacion_multiple', 'motivocancelacionid')) {
                $this->addForeignIfMissing(
                    $table,
                    'envio_asignacion_multiple',
                    'motivocancelacionid',
                    'motivo_cancelacion_envio',
                    'motivocancelacionid'
                );
            }
            if (Schema::hasTable('tipo_transporte')
                && Schema::hasColumn('envio_asignacion_multiple', 'tipotransporteid')) {
                $this->addForeignIfMissing(
                    $table,
                    'envio_asignacion_multiple',
                    'tipotransporteid',
                    'tipo_transporte',
                    'tipotransporteid'
                );
            }
            if (Schema::hasTable('recogida_entrega')
                && Schema::hasColumn('envio_asignacion_multiple', 'recogidaentregaid')) {
                $this->addForeignIfMissing(
                    $table,
                    'envio_asignacion_multiple',
                    'recogidaentregaid',
                    'recogida_entrega',
                    'recogidaentregaid'
                );
            }
        });
    }

    private function backfillEstadoAsignacionCatalogIds(): void
    {
        if (! Schema::hasTable('estado_asignacion_multiple_catalogo')
            || ! Schema::hasColumn('envio_asignacion_multiple', 'estadoasignacioncatalogoid')) {
            return;
        }

        $catalog = DB::table('estado_asignacion_multiple_catalogo')->pluck('estadoasignacioncatalogoid', 'nombre');

        $aliases = [
            'pendiente'   => ['pendiente', 'creada'],
            'asignado'    => ['asignado', 'asignada'],
            'en_ruta'     => ['en_ruta', 'en_transito'],
            'entregado'   => ['entregado', 'entregada'],
            'cancelado'   => ['cancelada', 'cancelado'],
            'cancelada'   => ['cancelada', 'cancelado'],
            'creada'      => ['creada', 'pendiente'],
            'asignada'    => ['asignada', 'asignado'],
            'en_transito' => ['en_transito', 'en_ruta'],
            'entregada'   => ['entregada', 'entregado'],
        ];

        DB::table('envio_asignacion_multiple')
            ->select(['envioasignacionmultipleid', 'estado'])
            ->orderBy('envioasignacionmultipleid')
            ->chunkById(200, function ($rows) use ($catalog, $aliases) {
                foreach ($rows as $row) {
                    $estado = strtolower(trim((string) $row->estado));
                    if ($estado === '') {
                        continue;
                    }

                    $candidates = $aliases[$estado] ?? [$estado];
                    $catalogId = null;
                    foreach ($candidates as $nombre) {
                        if (isset($catalog[$nombre])) {
                            $catalogId = $catalog[$nombre];
                            break;
                        }
                    }

                    if ($catalogId !== null) {
                        DB::table('envio_asignacion_multiple')
                            ->where('envioasignacionmultipleid', $row->envioasignacionmultipleid)
                            ->update(['estadoasignacioncatalogoid' => $catalogId]);
                    }
                }
            }, 'envioasignacionmultipleid');
    }

    private function linkCargaEnvioTipoEmpaque(): void
    {
        if (! Schema::hasTable('carga_envio') || ! Schema::hasTable('tipo_empaque')) {
            return;
        }

        if (! Schema::hasColumn('carga_envio', 'tipoempaqueid')) {
            Schema::table('carga_envio', function (Blueprint $table) {
                $table->unsignedBigInteger('tipoempaqueid')->nullable()->after('catalogocargaid');
            });
        }

        if (Schema::hasTable('catalogo_carga') && Schema::hasColumn('catalogo_carga', 'empaque')) {
            $empaques = DB::table('tipo_empaque')->pluck('tipoempaqueid', 'nombre');
            DB::table('carga_envio')
                ->join('catalogo_carga', 'carga_envio.catalogocargaid', '=', 'catalogo_carga.catalogocargaid')
                ->whereNull('carga_envio.tipoempaqueid')
                ->select(['carga_envio.cargaenvioid', 'catalogo_carga.empaque'])
                ->orderBy('carga_envio.cargaenvioid')
                ->chunk(200, function ($rows) use ($empaques) {
                    foreach ($rows as $row) {
                        $empaque = trim((string) $row->empaque);
                        if ($empaque === '' || ! isset($empaques[$empaque])) {
                            continue;
                        }
                        DB::table('carga_envio')
                            ->where('cargaenvioid', $row->cargaenvioid)
                            ->update(['tipoempaqueid' => $empaques[$empaque]]);
                    }
                });
        }

        Schema::table('carga_envio', function (Blueprint $table) {
            $this->addForeignIfMissing(
                $table,
                'carga_envio',
                'tipoempaqueid',
                'tipo_empaque',
                'tipoempaqueid'
            );
        });
    }

    private function addForeignIfMissing(
        Blueprint $table,
        string $fromTable,
        string $column,
        string $toTable,
        string $references
    ): void {
        if (! Schema::hasColumn($fromTable, $column)) {
            return;
        }

        try {
            $table->foreign($column)->references($references)->on($toTable)->nullOnDelete();
        } catch (\Throwable) {
            //
        }
    }

    private function dropEnvioAsignacionMultipleCatalogs(): void
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return;
        }

        Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
            foreach (['recogidaentregaid', 'tipotransporteid', 'motivocancelacionid', 'estadoasignacioncatalogoid'] as $col) {
                if (! Schema::hasColumn('envio_asignacion_multiple', $col)) {
                    continue;
                }
                try {
                    $table->dropForeign([$col]);
                } catch (\Throwable) {
                    //
                }
                $table->dropColumn($col);
            }
        });
    }

    private function dropCargaEnvioTipoEmpaque(): void
    {
        if (! Schema::hasTable('carga_envio') || ! Schema::hasColumn('carga_envio', 'tipoempaqueid')) {
            return;
        }

        Schema::table('carga_envio', function (Blueprint $table) {
            try {
                $table->dropForeign(['tipoempaqueid']);
            } catch (\Throwable) {
                //
            }
            $table->dropColumn('tipoempaqueid');
        });
    }

};
