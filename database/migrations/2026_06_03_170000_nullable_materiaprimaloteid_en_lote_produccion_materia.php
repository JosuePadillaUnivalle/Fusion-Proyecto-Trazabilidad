<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lote_produccion_materia_prima')
            || ! Schema::hasColumn('lote_produccion_materia_prima', 'materiaprimaloteid')) {
            return;
        }

        try {
            Schema::table('lote_produccion_materia_prima', function (Blueprint $table) {
                $table->dropForeign(['materiaprimaloteid']);
            });
        } catch (\Throwable) {
            // Sin FK o ya eliminada
        }

        Schema::table('lote_produccion_materia_prima', function (Blueprint $table) {
            $table->unsignedBigInteger('materiaprimaloteid')->nullable()->change();
        });

        try {
            Schema::table('lote_produccion_materia_prima', function (Blueprint $table) {
                $table->foreign('materiaprimaloteid')
                    ->references('materiaprimaloteid')
                    ->on('materia_prima_lote')
                    ->nullOnDelete();
            });
        } catch (\Throwable) {
            // FK ya existe
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('lote_produccion_materia_prima')
            || ! Schema::hasColumn('lote_produccion_materia_prima', 'materiaprimaloteid')) {
            return;
        }

        try {
            Schema::table('lote_produccion_materia_prima', function (Blueprint $table) {
                $table->dropForeign(['materiaprimaloteid']);
            });
        } catch (\Throwable) {
        }

        Schema::table('lote_produccion_materia_prima', function (Blueprint $table) {
            $table->unsignedBigInteger('materiaprimaloteid')->nullable(false)->change();
        });
    }
};
