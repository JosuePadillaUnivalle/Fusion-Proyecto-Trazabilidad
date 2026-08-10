<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificacion_lote', function (Blueprint $table) {
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_estado')) {
                $table->string('blockchain_estado', 32)->nullable()->after('recomendaciones');
            }
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_dato_id')) {
                $table->string('blockchain_dato_id', 120)->nullable()->after('blockchain_estado');
            }
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_txid')) {
                $table->string('blockchain_txid', 128)->nullable()->after('blockchain_dato_id');
            }
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_error')) {
                $table->text('blockchain_error')->nullable()->after('blockchain_txid');
            }
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_enviado_en')) {
                $table->timestamp('blockchain_enviado_en')->nullable()->after('blockchain_error');
            }
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_intentos')) {
                $table->unsignedSmallInteger('blockchain_intentos')->default(0)->after('blockchain_enviado_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificacion_lote', function (Blueprint $table) {
            foreach ([
                'blockchain_estado',
                'blockchain_dato_id',
                'blockchain_txid',
                'blockchain_error',
                'blockchain_enviado_en',
                'blockchain_intentos',
            ] as $col) {
                if (Schema::hasColumn('certificacion_lote', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
