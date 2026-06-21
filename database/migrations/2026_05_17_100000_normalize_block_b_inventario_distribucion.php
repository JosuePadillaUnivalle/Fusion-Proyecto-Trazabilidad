<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bloque B — inventario por producto (distribución) y comprobantes distribución:
 * índices, unicidad lógica en detalles y reglas de cantidad no negativa (CHECK según motor).
 */
return new class extends Migration {
    public function up(): void
    {
        $this->indexesAlmacenProducto();
        $this->indexesInventarioAlmacenEnvio();
        $this->indexesDistribucionIngreso();
        $this->uniqueDetalleIngresoSiAplica();
        $this->indexesDistribucionSalida();
        $this->uniqueDetalleSalidaSiAplica();
        $this->indexesDistribucionPedidoAlmacen();
        $this->uniqueDetallePedidoAlmacenSiAplica();
        $this->checksCantidadesNoNegativas();
        $this->indexesAlmacenMovimiento();
    }

    public function down(): void
    {
        $this->dropCheckSqlite('almacen_producto', 'almacen_producto_cantidades_chk');
        $this->dropCheckSqlite('inventario_almacen_envio', 'inventario_almacen_envio_cantidades_chk');
        $this->dropCheckSqlite('distribucion_detalle_ingreso', 'distribucion_detalle_ingreso_cantidades_chk');
        $this->dropCheckSqlite('distribucion_detalle_salida', 'distribucion_detalle_salida_cantidades_chk');
        $this->dropCheckSqlite('distribucion_detalle_pedido_almacen', 'distribucion_detalle_pedido_cantidad_chk');
        $this->dropCheckSqlite('almacen_movimiento', 'almacen_movimiento_cantidad_chk');

        $this->dropIndexIfExists('almacen_movimiento', 'almacen_movimiento_insumoid_fecha_idx');

        $this->dropIndexIfExists('distribucion_detalle_pedido_almacen', 'distribucion_det_ped_prod_unique');
        $this->dropIndexIfExists('distribucion_pedido_almacen', 'distribucion_pedido_almacen_almacen_fecha_idx');
        $this->dropIndexIfExists('distribucion_pedido_almacen', 'distribucion_pedido_almacen_estado_idx');

        $this->dropIndexIfExists('distribucion_detalle_salida', 'distribucion_det_sal_prod_unique');
        $this->dropIndexIfExists('distribucion_salida', 'distribucion_salida_almacen_fecha_idx');
        $this->dropIndexIfExists('distribucion_salida', 'distribucion_salida_tipo_idx');

        $this->dropIndexIfExists('distribucion_detalle_ingreso', 'distribucion_det_ing_prod_unique');
        $this->dropIndexIfExists('distribucion_ingreso', 'distribucion_ingreso_almacen_fecha_idx');
        $this->dropIndexIfExists('distribucion_ingreso', 'distribucion_ingreso_tipo_idx');
        $this->dropIndexIfExists('distribucion_ingreso', 'distribucion_ingreso_pedido_idx');

        $this->dropIndexIfExists('inventario_almacen_envio', 'inv_alm_env_almacen_producto_idx');
        $this->dropIndexIfExists('inventario_almacen_envio', 'inv_alm_env_almacen_fecha_idx');

        $this->dropIndexIfExists('almacen_producto', 'almacen_producto_producto_idx');
        $this->dropIndexIfExists('almacen_producto', 'almacen_producto_almacen_idx');
    }

    private function indexesAlmacenProducto(): void
    {
        if (! Schema::hasTable('almacen_producto')) {
            return;
        }
        Schema::table('almacen_producto', function (Blueprint $table) {
            if (! $this->indexExists('almacen_producto', 'almacen_producto_almacen_idx')) {
                $table->index('almacenid', 'almacen_producto_almacen_idx');
            }
            if (! $this->indexExists('almacen_producto', 'almacen_producto_producto_idx')) {
                $table->index('productodistribucionid', 'almacen_producto_producto_idx');
            }
        });
    }

    private function indexesInventarioAlmacenEnvio(): void
    {
        if (! Schema::hasTable('inventario_almacen_envio')) {
            return;
        }
        Schema::table('inventario_almacen_envio', function (Blueprint $table) {
            if (! $this->indexExists('inventario_almacen_envio', 'inv_alm_env_almacen_producto_idx')) {
                $table->index(['almacenid', 'productodistribucionid'], 'inv_alm_env_almacen_producto_idx');
            }
            if (! $this->indexExists('inventario_almacen_envio', 'inv_alm_env_almacen_fecha_idx')) {
                $table->index(['almacenid', 'fecha_ingreso'], 'inv_alm_env_almacen_fecha_idx');
            }
        });
    }

    private function indexesDistribucionIngreso(): void
    {
        if (! Schema::hasTable('distribucion_ingreso')) {
            return;
        }
        Schema::table('distribucion_ingreso', function (Blueprint $table) {
            if (! $this->indexExists('distribucion_ingreso', 'distribucion_ingreso_almacen_fecha_idx')) {
                $table->index(['almacenid', 'fecha'], 'distribucion_ingreso_almacen_fecha_idx');
            }
            if (! $this->indexExists('distribucion_ingreso', 'distribucion_ingreso_tipo_idx')) {
                $table->index('distribuciontipoingresoid', 'distribucion_ingreso_tipo_idx');
            }
            if (! $this->indexExists('distribucion_ingreso', 'distribucion_ingreso_pedido_idx')) {
                $table->index('pedidoid', 'distribucion_ingreso_pedido_idx');
            }
        });
    }

    private function uniqueDetalleIngresoSiAplica(): void
    {
        if (! Schema::hasTable('distribucion_detalle_ingreso')) {
            return;
        }
        if ($this->indexExists('distribucion_detalle_ingreso', 'distribucion_det_ing_prod_unique')) {
            return;
        }
        if (! $this->parejaUnica('distribucion_detalle_ingreso', 'distribucioningresoid', 'productodistribucionid')) {
            return;
        }
        Schema::table('distribucion_detalle_ingreso', function (Blueprint $table) {
            $table->unique(['distribucioningresoid', 'productodistribucionid'], 'distribucion_det_ing_prod_unique');
        });
    }

    private function indexesDistribucionSalida(): void
    {
        if (! Schema::hasTable('distribucion_salida')) {
            return;
        }
        Schema::table('distribucion_salida', function (Blueprint $table) {
            if (! $this->indexExists('distribucion_salida', 'distribucion_salida_almacen_fecha_idx')) {
                $table->index(['almacenid', 'fecha'], 'distribucion_salida_almacen_fecha_idx');
            }
            if (! $this->indexExists('distribucion_salida', 'distribucion_salida_tipo_idx')) {
                $table->index('distribuciontiposalidaid', 'distribucion_salida_tipo_idx');
            }
        });
    }

    private function uniqueDetalleSalidaSiAplica(): void
    {
        if (! Schema::hasTable('distribucion_detalle_salida')) {
            return;
        }
        if ($this->indexExists('distribucion_detalle_salida', 'distribucion_det_sal_prod_unique')) {
            return;
        }
        if (! $this->parejaUnica('distribucion_detalle_salida', 'distribucionsalidaid', 'productodistribucionid')) {
            return;
        }
        Schema::table('distribucion_detalle_salida', function (Blueprint $table) {
            $table->unique(['distribucionsalidaid', 'productodistribucionid'], 'distribucion_det_sal_prod_unique');
        });
    }

    private function indexesDistribucionPedidoAlmacen(): void
    {
        if (! Schema::hasTable('distribucion_pedido_almacen')) {
            return;
        }
        Schema::table('distribucion_pedido_almacen', function (Blueprint $table) {
            if (! $this->indexExists('distribucion_pedido_almacen', 'distribucion_pedido_almacen_almacen_fecha_idx')) {
                $table->index(['almacenid', 'fecha'], 'distribucion_pedido_almacen_almacen_fecha_idx');
            }
            if (! $this->indexExists('distribucion_pedido_almacen', 'distribucion_pedido_almacen_estado_idx')) {
                $table->index('estado', 'distribucion_pedido_almacen_estado_idx');
            }
        });
    }

    private function uniqueDetallePedidoAlmacenSiAplica(): void
    {
        if (! Schema::hasTable('distribucion_detalle_pedido_almacen')) {
            return;
        }
        if ($this->indexExists('distribucion_detalle_pedido_almacen', 'distribucion_det_ped_prod_unique')) {
            return;
        }
        if (! $this->parejaUnica('distribucion_detalle_pedido_almacen', 'distribucionpedidoid', 'productodistribucionid')) {
            return;
        }
        Schema::table('distribucion_detalle_pedido_almacen', function (Blueprint $table) {
            $table->unique(['distribucionpedidoid', 'productodistribucionid'], 'distribucion_det_ped_prod_unique');
        });
    }

    private function checksCantidadesNoNegativas(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }

        $this->tryAddCheck('almacen_producto', 'almacen_producto_cantidades_chk',
            '(stock >= 0) AND (stock_minimo >= 0) AND (en_pedido >= 0)');
        $this->tryAddCheck('inventario_almacen_envio', 'inventario_almacen_envio_cantidades_chk',
            '(cantidad >= 0) AND (peso_total IS NULL OR peso_total >= 0)');
        $this->tryAddCheck('distribucion_detalle_ingreso', 'distribucion_detalle_ingreso_cantidades_chk',
            '(cant_ingreso >= 0) AND (precio >= 0)');
        $this->tryAddCheck('distribucion_detalle_salida', 'distribucion_detalle_salida_cantidades_chk',
            '(cant_salida >= 0) AND (precio >= 0)');
        $this->tryAddCheck('distribucion_detalle_pedido_almacen', 'distribucion_detalle_pedido_cantidad_chk',
            '(cantidad >= 0)');
        $this->tryAddCheck('almacen_movimiento', 'almacen_movimiento_cantidad_chk',
            '(cantidad > 0)');
    }

    private function indexesAlmacenMovimiento(): void
    {
        if (! Schema::hasTable('almacen_movimiento')) {
            return;
        }
        Schema::table('almacen_movimiento', function (Blueprint $table) {
            if (! $this->indexExists('almacen_movimiento', 'almacen_movimiento_insumoid_fecha_idx')) {
                $table->index(['insumoid', 'fecha'], 'almacen_movimiento_insumoid_fecha_idx');
            }
        });
    }

    private function tryAddCheck(string $table, string $constraintName, string $expression): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        try {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraintName} CHECK ({$expression})");
        } catch (\Throwable) {
            // ya existe o versión SQLite sin soporte
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

        if ($connection->getDriverName() === 'pgsql') {
            $result = $connection->selectOne(
                "SELECT COUNT(*) AS c FROM pg_indexes WHERE schemaname = 'public' AND tablename = ? AND indexname = ?",
                [$table, $indexName]
            );
            return isset($result->c) && (int) $result->c > 0;
        }

        $db = $connection->getDatabaseName();
        $result = $connection->selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$db, $table, $indexName]
        );

        return isset($result->c) && (int) $result->c > 0;
    }
};
