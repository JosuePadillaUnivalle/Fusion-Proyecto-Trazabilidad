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

        if (! Schema::hasColumn('pedido_distribucion', 'transportista_usuarioid')) {
            Schema::table('pedido_distribucion', function (Blueprint $table) {
                $table->unsignedBigInteger('transportista_usuarioid')->nullable()->after('rutadistribucionid');
                $table->foreign('transportista_usuarioid')
                    ->references('usuarioid')
                    ->on('usuario')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('pedido_distribucion', 'vehiculoid')) {
            Schema::table('pedido_distribucion', function (Blueprint $table) {
                $table->unsignedBigInteger('vehiculoid')->nullable()->after('transportista_usuarioid');
                $table->foreign('vehiculoid')
                    ->references('vehiculoid')
                    ->on('vehiculo')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pedido_distribucion')) {
            return;
        }

        if (Schema::hasColumn('pedido_distribucion', 'vehiculoid')) {
            Schema::table('pedido_distribucion', function (Blueprint $table) {
                $table->dropForeign(['vehiculoid']);
                $table->dropColumn('vehiculoid');
            });
        }

        if (Schema::hasColumn('pedido_distribucion', 'transportista_usuarioid')) {
            Schema::table('pedido_distribucion', function (Blueprint $table) {
                $table->dropForeign(['transportista_usuarioid']);
                $table->dropColumn('transportista_usuarioid');
            });
        }
    }
};
