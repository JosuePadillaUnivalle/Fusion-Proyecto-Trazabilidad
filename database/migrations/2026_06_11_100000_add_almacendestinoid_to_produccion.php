<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('produccion') || Schema::hasColumn('produccion', 'almacendestinoid')) {
            return;
        }

        Schema::table('produccion', function (Blueprint $table) {
            $table->unsignedBigInteger('almacendestinoid')->nullable()->after('destinoproduccionid');
            $table->foreign('almacendestinoid')
                ->references('almacenid')
                ->on('almacen')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('produccion') || ! Schema::hasColumn('produccion', 'almacendestinoid')) {
            return;
        }

        Schema::table('produccion', function (Blueprint $table) {
            $table->dropForeign(['almacendestinoid']);
            $table->dropColumn('almacendestinoid');
        });
    }
};
