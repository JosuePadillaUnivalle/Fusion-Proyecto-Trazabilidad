<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insumo')) {
            return;
        }

        Schema::table('insumo', function (Blueprint $table) {
            if (! Schema::hasColumn('insumo', 'rendimiento_cosecha_kg_ha')) {
                $table->decimal('rendimiento_cosecha_kg_ha', 12, 2)->nullable()->after('semillas_por_kg');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasColumn('insumo', 'rendimiento_cosecha_kg_ha')) {
            return;
        }

        Schema::table('insumo', function (Blueprint $table) {
            $table->dropColumn('rendimiento_cosecha_kg_ha');
        });
    }
};
