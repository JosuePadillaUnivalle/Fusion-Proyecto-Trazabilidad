<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Incorpora estructuras descritas en los archivos de "Tablas Desplegadas"
 * (Plantalogistica, ORG_DB, prueba3/producción-planta, Agronexus ya fusionado,
 * almacenamiento-producto-distribución) donde no existía equivalente en BD.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('datos_planta')) {
            Schema::create('datos_planta', function (Blueprint $table) {
                $table->id('datosplantaid');
                $table->string('nombre', 255);
                $table->string('direccion', 500);
                $table->string('ciudad', 100);
                $table->string('departamento', 100);
                $table->string('pais', 100)->default('Bolivia');
                $table->decimal('latitud', 10, 8);
                $table->decimal('longitud', 11, 8);
                $table->string('telefono', 20)->nullable();
                $table->string('email', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('direccion_logistica')) {
            Schema::create('direccion_logistica', function (Blueprint $table) {
                $table->id('direccionlogisticaid');
                $table->string('nombre', 255);
                $table->string('direccion_completa', 500);
                $table->string('ciudad', 100);
                $table->string('departamento', 100);
                $table->string('pais', 100)->default('Bolivia');
                $table->decimal('latitud', 10, 8)->nullable();
                $table->decimal('longitud', 11, 8)->nullable();
                $table->text('referencia')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tipo_empaque')) {
            Schema::create('tipo_empaque', function (Blueprint $table) {
                $table->id('tipoempaqueid');
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            $now = now();
            foreach (
                [
                    ['nombre' => 'Caja de cartón', 'descripcion' => 'Caja de cartón corrugado'],
                    ['nombre' => 'Bolsa plástica', 'descripcion' => 'Bolsa de polietileno'],
                    ['nombre' => 'Canasta', 'descripcion' => 'Canasta de plástico reutilizable'],
                    ['nombre' => 'Saco', 'descripcion' => 'Saco de yute o polipropileno'],
                    ['nombre' => 'Bandeja', 'descripcion' => 'Bandeja de poliestireno'],
                ] as $row
            ) {
                DB::table('tipo_empaque')->insert(array_merge($row, [
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        if (Schema::hasTable('almacen')) {
            if (! Schema::hasColumn('almacen', 'direccionlogisticaid')) {
                Schema::table('almacen', function (Blueprint $table) {
                    $table->unsignedBigInteger('direccionlogisticaid')->nullable()->after('ubicacion');
                    $table->foreign('direccionlogisticaid')->references('direccionlogisticaid')->on('direccion_logistica')->nullOnDelete();
                });
            }
            if (! Schema::hasColumn('almacen', 'codigo')) {
                Schema::table('almacen', function (Blueprint $table) {
                    $table->string('codigo', 50)->nullable()->unique()->after('nombre');
                });
            }
        }

        if (Schema::hasTable('envio_asignacion_multiple') && ! Schema::hasTable('seguimiento_envio_gps')) {
            Schema::create('seguimiento_envio_gps', function (Blueprint $table) {
                $table->id('seguimientogpsid');
                $table->unsignedBigInteger('envioasignacionmultipleid')->nullable();
                $table->string('externo_envio_id', 64)->nullable()->index();
                $table->decimal('latitud', 10, 8);
                $table->decimal('longitud', 11, 8);
                $table->decimal('velocidad', 8, 2)->nullable();
                $table->timestamp('registrado_en')->useCurrent();

                $table->foreign('envioasignacionmultipleid')
                    ->references('envioasignacionmultipleid')
                    ->on('envio_asignacion_multiple')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple') && ! Schema::hasTable('checklist_condicion_logistica')) {
            Schema::create('checklist_condicion_logistica', function (Blueprint $table) {
                $table->id('checklistcondicionid');
                $table->unsignedBigInteger('envioasignacionmultipleid');
                $table->unsignedBigInteger('almacenid')->nullable();
                $table->unsignedBigInteger('revisado_por_usuarioid')->nullable();
                $table->string('estado_general', 50)->nullable();
                $table->boolean('productos_completos')->nullable();
                $table->boolean('empaque_intacto')->nullable();
                $table->boolean('temperatura_adecuada')->nullable();
                $table->boolean('sin_danos_visibles')->nullable();
                $table->boolean('documentacion_completa')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamp('fecha_revision')->useCurrent();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('envioasignacionmultipleid')
                    ->references('envioasignacionmultipleid')
                    ->on('envio_asignacion_multiple')
                    ->cascadeOnDelete();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->nullOnDelete();
                $table->foreign('revisado_por_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('cliente_comercial')) {
            Schema::create('cliente_comercial', function (Blueprint $table) {
                $table->id('clientecomercialid');
                $table->string('razon_social', 200);
                $table->string('nombre_comercial', 200)->nullable();
                $table->string('nit', 20)->nullable();
                $table->string('direccion', 255)->nullable();
                $table->string('telefono', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('contacto', 100)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('pedido') && ! Schema::hasColumn('pedido', 'clientecomercialid')) {
            Schema::table('pedido', function (Blueprint $table) {
                $table->unsignedBigInteger('clientecomercialid')->nullable()->after('numero_solicitud');
                $table->foreign('clientecomercialid')->references('clientecomercialid')->on('cliente_comercial')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('categoria_materia_prima')) {
            Schema::create('categoria_materia_prima', function (Blueprint $table) {
                $table->id('categoriamateriaprimaid');
                $table->string('codigo', 50);
                $table->string('nombre', 100);
                $table->string('descripcion', 255)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('materia_prima_base')) {
            Schema::create('materia_prima_base', function (Blueprint $table) {
                $table->id('materiaprimabaseid');
                $table->unsignedBigInteger('categoriamateriaprimaid');
                $table->unsignedBigInteger('unidadmedidaid');
                $table->string('codigo', 50);
                $table->string('nombre', 100);
                $table->string('descripcion', 255)->nullable();
                $table->decimal('cantidad_disponible', 15, 4)->default(0);
                $table->decimal('stock_minimo', 15, 4)->default(0);
                $table->decimal('stock_maximo', 15, 4)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->foreign('categoriamateriaprimaid')->references('categoriamateriaprimaid')->on('categoria_materia_prima');
                $table->foreign('unidadmedidaid')->references('unidadmedidaid')->on('unidadmedida');
            });
        }

        if (
            Schema::hasTable('actor_abastecimiento')
            && Schema::hasTable('unidadmedida')
            && ! Schema::hasTable('materia_prima_lote')
        ) {
            Schema::create('materia_prima_lote', function (Blueprint $table) {
                $table->id('materiaprimaloteid');
                $table->unsignedBigInteger('materiaprimabaseid');
                $table->unsignedBigInteger('proveedor_actorid');
                $table->string('lote_proveedor', 100)->nullable();
                $table->string('numero_factura', 100)->nullable();
                $table->date('fecha_recepcion');
                $table->date('fecha_vencimiento')->nullable();
                $table->decimal('cantidad', 15, 4);
                $table->decimal('cantidad_disponible', 15, 4);
                $table->boolean('conformidad_recepcion')->nullable();
                $table->string('observaciones', 500)->nullable();
                $table->timestamps();

                $table->foreign('materiaprimabaseid')->references('materiaprimabaseid')->on('materia_prima_base');
                $table->foreign('proveedor_actorid')->references('actorid')->on('actor_abastecimiento');
            });
        }

        if (! Schema::hasTable('tipo_movimiento_materia')) {
            Schema::create('tipo_movimiento_materia', function (Blueprint $table) {
                $table->id('tipomovimientomateriaid');
                $table->string('codigo', 20);
                $table->string('nombre', 100);
                $table->boolean('afecta_stock')->default(true);
                $table->boolean('es_entrada')->default(false);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            $now = now();
            foreach (
                [
                    ['codigo' => 'REC', 'nombre' => 'Recepción proveedor', 'afecta_stock' => true, 'es_entrada' => true],
                    ['codigo' => 'CONS', 'nombre' => 'Consumo producción', 'afecta_stock' => true, 'es_entrada' => false],
                    ['codigo' => 'AJ+', 'nombre' => 'Ajuste inventario +', 'afecta_stock' => true, 'es_entrada' => true],
                    ['codigo' => 'AJ-', 'nombre' => 'Ajuste inventario -', 'afecta_stock' => true, 'es_entrada' => false],
                    ['codigo' => 'TRF', 'nombre' => 'Transferencia interna', 'afecta_stock' => false, 'es_entrada' => false],
                ] as $row
            ) {
                DB::table('tipo_movimiento_materia')->insert(array_merge($row, [
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        if (! Schema::hasTable('registro_movimiento_materia')) {
            Schema::create('registro_movimiento_materia', function (Blueprint $table) {
                $table->id('registromovimientomateriaid');
                $table->unsignedBigInteger('materiaprimabaseid');
                $table->unsignedBigInteger('tipomovimientomateriaid');
                $table->unsignedBigInteger('usuarioid')->nullable();
                $table->decimal('cantidad', 15, 4);
                $table->decimal('saldo_anterior', 15, 4)->nullable();
                $table->decimal('saldo_nuevo', 15, 4)->nullable();
                $table->string('descripcion', 500)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamp('fecha_movimiento')->useCurrent();

                $table->foreign('materiaprimabaseid')->references('materiaprimabaseid')->on('materia_prima_base');
                $table->foreign('tipomovimientomateriaid')->references('tipomovimientomateriaid')->on('tipo_movimiento_materia');
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('categoria_producto')) {
            Schema::create('categoria_producto', function (Blueprint $table) {
                $table->id('categoriaproductoid');
                $table->string('nombre', 100)->unique();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('producto_distribucion')) {
            Schema::create('producto_distribucion', function (Blueprint $table) {
                $table->id('productodistribucionid');
                $table->string('nombre', 255);
                $table->string('codigo', 50)->unique();
                $table->unsignedBigInteger('categoriaproductoid');
                $table->text('descripcion')->nullable();
                $table->unsignedBigInteger('unidadmedidaid')->nullable();
                $table->decimal('precio_unitario', 15, 2)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->foreign('categoriaproductoid')->references('categoriaproductoid')->on('categoria_producto');
                $table->foreign('unidadmedidaid')->references('unidadmedidaid')->on('unidadmedida')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('almacen_producto')) {
            Schema::create('almacen_producto', function (Blueprint $table) {
                $table->id('almacenproductoid');
                $table->unsignedBigInteger('productodistribucionid');
                $table->unsignedBigInteger('almacenid');
                $table->decimal('stock', 14, 3)->default(0);
                $table->decimal('stock_minimo', 14, 3)->default(0);
                $table->unsignedInteger('en_pedido')->default(0);
                $table->timestamps();

                $table->foreign('productodistribucionid')->references('productodistribucionid')->on('producto_distribucion')->cascadeOnDelete();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->cascadeOnDelete();
                $table->unique(['productodistribucionid', 'almacenid'], 'almacen_producto_unico');
            });
        }

        if (! Schema::hasTable('almacen_usuario')) {
            Schema::create('almacen_usuario', function (Blueprint $table) {
                $table->id('almacenusuarioid');
                $table->unsignedBigInteger('usuarioid');
                $table->unsignedBigInteger('almacenid');
                $table->timestamps();

                $table->foreign('usuarioid')->references('usuarioid')->on('usuario')->cascadeOnDelete();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->cascadeOnDelete();
                $table->unique(['usuarioid', 'almacenid'], 'almacen_usuario_unico');
            });
        }

        if (Schema::hasTable('pedido') && Schema::hasTable('almacen') && ! Schema::hasTable('pedido_destino')) {
            Schema::create('pedido_destino', function (Blueprint $table) {
                $table->id('pedidodestinoid');
                $table->unsignedBigInteger('pedidoid');
                $table->string('direccion', 500);
                $table->string('referencia', 200)->nullable();
                $table->decimal('latitud', 10, 8)->nullable();
                $table->decimal('longitud', 11, 8)->nullable();
                $table->string('nombre_contacto', 200)->nullable();
                $table->string('telefono_contacto', 20)->nullable();
                $table->text('instrucciones_entrega')->nullable();
                $table->unsignedBigInteger('almacen_origenid')->nullable();
                $table->string('almacen_origen_nombre', 255)->nullable();
                $table->unsignedBigInteger('almacen_destinoid')->nullable();
                $table->string('almacen_destino_nombre', 255)->nullable();
                $table->unsignedBigInteger('almacen_externo_psii_id')->nullable();
                $table->timestamps();

                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->cascadeOnDelete();
                $table->foreign('almacen_origenid')->references('almacenid')->on('almacen')->nullOnDelete();
                $table->foreign('almacen_destinoid')->references('almacenid')->on('almacen')->nullOnDelete();
            });
        }

        if (Schema::hasTable('pedido') && Schema::hasTable('pedido_destino') && ! Schema::hasTable('seguimiento_envio_pedido')) {
            Schema::create('seguimiento_envio_pedido', function (Blueprint $table) {
                $table->id('seguimientoenviopedidoid');
                $table->unsignedBigInteger('pedidoid')->nullable();
                $table->unsignedBigInteger('pedidodestinoid')->nullable();
                $table->string('externo_envio_id', 64)->nullable()->index();
                $table->string('codigo_envio', 255)->nullable();
                $table->string('estado', 255)->default('pendiente');
                $table->text('mensaje_error')->nullable();
                $table->json('datos_solicitud')->nullable();
                $table->json('datos_respuesta')->nullable();
                $table->timestamps();

                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->nullOnDelete();
                $table->foreign('pedidodestinoid')->references('pedidodestinoid')->on('pedido_destino')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('variable_estandar')) {
            Schema::create('variable_estandar', function (Blueprint $table) {
                $table->id('variableestandarid');
                $table->string('codigo', 50);
                $table->string('nombre', 100);
                $table->string('unidad', 50)->nullable();
                $table->string('descripcion', 255)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (
            Schema::hasTable('proceso_planta')
            && Schema::hasTable('maquina_planta')
            && ! Schema::hasTable('proceso_maquina_planta')
        ) {
            Schema::create('proceso_maquina_planta', function (Blueprint $table) {
                $table->id('procesomaquinaplantaid');
                $table->unsignedBigInteger('procesoplantaid');
                $table->unsignedBigInteger('maquinaplantaid');
                $table->unsignedInteger('orden_paso');
                $table->string('nombre', 100);
                $table->string('descripcion', 255)->nullable();
                $table->unsignedInteger('tiempo_estimado')->nullable();
                $table->timestamps();

                $table->foreign('procesoplantaid')->references('procesoplantaid')->on('proceso_planta')->cascadeOnDelete();
                $table->foreign('maquinaplantaid')->references('maquinaplantaid')->on('maquina_planta')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('variable_proceso_maquina_planta')) {
            Schema::create('variable_proceso_maquina_planta', function (Blueprint $table) {
                $table->id('variableprocesomaquinaid');
                $table->unsignedBigInteger('procesomaquinaplantaid');
                $table->unsignedBigInteger('variableestandarid');
                $table->decimal('valor_minimo', 10, 2);
                $table->decimal('valor_maximo', 10, 2);
                $table->decimal('valor_objetivo', 10, 2)->nullable();
                $table->boolean('obligatorio')->default(true);
                $table->timestamps();

                $table->foreign('procesomaquinaplantaid')->references('procesomaquinaplantaid')->on('proceso_maquina_planta')->cascadeOnDelete();
                $table->foreign('variableestandarid')->references('variableestandarid')->on('variable_estandar');
            });
        }

        if (! Schema::hasTable('registro_proceso_maquina_planta')) {
            Schema::create('registro_proceso_maquina_planta', function (Blueprint $table) {
                $table->id('registroprocesomaquinaplantaid');
                $table->unsignedBigInteger('procesomaquinaplantaid');
                $table->unsignedBigInteger('loteid');
                $table->unsignedBigInteger('usuarioid');
                $table->text('variables_ingresadas');
                $table->boolean('cumple_estandar');
                $table->string('observaciones', 500)->nullable();
                $table->timestamp('hora_inicio')->nullable();
                $table->timestamp('hora_fin')->nullable();
                $table->timestamp('fecha_registro')->useCurrent();
                $table->timestamps();

                $table->foreign('procesomaquinaplantaid')->references('procesomaquinaplantaid')->on('proceso_maquina_planta')->cascadeOnDelete();
                $table->foreign('loteid')->references('loteid')->on('lote')->cascadeOnDelete();
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_proceso_maquina_planta');
        Schema::dropIfExists('variable_proceso_maquina_planta');
        Schema::dropIfExists('proceso_maquina_planta');
        Schema::dropIfExists('variable_estandar');

        if (Schema::hasTable('seguimiento_envio_pedido') && Schema::hasColumn('seguimiento_envio_pedido', 'pedidodestinoid')) {
            Schema::table('seguimiento_envio_pedido', function (Blueprint $table) {
                $table->dropForeign(['pedidodestinoid']);
                $table->dropColumn('pedidodestinoid');
            });
        }

        Schema::dropIfExists('pedido_destino');
        Schema::dropIfExists('seguimiento_envio_pedido');
        Schema::dropIfExists('almacen_usuario');
        Schema::dropIfExists('almacen_producto');
        Schema::dropIfExists('producto_distribucion');
        Schema::dropIfExists('categoria_producto');
        Schema::dropIfExists('registro_movimiento_materia');
        Schema::dropIfExists('tipo_movimiento_materia');
        Schema::dropIfExists('materia_prima_lote');
        Schema::dropIfExists('materia_prima_base');
        Schema::dropIfExists('categoria_materia_prima');

        if (Schema::hasTable('pedido') && Schema::hasColumn('pedido', 'clientecomercialid')) {
            Schema::table('pedido', function (Blueprint $table) {
                $table->dropForeign(['clientecomercialid']);
                $table->dropColumn('clientecomercialid');
            });
        }

        Schema::dropIfExists('cliente_comercial');
        Schema::dropIfExists('checklist_condicion_logistica');
        Schema::dropIfExists('seguimiento_envio_gps');
        Schema::dropIfExists('tipo_empaque');

        if (Schema::hasTable('almacen')) {
            if (Schema::hasColumn('almacen', 'direccionlogisticaid')) {
                Schema::table('almacen', function (Blueprint $table) {
                    $table->dropForeign(['direccionlogisticaid']);
                    $table->dropColumn('direccionlogisticaid');
                });
            }
            if (Schema::hasColumn('almacen', 'codigo')) {
                Schema::table('almacen', function (Blueprint $table) {
                    $table->dropColumn('codigo');
                });
            }
        }

        Schema::dropIfExists('direccion_logistica');
        Schema::dropIfExists('datos_planta');
    }
};
