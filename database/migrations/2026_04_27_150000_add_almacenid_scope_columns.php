<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuario') && ! Schema::hasColumn('usuario', 'almacenid')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->unsignedBigInteger('almacenid')->nullable()->index();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->nullOnDelete();
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple') && ! Schema::hasColumn('envio_asignacion_multiple', 'almacenid')) {
            Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                $table->unsignedBigInteger('almacenid')->nullable()->index();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->nullOnDelete();
            });
        }

        if (Schema::hasTable('insumo') && ! Schema::hasColumn('insumo', 'almacenid')) {
            Schema::table('insumo', function (Blueprint $table) {
                $table->unsignedBigInteger('almacenid')->nullable()->index();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->nullOnDelete();
            });
        }

        if (Schema::hasTable('documento_entrega') && ! Schema::hasColumn('documento_entrega', 'almacenid')) {
            Schema::table('documento_entrega', function (Blueprint $table) {
                $table->unsignedBigInteger('almacenid')->nullable()->index();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->nullOnDelete();
            });
        }

        if (Schema::hasTable('incidente_envio') && ! Schema::hasColumn('incidente_envio', 'almacenid')) {
            Schema::table('incidente_envio', function (Blueprint $table) {
                $table->unsignedBigInteger('almacenid')->nullable()->index();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('incidente_envio') && Schema::hasColumn('incidente_envio', 'almacenid')) {
            Schema::table('incidente_envio', function (Blueprint $table) {
                $table->dropForeign(['almacenid']);
                $table->dropColumn('almacenid');
            });
        }

        if (Schema::hasTable('documento_entrega') && Schema::hasColumn('documento_entrega', 'almacenid')) {
            Schema::table('documento_entrega', function (Blueprint $table) {
                $table->dropForeign(['almacenid']);
                $table->dropColumn('almacenid');
            });
        }

        if (Schema::hasTable('insumo') && Schema::hasColumn('insumo', 'almacenid')) {
            Schema::table('insumo', function (Blueprint $table) {
                $table->dropForeign(['almacenid']);
                $table->dropColumn('almacenid');
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple') && Schema::hasColumn('envio_asignacion_multiple', 'almacenid')) {
            Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                $table->dropForeign(['almacenid']);
                $table->dropColumn('almacenid');
            });
        }

        if (Schema::hasTable('usuario') && Schema::hasColumn('usuario', 'almacenid')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropForeign(['almacenid']);
                $table->dropColumn('almacenid');
            });
        }
    }
};
