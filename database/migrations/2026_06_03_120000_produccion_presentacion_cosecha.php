<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('produccion')) {
            return;
        }

        Schema::table('produccion', function (Blueprint $table) {
            if (! Schema::hasColumn('produccion', 'catalogotamanoconteoid')) {
                $table->unsignedBigInteger('catalogotamanoconteoid')->nullable()->after('cantidad_base');
                if (Schema::hasTable('catalogo_tamano_conteo')) {
                    $table->foreign('catalogotamanoconteoid')
                        ->references('catalogotamanoconteoid')
                        ->on('catalogo_tamano_conteo')
                        ->nullOnDelete();
                }
            }
            if (! Schema::hasColumn('produccion', 'cantidad_unidades')) {
                $table->unsignedInteger('cantidad_unidades')->nullable()->after('catalogotamanoconteoid');
            }
            if (! Schema::hasColumn('produccion', 'cantidad_empaques')) {
                $table->unsignedInteger('cantidad_empaques')->nullable()->after('cantidad_unidades');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('produccion')) {
            return;
        }

        Schema::table('produccion', function (Blueprint $table) {
            if (Schema::hasColumn('produccion', 'catalogotamanoconteoid')) {
                try {
                    $table->dropForeign(['catalogotamanoconteoid']);
                } catch (\Throwable) {
                    // ignore
                }
            }
            foreach (['catalogotamanoconteoid', 'cantidad_unidades', 'cantidad_empaques'] as $col) {
                if (Schema::hasColumn('produccion', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
