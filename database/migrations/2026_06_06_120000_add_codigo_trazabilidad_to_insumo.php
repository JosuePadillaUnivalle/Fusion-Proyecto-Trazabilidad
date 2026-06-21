<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insumo') || Schema::hasColumn('insumo', 'codigo_trazabilidad')) {
            return;
        }

        Schema::table('insumo', function (Blueprint $table) {
            $table->string('codigo_trazabilidad', 80)->nullable()->unique()->after('nombre');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasColumn('insumo', 'codigo_trazabilidad')) {
            return;
        }

        Schema::table('insumo', function (Blueprint $table) {
            $table->dropColumn('codigo_trazabilidad');
        });
    }
};
