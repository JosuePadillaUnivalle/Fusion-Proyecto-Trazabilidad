<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedido')) {
            return;
        }

        Schema::table('pedido', function (Blueprint $table) {
            if (! Schema::hasColumn('pedido', 'hora_recogida')) {
                $table->time('hora_recogida')->nullable()->after('fechaEntregaDeseada');
            }
            if (! Schema::hasColumn('pedido', 'hora_entrega_estimada')) {
                $table->time('hora_entrega_estimada')->nullable()->after('hora_recogida');
            }
            if (! Schema::hasColumn('pedido', 'instrucciones_recogida')) {
                $table->text('instrucciones_recogida')->nullable()->after('hora_entrega_estimada');
            }
            if (! Schema::hasColumn('pedido', 'instrucciones_entrega')) {
                $table->text('instrucciones_entrega')->nullable()->after('instrucciones_recogida');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pedido')) {
            return;
        }

        Schema::table('pedido', function (Blueprint $table) {
            foreach (['instrucciones_entrega', 'instrucciones_recogida', 'hora_entrega_estimada', 'hora_recogida'] as $col) {
                if (Schema::hasColumn('pedido', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
