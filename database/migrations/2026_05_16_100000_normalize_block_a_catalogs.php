<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bloque A — normalización de catálogos: unicidad lógica, índices y semillas idempotentes.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->normalizeTipoVehiculo();
        $this->normalizeTipoEmpaque();
        $this->normalizeTipoMovimientoMateria();
        $this->normalizeTipoMovimientoAlmacen();
        $this->normalizeCategoriaMateriaPrima();
        $this->normalizeMateriaPrimaBaseCodigo();
        $this->normalizeCatalogoCarga();
        $this->seedTipoTransporte();
        $this->seedMotivosCancelacion();
        $this->seedCondicionesYTiposIncidente();
        $this->normalizeDistribucionTipos();
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

    public function down(): void
    {
        $drop = function (string $table, array $columns): void {
            if (! Schema::hasTable($table)) {
                return;
            }
            try {
                Schema::table($table, function (Blueprint $t) use ($columns) {
                    $t->dropUnique($columns);
                });
            } catch (\Throwable) {
                // índice inexistente u otro nombre según driver
            }
        };

        $drop('distribucion_tipo_salida', ['nombre']);
        $drop('distribucion_tipo_ingreso', ['nombre']);
        $drop('catalogo_carga', ['tipo', 'variedad', 'empaque']);
        $drop('materia_prima_base', ['codigo']);
        $drop('categoria_materia_prima', ['codigo']);
        $drop('tipo_movimiento_almacen', ['nombre']);
        $drop('tipo_movimiento_materia', ['codigo']);
        $drop('tipo_empaque', ['nombre']);
        $drop('tipo_vehiculo', ['nombre']);
        $drop('tipo_vehiculo', ['codigo']);

        if (Schema::hasTable('tipo_vehiculo') && Schema::hasColumn('tipo_vehiculo', 'codigo')) {
            Schema::table('tipo_vehiculo', function (Blueprint $table) {
                $table->dropColumn('codigo');
            });
        }
    }

    private function normalizeTipoVehiculo(): void
    {
        if (! Schema::hasTable('tipo_vehiculo')) {
            return;
        }

        if (! Schema::hasColumn('tipo_vehiculo', 'codigo')) {
            Schema::table('tipo_vehiculo', function (Blueprint $table) {
                $table->string('codigo', 30)->nullable()->after('tipovehiculoid');
            });
        }

        $map = [
            'Motocicleta' => 'MOTO',
            'Camioneta' => 'CAMIONETA',
            'Furgoneta' => 'FURGONETA',
            'Camión pequeño' => 'CAMION_PQ',
            'Camión grande' => 'CAMION_GR',
        ];
        foreach ($map as $nombre => $codigo) {
            DB::table('tipo_vehiculo')->where('nombre', $nombre)->update(['codigo' => $codigo]);
        }

        $idxCodigo = $this->guessUniqueIndexName('tipo_vehiculo', 'codigo');
        if (! $this->indexExists('tipo_vehiculo', $idxCodigo) && $this->codigoNonNullUniqueSafe('tipo_vehiculo', 'codigo')) {
            Schema::table('tipo_vehiculo', function (Blueprint $table) {
                $table->unique('codigo');
            });
        }
        $idxNombre = $this->guessUniqueIndexName('tipo_vehiculo', 'nombre');
        if (! $this->indexExists('tipo_vehiculo', $idxNombre) && $this->columnValuesUnique('tipo_vehiculo', 'nombre')) {
            Schema::table('tipo_vehiculo', function (Blueprint $table) {
                $table->unique('nombre');
            });
        }
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

    private function normalizeTipoEmpaque(): void
    {
        if (! Schema::hasTable('tipo_empaque')) {
            return;
        }
        $idx = $this->guessUniqueIndexName('tipo_empaque', 'nombre');
        if (! $this->indexExists('tipo_empaque', $idx) && $this->columnValuesUnique('tipo_empaque', 'nombre')) {
            Schema::table('tipo_empaque', function (Blueprint $table) {
                $table->unique('nombre');
            });
        }
    }

    private function normalizeTipoMovimientoMateria(): void
    {
        if (! Schema::hasTable('tipo_movimiento_materia')) {
            return;
        }
        $idx = $this->guessUniqueIndexName('tipo_movimiento_materia', 'codigo');
        if (! $this->indexExists('tipo_movimiento_materia', $idx) && $this->columnValuesUnique('tipo_movimiento_materia', 'codigo')) {
            Schema::table('tipo_movimiento_materia', function (Blueprint $table) {
                $table->unique('codigo');
            });
        }
    }

    private function normalizeTipoMovimientoAlmacen(): void
    {
        if (! Schema::hasTable('tipo_movimiento_almacen')) {
            return;
        }
        $idx = $this->guessUniqueIndexName('tipo_movimiento_almacen', 'nombre');
        if (! $this->indexExists('tipo_movimiento_almacen', $idx) && $this->columnValuesUnique('tipo_movimiento_almacen', 'nombre')) {
            Schema::table('tipo_movimiento_almacen', function (Blueprint $table) {
                $table->unique('nombre');
            });
        }
    }

    private function normalizeCategoriaMateriaPrima(): void
    {
        if (! Schema::hasTable('categoria_materia_prima')) {
            return;
        }
        $idx = $this->guessUniqueIndexName('categoria_materia_prima', 'codigo');
        if (! $this->indexExists('categoria_materia_prima', $idx) && $this->columnValuesUnique('categoria_materia_prima', 'codigo')) {
            Schema::table('categoria_materia_prima', function (Blueprint $table) {
                $table->unique('codigo');
            });
        }
    }

    private function normalizeMateriaPrimaBaseCodigo(): void
    {
        if (! Schema::hasTable('materia_prima_base')) {
            return;
        }
        $idx = $this->guessUniqueIndexName('materia_prima_base', 'codigo');
        if (! $this->indexExists('materia_prima_base', $idx) && $this->columnValuesUnique('materia_prima_base', 'codigo')) {
            Schema::table('materia_prima_base', function (Blueprint $table) {
                $table->unique('codigo');
            });
        }
    }

    private function normalizeCatalogoCarga(): void
    {
        if (! Schema::hasTable('catalogo_carga')) {
            return;
        }
        $idx = 'catalogo_carga_tipo_variedad_empaque_unique';
        if ($this->indexExists('catalogo_carga', $idx)) {
            return;
        }
        if (! $this->compositeUniqueSafe('catalogo_carga', ['tipo', 'variedad', 'empaque'])) {
            return;
        }
        Schema::table('catalogo_carga', function (Blueprint $table) {
            $table->unique(['tipo', 'variedad', 'empaque'], 'catalogo_carga_tipo_variedad_empaque_unique');
        });
    }

    private function seedTipoTransporte(): void
    {
        if (! Schema::hasTable('tipo_transporte')) {
            return;
        }
        $now = now();
        $rows = [
            ['nombre' => 'Terrestre', 'descripcion' => 'Transporte por carretera'],
            ['nombre' => 'Refrigerado', 'descripcion' => 'Cadena de frío'],
            ['nombre' => 'Carga general', 'descripcion' => 'Mercancía estándar'],
        ];
        foreach ($rows as $row) {
            if (! DB::table('tipo_transporte')->where('nombre', $row['nombre'])->exists()) {
                DB::table('tipo_transporte')->insert(array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    private function seedMotivosCancelacion(): void
    {
        if (! Schema::hasTable('motivo_cancelacion_envio')) {
            return;
        }
        $now = now();
        foreach (
            [
                ['codigo' => 'CLI', 'titulo' => 'Cancelado por cliente', 'descripcion' => 'Solicitud del cliente o comprador'],
                ['codigo' => 'CLIMA', 'titulo' => 'Clima o vía', 'descripcion' => 'Condiciones meteorológicas o de ruta'],
                ['codigo' => 'VEH', 'titulo' => 'Falla de vehículo', 'descripcion' => 'Avería o indisponibilidad del vehículo'],
                ['codigo' => 'OTRO', 'titulo' => 'Otro motivo', 'descripcion' => 'Motivo no clasificado'],
            ] as $row
        ) {
            if (! DB::table('motivo_cancelacion_envio')->where('codigo', $row['codigo'])->exists()) {
                DB::table('motivo_cancelacion_envio')->insert(array_merge($row, [
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    private function seedCondicionesYTiposIncidente(): void
    {
        $now = now();
        if (Schema::hasTable('condicion_transporte')) {
            foreach (
                [
                    ['codigo' => 'TEMP_OK', 'titulo' => 'Temperatura adecuada', 'descripcion' => 'Cadena de frío dentro de rango'],
                    ['codigo' => 'EMP_INT', 'titulo' => 'Empaque íntegro', 'descripcion' => 'Sin daños visibles en embalaje'],
                    ['codigo' => 'DOC_OK', 'titulo' => 'Documentación completa', 'descripcion' => 'Guías y firmas requeridas'],
                ] as $row
            ) {
                if (! DB::table('condicion_transporte')->where('codigo', $row['codigo'])->exists()) {
                    DB::table('condicion_transporte')->insert(array_merge($row, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            }
        }
        if (Schema::hasTable('tipo_incidente_transporte')) {
            foreach (
                [
                    ['codigo' => 'RETRASO', 'titulo' => 'Retraso en ruta', 'descripcion' => 'Demora superior a lo planificado'],
                    ['codigo' => 'ACC', 'titulo' => 'Accidente o incidente', 'descripcion' => 'Evento que afecta la carga o plazos'],
                    ['codigo' => 'ROBO', 'titulo' => 'Robo o hurto', 'descripcion' => 'Pérdida de mercancía'],
                ] as $row
            ) {
                if (! DB::table('tipo_incidente_transporte')->where('codigo', $row['codigo'])->exists()) {
                    DB::table('tipo_incidente_transporte')->insert(array_merge($row, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            }
        }
    }

    private function normalizeDistribucionTipos(): void
    {
        if (Schema::hasTable('distribucion_tipo_ingreso')) {
            $idx = $this->guessUniqueIndexName('distribucion_tipo_ingreso', 'nombre');
            if (! $this->indexExists('distribucion_tipo_ingreso', $idx) && $this->columnValuesUnique('distribucion_tipo_ingreso', 'nombre')) {
                Schema::table('distribucion_tipo_ingreso', function (Blueprint $table) {
                    $table->unique('nombre');
                });
            }
            $now = now();
            foreach (['Compra', 'Devolución', 'Ajuste inventario', 'Transferencia interna'] as $nombre) {
                if (! DB::table('distribucion_tipo_ingreso')->where('nombre', $nombre)->exists()) {
                    DB::table('distribucion_tipo_ingreso')->insert([
                        'nombre' => $nombre,
                        'descripcion' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
        if (Schema::hasTable('distribucion_tipo_salida')) {
            $idx = $this->guessUniqueIndexName('distribucion_tipo_salida', 'nombre');
            if (! $this->indexExists('distribucion_tipo_salida', $idx) && $this->columnValuesUnique('distribucion_tipo_salida', 'nombre')) {
                Schema::table('distribucion_tipo_salida', function (Blueprint $table) {
                    $table->unique('nombre');
                });
            }
            $now = now();
            foreach (['Venta', 'Despacho', 'Merma', 'Ajuste negativo'] as $nombre) {
                if (! DB::table('distribucion_tipo_salida')->where('nombre', $nombre)->exists()) {
                    DB::table('distribucion_tipo_salida')->insert([
                        'nombre' => $nombre,
                        'descripcion' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    private function guessUniqueIndexName(string $table, string|array $columns): string
    {
        $cols = is_array($columns) ? $columns : [$columns];

        return $table.'_'.implode('_', $cols).'_unique';
    }

    private function compositeUniqueSafe(string $table, array $columns): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }
        foreach ($columns as $col) {
            if (! Schema::hasColumn($table, $col)) {
                return false;
            }
        }
        $rows = DB::table($table)->select($columns)->get();
        if ($rows->isEmpty()) {
            return true;
        }
        $keys = $rows->map(fn ($r) => implode("\0", array_map(fn ($c) => (string) $r->{$c}, $columns)));

        return $keys->count() === $keys->unique()->count();
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $rows = $connection->select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name = ? AND name = ?", [$table, $indexName]);

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
