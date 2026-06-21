<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('insumo') && ! Schema::hasColumn('insumo', 'actorid')) {
            Schema::table('insumo', function (Blueprint $table) {
                $table->unsignedBigInteger('actorid')->nullable()->after('proveedor');
                $table->foreign('actorid')->references('actorid')->on('actor_abastecimiento')->nullOnDelete();
            });
        }

        if (Schema::hasTable('lote') && ! Schema::hasColumn('lote', 'actorid')) {
            Schema::table('lote', function (Blueprint $table) {
                $table->unsignedBigInteger('actorid')->nullable()->after('cultivoid');
                $table->string('codigo_trazabilidad', 80)->nullable()->after('nombre');
                $table->foreign('actorid')->references('actorid')->on('actor_abastecimiento')->nullOnDelete();
            });
        }

        if (Schema::hasTable('produccion') && ! Schema::hasColumn('produccion', 'procesoplantaid')) {
            Schema::table('produccion', function (Blueprint $table) {
                $table->unsignedBigInteger('procesoplantaid')->nullable()->after('destinoproduccionid');
                $table->unsignedBigInteger('maquinaplantaid')->nullable()->after('procesoplantaid');
                $table->foreign('procesoplantaid')->references('procesoplantaid')->on('proceso_planta')->nullOnDelete();
                $table->foreign('maquinaplantaid')->references('maquinaplantaid')->on('maquina_planta')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('produccion') && Schema::hasColumn('produccion', 'maquinaplantaid')) {
            Schema::table('produccion', function (Blueprint $table) {
                $table->dropForeign(['procesoplantaid']);
                $table->dropForeign(['maquinaplantaid']);
                $table->dropColumn(['procesoplantaid', 'maquinaplantaid']);
            });
        }

        if (Schema::hasTable('lote') && Schema::hasColumn('lote', 'actorid')) {
            Schema::table('lote', function (Blueprint $table) {
                $table->dropForeign(['actorid']);
                $table->dropColumn(['actorid', 'codigo_trazabilidad']);
            });
        }

        if (Schema::hasTable('insumo') && Schema::hasColumn('insumo', 'actorid')) {
            Schema::table('insumo', function (Blueprint $table) {
                $table->dropForeign(['actorid']);
                $table->dropColumn('actorid');
            });
        }
    }
};

