<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('insumo') && ! Schema::hasColumn('insumo', 'imagenurl')) {
            Schema::table('insumo', function (Blueprint $table) {
                $table->text('imagenurl')->nullable()->after('descripcion');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('insumo') && Schema::hasColumn('insumo', 'imagenurl')) {
            Schema::table('insumo', function (Blueprint $table) {
                $table->dropColumn('imagenurl');
            });
        }
    }
};
