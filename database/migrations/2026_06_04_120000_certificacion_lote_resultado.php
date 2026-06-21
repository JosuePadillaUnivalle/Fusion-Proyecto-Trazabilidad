<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('certificacion_lote')) {
            return;
        }

        Schema::table('certificacion_lote', function (Blueprint $table) {
            if (! Schema::hasColumn('certificacion_lote', 'resultado')) {
                $table->string('resultado', 30)->default('Certificado')->after('codigo_certificado');
            }
        });

        DB::table('certificacion_lote')
            ->whereNull('resultado')
            ->orWhere('resultado', '')
            ->update(['resultado' => 'Certificado']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('certificacion_lote')) {
            return;
        }

        Schema::table('certificacion_lote', function (Blueprint $table) {
            if (Schema::hasColumn('certificacion_lote', 'resultado')) {
                $table->dropColumn('resultado');
            }
        });
    }
};
