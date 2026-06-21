<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedido') && Schema::hasColumn('pedido', 'estado')) {
            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE pedido ALTER COLUMN estado DROP DEFAULT');
                DB::statement('ALTER TABLE pedido DROP CONSTRAINT IF EXISTS pedido_estado_check');
                DB::statement('ALTER TABLE pedido ALTER COLUMN estado TYPE VARCHAR(50) USING estado::text');
                DB::statement("ALTER TABLE pedido ALTER COLUMN estado SET DEFAULT 'sin asignacion'");
                DB::statement("ALTER TABLE pedido ADD CONSTRAINT pedido_estado_check CHECK (estado IN (
                    'sin asignacion',
                    'pendiente',
                    'confirmado',
                    'en produccion',
                    'rechazado'
                ))");
            } else {
                Schema::table('pedido', function (Blueprint $table) {
                    $table->string('estado', 50)->default('sin asignacion')->change();
                });
            }
        }

        if (Schema::hasTable('pedido') && Schema::hasColumn('pedido', 'nombre_planta')) {
            Schema::table('pedido', function (Blueprint $table) {
                $table->string('nombre_planta')->nullable()->change();
            });
        }

        if (Schema::hasTable('detallepedido') && ! Schema::hasColumn('detallepedido', 'insumoid')) {
            Schema::table('detallepedido', function (Blueprint $table) {
                $table->unsignedBigInteger('insumoid')->nullable()->after('pedidoid');
                if (Schema::hasTable('insumo')) {
                    $table->foreign('insumoid')->references('insumoid')->on('insumo')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detallepedido') && Schema::hasColumn('detallepedido', 'insumoid')) {
            Schema::table('detallepedido', function (Blueprint $table) {
                $table->dropForeign(['insumoid']);
                $table->dropColumn('insumoid');
            });
        }
    }
};
