<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('envio_asignacion_multiple')) {
            Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                if (! Schema::hasColumn('envio_asignacion_multiple', 'fecha_recepcion_planta')) {
                    $table->dateTime('fecha_recepcion_planta')->nullable()->after('fecha_asignacion');
                }
                if (! Schema::hasColumn('envio_asignacion_multiple', 'recepcion_usuarioid')) {
                    $table->unsignedBigInteger('recepcion_usuarioid')->nullable()->after('fecha_recepcion_planta');
                }
            });

            if (Schema::hasColumn('envio_asignacion_multiple', 'recepcion_usuarioid')) {
                Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                    $table->foreign('recepcion_usuarioid')
                        ->references('usuarioid')
                        ->on('usuario')
                        ->nullOnDelete();
                });
            }
        }

        if (Schema::hasTable('estado_asignacion_multiple_catalogo')) {
            foreach (['en_transporte_planta', 'recibido_planta'] as $nombre) {
                if (! DB::table('estado_asignacion_multiple_catalogo')->where('nombre', $nombre)->exists()) {
                    DB::table('estado_asignacion_multiple_catalogo')->insert(['nombre' => $nombre]);
                }
            }
        }

        if (Schema::hasTable('lote_produccion_materia_prima')) {
            if (! Schema::hasColumn('lote_produccion_materia_prima', 'insumoid')) {
                Schema::table('lote_produccion_materia_prima', function (Blueprint $table) {
                    $table->unsignedBigInteger('insumoid')->nullable()->after('materiaprimaloteid');
                });
            }
            if (Schema::hasTable('insumo') && Schema::hasColumn('lote_produccion_materia_prima', 'insumoid')) {
                try {
                    Schema::table('lote_produccion_materia_prima', function (Blueprint $table) {
                        $table->foreign('insumoid')->references('insumoid')->on('insumo')->nullOnDelete();
                    });
                } catch (\Throwable) {
                    // FK ya existe
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lote_produccion_materia_prima') && Schema::hasColumn('lote_produccion_materia_prima', 'insumoid')) {
            Schema::table('lote_produccion_materia_prima', function (Blueprint $table) {
                $table->dropForeign(['insumoid']);
                $table->dropColumn('insumoid');
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple')) {
            Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                if (Schema::hasColumn('envio_asignacion_multiple', 'recepcion_usuarioid')) {
                    $table->dropForeign(['recepcion_usuarioid']);
                    $table->dropColumn('recepcion_usuarioid');
                }
                if (Schema::hasColumn('envio_asignacion_multiple', 'fecha_recepcion_planta')) {
                    $table->dropColumn('fecha_recepcion_planta');
                }
            });
        }
    }
};
