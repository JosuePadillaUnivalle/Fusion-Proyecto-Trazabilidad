<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('direccion_logistica') && ! Schema::hasColumn('direccion_logistica', 'tipo_punto')) {
            Schema::table('direccion_logistica', function (Blueprint $table) {
                $table->string('tipo_punto', 20)->nullable()->after('nombre');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('direccion_logistica', 'tipo_punto')) {
            Schema::table('direccion_logistica', function (Blueprint $table) {
                $table->dropColumn('tipo_punto');
            });
        }
    }
};
