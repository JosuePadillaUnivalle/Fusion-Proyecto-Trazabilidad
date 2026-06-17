<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lote') && ! Schema::hasColumn('lote', 'cantidad_semilla_planificada')) {
            Schema::table('lote', function (Blueprint $table) {
                $table->decimal('cantidad_semilla_planificada', 12, 3)
                    ->nullable()
                    ->after('insumosemillaid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lote') && Schema::hasColumn('lote', 'cantidad_semilla_planificada')) {
            Schema::table('lote', function (Blueprint $table) {
                $table->dropColumn('cantidad_semilla_planificada');
            });
        }
    }
};
