<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('almacen')) {
            return;
        }

        Schema::table('almacen', function (Blueprint $table) {
            if (! Schema::hasColumn('almacen', 'responsable_usuarioid')) {
                $table->unsignedBigInteger('responsable_usuarioid')->nullable()->after('ambito');
                $table->foreign('responsable_usuarioid')
                    ->references('usuarioid')
                    ->on('usuario')
                    ->nullOnDelete();
                $table->index('responsable_usuarioid', 'almacen_responsable_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('almacen') || ! Schema::hasColumn('almacen', 'responsable_usuarioid')) {
            return;
        }

        Schema::table('almacen', function (Blueprint $table) {
            $table->dropForeign(['responsable_usuarioid']);
            $table->dropIndex('almacen_responsable_idx');
            $table->dropColumn('responsable_usuarioid');
        });
    }
};
