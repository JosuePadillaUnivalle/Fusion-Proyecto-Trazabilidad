<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lote') && ! Schema::hasColumn('lote', 'catalogotamanoconteoid')) {
            Schema::table('lote', function (Blueprint $table) {
                $table->unsignedBigInteger('catalogotamanoconteoid')->nullable()->after('cantidad_semilla_planificada');
                if (Schema::hasTable('catalogo_tamano_conteo')) {
                    $table->foreign('catalogotamanoconteoid')
                        ->references('catalogotamanoconteoid')
                        ->on('catalogo_tamano_conteo')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('produccionalmacenamiento')) {
            Schema::table('produccionalmacenamiento', function (Blueprint $table) {
                if (! Schema::hasColumn('produccionalmacenamiento', 'catalogotamanoconteoid')) {
                    $table->unsignedBigInteger('catalogotamanoconteoid')->nullable()->after('unidadmedidaid');
                    if (Schema::hasTable('catalogo_tamano_conteo')) {
                        $table->foreign('catalogotamanoconteoid')
                            ->references('catalogotamanoconteoid')
                            ->on('catalogo_tamano_conteo')
                            ->nullOnDelete();
                    }
                }
                if (! Schema::hasColumn('produccionalmacenamiento', 'cantidad_empaques')) {
                    $table->unsignedInteger('cantidad_empaques')->nullable()->after('cantidad');
                }
                if (! Schema::hasColumn('produccionalmacenamiento', 'cantidad_unidades')) {
                    $table->unsignedInteger('cantidad_unidades')->nullable()->after('cantidad_empaques');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lote') && Schema::hasColumn('lote', 'catalogotamanoconteoid')) {
            Schema::table('lote', function (Blueprint $table) {
                $table->dropForeign(['catalogotamanoconteoid']);
                $table->dropColumn('catalogotamanoconteoid');
            });
        }

        if (Schema::hasTable('produccionalmacenamiento')) {
            Schema::table('produccionalmacenamiento', function (Blueprint $table) {
                if (Schema::hasColumn('produccionalmacenamiento', 'catalogotamanoconteoid')) {
                    $table->dropForeign(['catalogotamanoconteoid']);
                }
                foreach (['catalogotamanoconteoid', 'cantidad_empaques', 'cantidad_unidades'] as $col) {
                    if (Schema::hasColumn('produccionalmacenamiento', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
