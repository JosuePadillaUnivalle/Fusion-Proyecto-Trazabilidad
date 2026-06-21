<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedido')) {
            Schema::table('pedido', function (Blueprint $table) {
                if (! Schema::hasColumn('pedido', 'fecha_aceptacion_agricola')) {
                    $table->timestamp('fecha_aceptacion_agricola')->nullable()->after('observaciones');
                }
                if (! Schema::hasColumn('pedido', 'aceptado_por_usuarioid')) {
                    $table->unsignedBigInteger('aceptado_por_usuarioid')->nullable()->after('fecha_aceptacion_agricola');
                    if (Schema::hasTable('usuario')) {
                        $table->foreign('aceptado_por_usuarioid')
                            ->references('usuarioid')
                            ->on('usuario')
                            ->nullOnDelete();
                    }
                }
            });
        }

        if (Schema::hasTable('detallepedido')) {
            Schema::table('detallepedido', function (Blueprint $table) {
                if (! Schema::hasColumn('detallepedido', 'producto_ref')) {
                    $table->string('producto_ref', 64)->nullable()->after('insumoid');
                }
                if (! Schema::hasColumn('detallepedido', 'produccionalmacenamientoid')) {
                    $table->unsignedBigInteger('produccionalmacenamientoid')->nullable()->after('producto_ref');
                    if (Schema::hasTable('produccionalmacenamiento')) {
                        $table->foreign('produccionalmacenamientoid')
                            ->references('produccionalmacenamientoid')
                            ->on('produccionalmacenamiento')
                            ->nullOnDelete();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detallepedido')) {
            Schema::table('detallepedido', function (Blueprint $table) {
                foreach (['produccionalmacenamientoid', 'producto_ref'] as $col) {
                    if (Schema::hasColumn('detallepedido', $col)) {
                        if ($col === 'produccionalmacenamientoid') {
                            $table->dropForeign(['produccionalmacenamientoid']);
                        }
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('pedido')) {
            Schema::table('pedido', function (Blueprint $table) {
                if (Schema::hasColumn('pedido', 'aceptado_por_usuarioid')) {
                    $table->dropForeign(['aceptado_por_usuarioid']);
                    $table->dropColumn('aceptado_por_usuarioid');
                }
                if (Schema::hasColumn('pedido', 'fecha_aceptacion_agricola')) {
                    $table->dropColumn('fecha_aceptacion_agricola');
                }
            });
        }
    }
};
