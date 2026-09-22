<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('certificacion_lote')) {
            return;
        }

        Schema::table('certificacion_lote', function (Blueprint $table) {
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_solicitud_id')) {
                $table->string('blockchain_solicitud_id', 120)->nullable()->after('blockchain_dato_id');
            }
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_operacion_id')) {
                $table->string('blockchain_operacion_id', 128)->nullable()->after('blockchain_solicitud_id');
            }
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_hash')) {
                $table->string('blockchain_hash', 64)->nullable()->after('blockchain_operacion_id');
            }
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_payload_version')) {
                $table->unsignedSmallInteger('blockchain_payload_version')->nullable()->after('blockchain_hash');
            }
            if (! Schema::hasColumn('certificacion_lote', 'blockchain_certificado_en')) {
                $table->timestamp('blockchain_certificado_en')->nullable()->after('blockchain_enviado_en');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('certificacion_lote')) {
            return;
        }

        Schema::table('certificacion_lote', function (Blueprint $table) {
            foreach ([
                'blockchain_solicitud_id',
                'blockchain_operacion_id',
                'blockchain_hash',
                'blockchain_payload_version',
                'blockchain_certificado_en',
            ] as $col) {
                if (Schema::hasColumn('certificacion_lote', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
