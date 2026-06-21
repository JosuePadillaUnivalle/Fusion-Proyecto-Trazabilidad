<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insumo_presentacion')) {
            return;
        }

        Schema::table('insumo_presentacion', function (Blueprint $table) {
            if (! Schema::hasColumn('insumo_presentacion', 'tipoempaqueid')) {
                $table->unsignedBigInteger('tipoempaqueid')->nullable()->after('insumoid');
                $table->foreign('tipoempaqueid')
                    ->references('tipoempaqueid')
                    ->on('tipo_empaque')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('insumo_presentacion')) {
            return;
        }

        Schema::table('insumo_presentacion', function (Blueprint $table) {
            if (Schema::hasColumn('insumo_presentacion', 'tipoempaqueid')) {
                $table->dropForeign(['tipoempaqueid']);
                $table->dropColumn('tipoempaqueid');
            }
        });
    }
};
