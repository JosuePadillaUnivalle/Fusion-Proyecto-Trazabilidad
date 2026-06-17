<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('certificacion_lote') && ! Schema::hasColumn('certificacion_lote', 'recomendaciones')) {
            Schema::table('certificacion_lote', function (Blueprint $table) {
                $table->text('recomendaciones')->nullable()->after('observaciones');
            });
        }

        if (Schema::hasTable('evaluacion_final_lote_produccion') && ! Schema::hasColumn('evaluacion_final_lote_produccion', 'recomendaciones')) {
            Schema::table('evaluacion_final_lote_produccion', function (Blueprint $table) {
                $table->text('recomendaciones')->nullable()->after('observaciones');
            });
        }

        if (Schema::hasTable('perfil_transportista') && ! Schema::hasColumn('perfil_transportista', 'licencias_json')) {
            Schema::table('perfil_transportista', function (Blueprint $table) {
                $table->json('licencias_json')->nullable()->after('tipo_licencia');
            });
        }

        if (Schema::hasTable('usuario') && ! Schema::hasColumn('usuario', 'licencias_json')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->json('licencias_json')->nullable()->after('tipo_licencia');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('certificacion_lote') && Schema::hasColumn('certificacion_lote', 'recomendaciones')) {
            Schema::table('certificacion_lote', function (Blueprint $table) {
                $table->dropColumn('recomendaciones');
            });
        }

        if (Schema::hasTable('evaluacion_final_lote_produccion') && Schema::hasColumn('evaluacion_final_lote_produccion', 'recomendaciones')) {
            Schema::table('evaluacion_final_lote_produccion', function (Blueprint $table) {
                $table->dropColumn('recomendaciones');
            });
        }

        if (Schema::hasTable('perfil_transportista') && Schema::hasColumn('perfil_transportista', 'licencias_json')) {
            Schema::table('perfil_transportista', function (Blueprint $table) {
                $table->dropColumn('licencias_json');
            });
        }

        if (Schema::hasTable('usuario') && Schema::hasColumn('usuario', 'licencias_json')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('licencias_json');
            });
        }
    }
};
