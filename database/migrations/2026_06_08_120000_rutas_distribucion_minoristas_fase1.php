<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('perfil_transportista') && ! Schema::hasColumn('perfil_transportista', 'ambito_flota')) {
            Schema::table('perfil_transportista', function (Blueprint $table) {
                $table->string('ambito_flota', 20)->default('agricola')->after('usuarioid');
            });
        }

        if (! Schema::hasTable('ruta_distribucion')) {
            Schema::create('ruta_distribucion', function (Blueprint $table) {
                $table->id('rutadistribucionid');
                $table->string('codigo', 40)->unique();
                $table->string('nombre', 150)->nullable();
                $table->unsignedBigInteger('almacen_planta_origenid');
                $table->unsignedBigInteger('transportista_usuarioid');
                $table->unsignedBigInteger('vehiculoid')->nullable();
                $table->unsignedBigInteger('creado_por_usuarioid')->nullable();
                $table->string('estado', 30)->default('planificada');
                $table->timestamp('fecha_salida')->nullable();
                $table->json('rutageojson')->nullable();
                $table->timestamps();

                $table->foreign('almacen_planta_origenid')->references('almacenid')->on('almacen');
                $table->foreign('transportista_usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('vehiculoid')->references('vehiculoid')->on('vehiculo');
                $table->foreign('creado_por_usuarioid')->references('usuarioid')->on('usuario');
            });
        }

        if (! Schema::hasTable('ruta_distribucion_parada')) {
            Schema::create('ruta_distribucion_parada', function (Blueprint $table) {
                $table->id('rutadistribucionparadaid');
                $table->unsignedBigInteger('rutadistribucionid');
                $table->unsignedSmallInteger('orden')->default(1);
                $table->string('tipo', 20);
                $table->unsignedBigInteger('almacenid')->nullable();
                $table->unsignedBigInteger('puntoventaid')->nullable();
                $table->unsignedBigInteger('pedidodistribucionid')->nullable();
                $table->string('destino', 255)->nullable();
                $table->decimal('latitud', 10, 7)->nullable();
                $table->decimal('longitud', 10, 7)->nullable();
                $table->string('estado', 20)->default('pendiente');
                $table->timestamps();

                $table->foreign('rutadistribucionid')->references('rutadistribucionid')->on('ruta_distribucion')->cascadeOnDelete();
                $table->foreign('almacenid')->references('almacenid')->on('almacen');
                $table->foreign('puntoventaid')->references('puntoventaid')->on('punto_venta');
                $table->foreign('pedidodistribucionid')->references('pedidodistribucionid')->on('pedido_distribucion');
            });
        }

        if (Schema::hasTable('pedido_distribucion') && ! Schema::hasColumn('pedido_distribucion', 'rutadistribucionid')) {
            Schema::table('pedido_distribucion', function (Blueprint $table) {
                $table->unsignedBigInteger('rutadistribucionid')->nullable()->after('almacen_planta_origenid');
                $table->foreign('rutadistribucionid')->references('rutadistribucionid')->on('ruta_distribucion')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pedido_distribucion') && Schema::hasColumn('pedido_distribucion', 'rutadistribucionid')) {
            Schema::table('pedido_distribucion', function (Blueprint $table) {
                $table->dropForeign(['rutadistribucionid']);
                $table->dropColumn('rutadistribucionid');
            });
        }

        Schema::dropIfExists('ruta_distribucion_parada');
        Schema::dropIfExists('ruta_distribucion');

        if (Schema::hasTable('perfil_transportista') && Schema::hasColumn('perfil_transportista', 'ambito_flota')) {
            Schema::table('perfil_transportista', function (Blueprint $table) {
                $table->dropColumn('ambito_flota');
            });
        }
    }
};
