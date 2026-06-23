<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedido_distribucion')) {
            return;
        }

        Schema::table('pedido_distribucion', function (Blueprint $table) {
            if (! Schema::hasColumn('pedido_distribucion', 'envio_iniciado_mayorista')) {
                $table->boolean('envio_iniciado_mayorista')->default(false)->after('creado_por_usuarioid');
            }
            if (! Schema::hasColumn('pedido_distribucion', 'fecha_confirmacion_minorista')) {
                $table->timestamp('fecha_confirmacion_minorista')->nullable()->after('envio_iniciado_mayorista');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pedido_distribucion')) {
            return;
        }

        Schema::table('pedido_distribucion', function (Blueprint $table) {
            foreach (['fecha_confirmacion_minorista', 'envio_iniciado_mayorista'] as $col) {
                if (Schema::hasColumn('pedido_distribucion', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
