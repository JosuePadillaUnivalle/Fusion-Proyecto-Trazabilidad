<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Complemento: tablas descritas en "Tablas Desplegadas" sin equivalente previo en Fusion.
 * Los nombres propios de Fusion (usuario, pedido, envio_asignacion_multiple, etc.) no se renombran.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('tipo_vehiculo')) {
            Schema::create('tipo_vehiculo', function (Blueprint $table) {
                $table->id('tipovehiculoid');
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->decimal('capacidad_kg', 10, 2)->nullable();
                $table->decimal('capacidad_m3', 10, 2)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
            $now = now();
            foreach (
                [
                    ['nombre' => 'Motocicleta', 'descripcion' => null, 'capacidad_kg' => 50, 'capacidad_m3' => 0.5],
                    ['nombre' => 'Camioneta', 'descripcion' => null, 'capacidad_kg' => 1000, 'capacidad_m3' => 3],
                    ['nombre' => 'Furgoneta', 'descripcion' => null, 'capacidad_kg' => 2000, 'capacidad_m3' => 8],
                    ['nombre' => 'Camión pequeño', 'descripcion' => null, 'capacidad_kg' => 3500, 'capacidad_m3' => 15],
                    ['nombre' => 'Camión grande', 'descripcion' => null, 'capacidad_kg' => 10000, 'capacidad_m3' => 40],
                ] as $row
            ) {
                DB::table('tipo_vehiculo')->insert(array_merge($row, [
                    'activo' => true, 'created_at' => $now, 'updated_at' => $now,
                ]));
            }
        }

        if (! Schema::hasTable('estado_vehiculo')) {
            Schema::create('estado_vehiculo', function (Blueprint $table) {
                $table->id('estadovehiculoid');
                $table->string('nombre', 50)->unique();
            });
            foreach (['operativo', 'mantenimiento', 'baja'] as $n) {
                DB::table('estado_vehiculo')->insert(['nombre' => $n]);
            }
        }

        if (! Schema::hasTable('estado_transportista')) {
            Schema::create('estado_transportista', function (Blueprint $table) {
                $table->id('estadotransportistaid');
                $table->string('nombre', 50)->unique();
            });
            foreach (['disponible', 'en_ruta', 'inactivo'] as $n) {
                DB::table('estado_transportista')->insert(['nombre' => $n]);
            }
        }

        if (! Schema::hasTable('estado_envio_catalogo')) {
            Schema::create('estado_envio_catalogo', function (Blueprint $table) {
                $table->id('estadoenviocatalogoid');
                $table->string('nombre', 50)->unique();
                $table->text('descripcion')->nullable();
                $table->string('color', 20)->nullable();
                $table->unsignedInteger('orden')->nullable();
                $table->timestamps();
            });
            $now = now();
            foreach (
                [
                    ['nombre' => 'pendiente', 'descripcion' => 'Pendiente de asignación', 'color' => '#ffc107', 'orden' => 1],
                    ['nombre' => 'asignado', 'descripcion' => 'Asignado a transportista', 'color' => '#17a2b8', 'orden' => 2],
                    ['nombre' => 'en_transito', 'descripcion' => 'En camino', 'color' => '#007bff', 'orden' => 3],
                    ['nombre' => 'entregado', 'descripcion' => 'Entregado', 'color' => '#28a745', 'orden' => 4],
                    ['nombre' => 'cancelado', 'descripcion' => 'Cancelado', 'color' => '#dc3545', 'orden' => 5],
                ] as $row
            ) {
                DB::table('estado_envio_catalogo')->insert(array_merge($row, ['created_at' => $now, 'updated_at' => $now]));
            }
        }

        if (! Schema::hasTable('estado_asignacion_multiple_catalogo')) {
            Schema::create('estado_asignacion_multiple_catalogo', function (Blueprint $table) {
                $table->id('estadoasignacioncatalogoid');
                $table->string('nombre', 50)->unique();
            });
            foreach (['creada', 'asignada', 'en_transito', 'entregada', 'cancelada'] as $n) {
                DB::table('estado_asignacion_multiple_catalogo')->insert(['nombre' => $n]);
            }
        }

        if (! Schema::hasTable('tipo_transporte')) {
            Schema::create('tipo_transporte', function (Blueprint $table) {
                $table->id('tipotransporteid');
                $table->string('nombre', 50)->unique();
                $table->string('descripcion', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('motivo_cancelacion_envio')) {
            Schema::create('motivo_cancelacion_envio', function (Blueprint $table) {
                $table->id('motivocancelacionid');
                $table->string('codigo', 50)->unique();
                $table->string('titulo', 100);
                $table->string('descripcion', 255)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('catalogo_carga')) {
            Schema::create('catalogo_carga', function (Blueprint $table) {
                $table->id('catalogocargaid');
                $table->string('tipo', 50);
                $table->string('variedad', 50);
                $table->string('empaque', 50);
                $table->string('descripcion', 150)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('condicion_transporte')) {
            Schema::create('condicion_transporte', function (Blueprint $table) {
                $table->id('condiciontransporteid');
                $table->string('codigo', 50)->unique();
                $table->string('titulo', 100);
                $table->string('descripcion', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tipo_incidente_transporte')) {
            Schema::create('tipo_incidente_transporte', function (Blueprint $table) {
                $table->id('tipoincidentetransporteid');
                $table->string('codigo', 50)->unique();
                $table->string('titulo', 100);
                $table->string('descripcion', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('estado_qrtoken')) {
            Schema::create('estado_qrtoken', function (Blueprint $table) {
                $table->id('estadoqrtokenid');
                $table->string('nombre', 30)->unique();
            });
            foreach (['pendiente', 'validado', 'expirado'] as $n) {
                DB::table('estado_qrtoken')->insert(['nombre' => $n]);
            }
        }

        if (Schema::hasTable('tipo_vehiculo') && Schema::hasTable('estado_vehiculo') && ! Schema::hasTable('vehiculo')) {
            Schema::create('vehiculo', function (Blueprint $table) {
                $table->id('vehiculoid');
                $table->string('placa', 20)->unique();
                $table->string('marca', 100)->nullable();
                $table->string('modelo', 100)->nullable();
                $table->unsignedSmallInteger('anio')->nullable();
                $table->unsignedBigInteger('tipovehiculoid')->nullable();
                $table->unsignedBigInteger('estadovehiculoid')->nullable();
                $table->string('color', 50)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->foreign('tipovehiculoid')->references('tipovehiculoid')->on('tipo_vehiculo')->nullOnDelete();
                $table->foreign('estadovehiculoid')->references('estadovehiculoid')->on('estado_vehiculo')->nullOnDelete();
            });
        }

        if (Schema::hasTable('usuario') && Schema::hasTable('estado_transportista') && ! Schema::hasTable('perfil_transportista')) {
            Schema::create('perfil_transportista', function (Blueprint $table) {
                $table->id('perfiltransportistaid');
                $table->unsignedBigInteger('usuarioid')->unique();
                $table->unsignedBigInteger('estadotransportistaid')->nullable();
                $table->unsignedBigInteger('vehiculoid')->nullable();
                $table->string('licencia', 50)->nullable();
                $table->string('tipo_licencia', 20)->nullable();
                $table->date('fecha_vencimiento_licencia')->nullable();
                $table->boolean('disponible')->default(true);
                $table->timestamps();
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario')->cascadeOnDelete();
                $table->foreign('estadotransportistaid')->references('estadotransportistaid')->on('estado_transportista')->nullOnDelete();
                $table->foreign('vehiculoid')->references('vehiculoid')->on('vehiculo')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('recogida_entrega')) {
            Schema::create('recogida_entrega', function (Blueprint $table) {
                $table->id('recogidaentregaid');
                $table->date('fecha_recogida');
                $table->time('hora_recogida');
                $table->time('hora_entrega');
                $table->string('instrucciones_recogida', 255)->nullable();
                $table->string('instrucciones_entrega', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('direccion_geo_envio')) {
            Schema::create('direccion_geo_envio', function (Blueprint $table) {
                $table->id('direcciongeoenvioid');
                $table->unsignedBigInteger('usuarioid')->nullable();
                $table->string('nombreorigen', 200)->nullable();
                $table->double('origen_lng')->nullable();
                $table->double('origen_lat')->nullable();
                $table->string('nombredestino', 200)->nullable();
                $table->double('destino_lng')->nullable();
                $table->double('destino_lat')->nullable();
                $table->text('rutageojson')->nullable();
                $table->timestamps();
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('direccion_geo_segmento')) {
            Schema::create('direccion_geo_segmento', function (Blueprint $table) {
                $table->id('direcciongeosegmentoid');
                $table->unsignedBigInteger('direcciongeoenvioid');
                $table->text('segmentogeojson');
                $table->timestamps();
                $table->foreign('direcciongeoenvioid')->references('direcciongeoenvioid')->on('direccion_geo_envio')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('catalogo_carga') && Schema::hasTable('unidadmedida') && ! Schema::hasTable('carga_envio')) {
            Schema::create('carga_envio', function (Blueprint $table) {
                $table->id('cargaenvioid');
                $table->unsignedBigInteger('catalogocargaid');
                $table->unsignedInteger('cantidad');
                $table->decimal('peso', 10, 2);
                $table->unsignedBigInteger('unidadmedidaid')->nullable();
                $table->timestamps();
                $table->foreign('catalogocargaid')->references('catalogocargaid')->on('catalogo_carga');
                $table->foreign('unidadmedidaid')->references('unidadmedidaid')->on('unidadmedida')->nullOnDelete();
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple') && Schema::hasTable('estado_envio_catalogo') && ! Schema::hasTable('historial_estado_envio')) {
            Schema::create('historial_estado_envio', function (Blueprint $table) {
                $table->id('historialestadoenvioid');
                $table->unsignedBigInteger('envioasignacionmultipleid')->nullable();
                $table->string('externo_envio_id', 64)->nullable()->index();
                $table->unsignedBigInteger('estadoenviocatalogoid');
                $table->timestamp('fecha')->useCurrent();
                $table->timestamps();
                $table->foreign('envioasignacionmultipleid')->references('envioasignacionmultipleid')->on('envio_asignacion_multiple')->cascadeOnDelete();
                $table->foreign('estadoenviocatalogoid')->references('estadoenviocatalogoid')->on('estado_envio_catalogo');
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple') && Schema::hasTable('carga_envio') && ! Schema::hasTable('asignacion_carga')) {
            Schema::create('asignacion_carga', function (Blueprint $table) {
                $table->unsignedBigInteger('envioasignacionmultipleid');
                $table->unsignedBigInteger('cargaenvioid');
                $table->primary(['envioasignacionmultipleid', 'cargaenvioid']);
                $table->foreign('envioasignacionmultipleid')->references('envioasignacionmultipleid')->on('envio_asignacion_multiple')->cascadeOnDelete();
                $table->foreign('cargaenvioid')->references('cargaenvioid')->on('carga_envio')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('checklist_condicion_logistica') && Schema::hasTable('condicion_transporte') && ! Schema::hasTable('checklist_condicion_logistica_detalle')) {
            Schema::create('checklist_condicion_logistica_detalle', function (Blueprint $table) {
                $table->id('checklistcondiciondetalleid');
                $table->unsignedBigInteger('checklistcondicionid');
                $table->unsignedBigInteger('condiciontransporteid');
                $table->boolean('valor');
                $table->string('comentario', 255)->nullable();
                $table->timestamps();
                $table->foreign('checklistcondicionid')->references('checklistcondicionid')->on('checklist_condicion_logistica')->cascadeOnDelete();
                $table->foreign('condiciontransporteid')->references('condiciontransporteid')->on('condicion_transporte');
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple') && ! Schema::hasTable('checklist_incidente_envio')) {
            Schema::create('checklist_incidente_envio', function (Blueprint $table) {
                $table->id('checklistincidenteenvioid');
                $table->unsignedBigInteger('envioasignacionmultipleid')->unique();
                $table->timestamp('fecha')->useCurrent();
                $table->string('observaciones', 255)->nullable();
                $table->timestamps();
                $table->foreign('envioasignacionmultipleid')->references('envioasignacionmultipleid')->on('envio_asignacion_multiple')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('checklist_incidente_envio') && Schema::hasTable('tipo_incidente_transporte') && ! Schema::hasTable('checklist_incidente_envio_detalle')) {
            Schema::create('checklist_incidente_envio_detalle', function (Blueprint $table) {
                $table->id('checklistincidentedetalleid');
                $table->unsignedBigInteger('checklistincidenteenvioid');
                $table->unsignedBigInteger('tipoincidentetransporteid');
                $table->boolean('ocurrio');
                $table->string('descripcion', 255)->nullable();
                $table->timestamps();
                $table->foreign('checklistincidenteenvioid')->references('checklistincidenteenvioid')->on('checklist_incidente_envio')->cascadeOnDelete();
                $table->foreign('tipoincidentetransporteid')->references('tipoincidentetransporteid')->on('tipo_incidente_transporte');
            });
        }

        if (Schema::hasTable('pedido') && Schema::hasTable('perfil_transportista') && ! Schema::hasTable('calificacion_envio')) {
            Schema::create('calificacion_envio', function (Blueprint $table) {
                $table->id('calificacionenvioid');
                $table->unsignedBigInteger('pedidoid');
                $table->unsignedBigInteger('usuarioid');
                $table->unsignedBigInteger('perfiltransportistaid');
                $table->unsignedTinyInteger('puntuacion');
                $table->string('comentario', 500)->nullable();
                $table->timestamp('fecha')->useCurrent();
                $table->timestamps();
                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->cascadeOnDelete();
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('perfiltransportistaid')->references('perfiltransportistaid')->on('perfil_transportista');
            });
        }

        if (Schema::hasTable('usuario') && ! Schema::hasTable('notificacion_usuario')) {
            Schema::create('notificacion_usuario', function (Blueprint $table) {
                $table->id('notificacionusuarioid');
                $table->unsignedBigInteger('usuarioid');
                $table->string('tipo', 50);
                $table->string('titulo', 150);
                $table->string('mensaje', 500);
                $table->boolean('leida')->default(false);
                $table->unsignedBigInteger('pedidoid')->nullable();
                $table->timestamp('fecha')->useCurrent();
                $table->timestamps();
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario')->cascadeOnDelete();
                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->nullOnDelete();
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple') && ! Schema::hasTable('firma_recepcion_envio')) {
            Schema::create('firma_recepcion_envio', function (Blueprint $table) {
                $table->id('firmarecepcionid');
                $table->unsignedBigInteger('envioasignacionmultipleid')->unique();
                $table->text('imagenfirma');
                $table->timestamp('fechafirma')->useCurrent();
                $table->timestamps();
                $table->foreign('envioasignacionmultipleid')->references('envioasignacionmultipleid')->on('envio_asignacion_multiple')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple') && ! Schema::hasTable('firma_transportista_envio')) {
            Schema::create('firma_transportista_envio', function (Blueprint $table) {
                $table->id('firmatransportistaid');
                $table->unsignedBigInteger('envioasignacionmultipleid')->unique();
                $table->text('imagenfirma');
                $table->timestamp('fechafirma')->useCurrent();
                $table->timestamps();
                $table->foreign('envioasignacionmultipleid')->references('envioasignacionmultipleid')->on('envio_asignacion_multiple')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('envio_asignacion_multiple') && Schema::hasTable('estado_qrtoken') && ! Schema::hasTable('qrtoken_asignacion')) {
            Schema::create('qrtoken_asignacion', function (Blueprint $table) {
                $table->id('qrtokenasignacionid');
                $table->unsignedBigInteger('envioasignacionmultipleid')->unique();
                $table->unsignedBigInteger('estadoqrtokenid');
                $table->string('token', 500)->unique();
                $table->text('imagenqr');
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->timestamp('fecha_expiracion');
                $table->timestamps();
                $table->foreign('envioasignacionmultipleid')->references('envioasignacionmultipleid')->on('envio_asignacion_multiple')->cascadeOnDelete();
                $table->foreign('estadoqrtokenid')->references('estadoqrtokenid')->on('estado_qrtoken');
            });
        }

        if (Schema::hasTable('pedido') && ! Schema::hasTable('lote_produccion_pedido')) {
            Schema::create('lote_produccion_pedido', function (Blueprint $table) {
                $table->id('loteproduccionpedidoid');
                $table->unsignedBigInteger('pedidoid');
                $table->string('codigo_lote', 50);
                $table->string('nombre', 100)->default('Lote sin nombre');
                $table->date('fecha_creacion')->nullable();
                $table->timestamp('hora_inicio')->nullable();
                $table->timestamp('hora_fin')->nullable();
                $table->decimal('cantidad_objetivo', 15, 4)->nullable();
                $table->decimal('cantidad_producida', 15, 4)->nullable();
                $table->string('observaciones', 500)->nullable();
                $table->timestamps();
                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('lote_produccion_pedido') && Schema::hasTable('materia_prima_lote') && ! Schema::hasTable('lote_produccion_materia_prima')) {
            Schema::create('lote_produccion_materia_prima', function (Blueprint $table) {
                $table->id('loteproduccionmateriaid');
                $table->unsignedBigInteger('loteproduccionpedidoid');
                $table->unsignedBigInteger('materiaprimaloteid');
                $table->decimal('cantidad_planificada', 15, 4);
                $table->decimal('cantidad_usada', 15, 4)->nullable();
                $table->timestamps();
                $table->foreign('loteproduccionpedidoid')->references('loteproduccionpedidoid')->on('lote_produccion_pedido')->cascadeOnDelete();
                $table->foreign('materiaprimaloteid')->references('materiaprimaloteid')->on('materia_prima_lote');
            });
        }

        if (Schema::hasTable('pedido_destino') && Schema::hasTable('detallepedido') && ! Schema::hasTable('producto_destino_pedido')) {
            Schema::create('producto_destino_pedido', function (Blueprint $table) {
                $table->id('productodestinopedidoid');
                $table->unsignedBigInteger('pedidodestinoid');
                $table->unsignedBigInteger('detallepedidoid');
                $table->decimal('cantidad', 15, 4);
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->foreign('pedidodestinoid')->references('pedidodestinoid')->on('pedido_destino')->cascadeOnDelete();
                $table->foreign('detallepedidoid')->references('detallepedidoid')->on('detallepedido')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('lote_produccion_pedido') && Schema::hasTable('usuario') && ! Schema::hasTable('evaluacion_final_lote_produccion')) {
            Schema::create('evaluacion_final_lote_produccion', function (Blueprint $table) {
                $table->id('evaluacionfinalloteid');
                $table->unsignedBigInteger('loteproduccionpedidoid');
                $table->unsignedBigInteger('inspector_usuarioid')->nullable();
                $table->string('razon', 500)->nullable();
                $table->string('observaciones', 500)->nullable();
                $table->timestamp('fecha_evaluacion')->useCurrent();
                $table->timestamps();
                $table->foreign('loteproduccionpedidoid')->references('loteproduccionpedidoid')->on('lote_produccion_pedido')->cascadeOnDelete();
                $table->foreign('inspector_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (Schema::hasTable('pedido') && ! Schema::hasTable('solicitud_material_pedido')) {
            Schema::create('solicitud_material_pedido', function (Blueprint $table) {
                $table->id('solicitudmaterialid');
                $table->unsignedBigInteger('pedidoid');
                $table->string('numero_solicitud', 50);
                $table->date('fecha_solicitud')->nullable();
                $table->date('fecha_requerida');
                $table->text('observaciones')->nullable();
                $table->string('direccion', 500)->nullable();
                $table->decimal('latitud', 10, 8)->nullable();
                $table->decimal('longitud', 11, 8)->nullable();
                $table->timestamps();
                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('solicitud_material_pedido') && Schema::hasTable('materia_prima_base') && ! Schema::hasTable('detalle_solicitud_material')) {
            Schema::create('detalle_solicitud_material', function (Blueprint $table) {
                $table->id('detallesolicitudmaterialid');
                $table->unsignedBigInteger('solicitudmaterialid');
                $table->unsignedBigInteger('materiaprimabaseid');
                $table->decimal('cantidad_solicitada', 15, 4);
                $table->decimal('cantidad_aprobada', 15, 4)->nullable();
                $table->timestamps();
                $table->foreign('solicitudmaterialid')->references('solicitudmaterialid')->on('solicitud_material_pedido')->cascadeOnDelete();
                $table->foreign('materiaprimabaseid')->references('materiaprimabaseid')->on('materia_prima_base');
            });
        }

        if (
            Schema::hasTable('solicitud_material_pedido')
            && Schema::hasTable('actor_abastecimiento')
            && ! Schema::hasTable('respuesta_proveedor_solicitud')
        ) {
            Schema::create('respuesta_proveedor_solicitud', function (Blueprint $table) {
                $table->id('respuestaproveedorid');
                $table->unsignedBigInteger('solicitudmaterialid');
                $table->unsignedBigInteger('proveedor_actorid');
                $table->timestamp('fecha_respuesta')->useCurrent();
                $table->decimal('cantidad_confirmada', 15, 4)->nullable();
                $table->date('fecha_entrega')->nullable();
                $table->text('observaciones')->nullable();
                $table->decimal('precio', 15, 2)->nullable();
                $table->timestamps();
                $table->foreign('solicitudmaterialid')->references('solicitudmaterialid')->on('solicitud_material_pedido')->cascadeOnDelete();
                $table->foreign('proveedor_actorid')->references('actorid')->on('actor_abastecimiento');
            });
        }

        if (
            Schema::hasTable('almacen')
            && Schema::hasTable('producto_distribucion')
            && Schema::hasTable('envio_asignacion_multiple')
            && ! Schema::hasTable('inventario_almacen_envio')
        ) {
            Schema::create('inventario_almacen_envio', function (Blueprint $table) {
                $table->id('inventarioalmacenenvioid');
                $table->unsignedBigInteger('almacenid');
                $table->unsignedBigInteger('productodistribucionid');
                $table->unsignedBigInteger('envioasignacionmultipleid')->nullable();
                $table->string('externo_envio_id', 64)->nullable()->index();
                $table->decimal('cantidad', 10, 2);
                $table->decimal('peso_total', 10, 3)->nullable();
                $table->timestamp('fecha_ingreso')->useCurrent();
                $table->string('estado', 50)->default('disponible');
                $table->timestamps();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->cascadeOnDelete();
                $table->foreign('productodistribucionid')->references('productodistribucionid')->on('producto_distribucion')->cascadeOnDelete();
                $table->foreign('envioasignacionmultipleid')->references('envioasignacionmultipleid')->on('envio_asignacion_multiple')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('distribucion_tipo_ingreso')) {
            Schema::create('distribucion_tipo_ingreso', function (Blueprint $table) {
                $table->id('distribuciontipoingresoid');
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('distribucion_tipo_salida')) {
            Schema::create('distribucion_tipo_salida', function (Blueprint $table) {
                $table->id('distribuciontiposalidaid');
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (
            Schema::hasTable('almacen')
            && Schema::hasTable('usuario')
            && Schema::hasTable('distribucion_tipo_ingreso')
            && Schema::hasTable('actor_abastecimiento')
            && Schema::hasTable('vehiculo')
            && ! Schema::hasTable('distribucion_ingreso')
        ) {
            Schema::create('distribucion_ingreso', function (Blueprint $table) {
                $table->id('distribucioningresoid');
                $table->string('codigo_comprobante', 80)->nullable();
                $table->date('fecha')->nullable();
                $table->integer('estado')->default(1);
                $table->unsignedBigInteger('almacenid');
                $table->unsignedBigInteger('operador_usuarioid');
                $table->unsignedBigInteger('transportista_usuarioid');
                $table->unsignedBigInteger('proveedor_actorid')->nullable();
                $table->unsignedBigInteger('pedidoid')->nullable();
                $table->unsignedBigInteger('distribuciontipoingresoid');
                $table->unsignedBigInteger('vehiculoid')->nullable();
                $table->unsignedBigInteger('administrador_usuarioid');
                $table->timestamps();
                $table->foreign('almacenid')->references('almacenid')->on('almacen');
                $table->foreign('operador_usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('transportista_usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('proveedor_actorid')->references('actorid')->on('actor_abastecimiento')->nullOnDelete();
                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->nullOnDelete();
                $table->foreign('distribuciontipoingresoid')->references('distribuciontipoingresoid')->on('distribucion_tipo_ingreso');
                $table->foreign('vehiculoid')->references('vehiculoid')->on('vehiculo')->nullOnDelete();
                $table->foreign('administrador_usuarioid')->references('usuarioid')->on('usuario');
            });
        }

        if (Schema::hasTable('distribucion_ingreso') && Schema::hasTable('producto_distribucion') && ! Schema::hasTable('distribucion_detalle_ingreso')) {
            Schema::create('distribucion_detalle_ingreso', function (Blueprint $table) {
                $table->id('distribuciondetalleingresoid');
                $table->unsignedBigInteger('distribucioningresoid');
                $table->unsignedBigInteger('productodistribucionid');
                $table->decimal('cant_ingreso', 12, 3);
                $table->decimal('precio', 12, 2);
                $table->timestamps();
                $table->foreign('distribucioningresoid')->references('distribucioningresoid')->on('distribucion_ingreso')->cascadeOnDelete();
                $table->foreign('productodistribucionid')->references('productodistribucionid')->on('producto_distribucion');
            });
        }

        if (Schema::hasTable('almacen') && Schema::hasTable('usuario') && Schema::hasTable('distribucion_tipo_salida') && Schema::hasTable('vehiculo') && ! Schema::hasTable('distribucion_salida')) {
            Schema::create('distribucion_salida', function (Blueprint $table) {
                $table->id('distribucionsalidaid');
                $table->string('codigo_comprobante', 80)->nullable();
                $table->date('fecha')->nullable();
                $table->integer('estado')->default(1);
                $table->unsignedBigInteger('almacenid');
                $table->unsignedBigInteger('operador_usuarioid');
                $table->unsignedBigInteger('transportista_usuarioid');
                $table->unsignedBigInteger('distribuciontiposalidaid');
                $table->unsignedBigInteger('vehiculoid');
                $table->unsignedBigInteger('administrador_usuarioid');
                $table->timestamps();
                $table->foreign('almacenid')->references('almacenid')->on('almacen');
                $table->foreign('operador_usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('transportista_usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('distribuciontiposalidaid')->references('distribuciontiposalidaid')->on('distribucion_tipo_salida');
                $table->foreign('vehiculoid')->references('vehiculoid')->on('vehiculo');
                $table->foreign('administrador_usuarioid')->references('usuarioid')->on('usuario');
            });
        }

        if (Schema::hasTable('distribucion_salida') && Schema::hasTable('producto_distribucion') && ! Schema::hasTable('distribucion_detalle_salida')) {
            Schema::create('distribucion_detalle_salida', function (Blueprint $table) {
                $table->id('distribuciondetallesalidaid');
                $table->unsignedBigInteger('distribucionsalidaid');
                $table->unsignedBigInteger('productodistribucionid');
                $table->decimal('cant_salida', 12, 3);
                $table->decimal('precio', 12, 2);
                $table->timestamps();
                $table->foreign('distribucionsalidaid')->references('distribucionsalidaid')->on('distribucion_salida')->cascadeOnDelete();
                $table->foreign('productodistribucionid')->references('productodistribucionid')->on('producto_distribucion');
            });
        }

        if (Schema::hasTable('almacen') && Schema::hasTable('usuario') && ! Schema::hasTable('distribucion_pedido_almacen')) {
            Schema::create('distribucion_pedido_almacen', function (Blueprint $table) {
                $table->id('distribucionpedidoid');
                $table->string('codigo_comprobante', 80)->nullable();
                $table->date('fecha')->nullable();
                $table->integer('estado')->default(1);
                $table->unsignedBigInteger('almacenid')->nullable();
                $table->unsignedBigInteger('operador_usuarioid')->nullable();
                $table->unsignedBigInteger('transportista_usuarioid')->nullable();
                $table->unsignedBigInteger('proveedor_actorid')->nullable();
                $table->unsignedBigInteger('administrador_usuarioid')->nullable();
                $table->timestamps();
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->nullOnDelete();
                $table->foreign('operador_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
                $table->foreign('transportista_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
                $table->foreign('proveedor_actorid')->references('actorid')->on('actor_abastecimiento')->nullOnDelete();
                $table->foreign('administrador_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (Schema::hasTable('distribucion_pedido_almacen') && Schema::hasTable('producto_distribucion') && ! Schema::hasTable('distribucion_detalle_pedido_almacen')) {
            Schema::create('distribucion_detalle_pedido_almacen', function (Blueprint $table) {
                $table->id('distribuciondetallepedidoid');
                $table->unsignedBigInteger('distribucionpedidoid');
                $table->unsignedBigInteger('productodistribucionid');
                $table->decimal('cantidad', 12, 3)->default(0);
                $table->timestamps();
                $table->foreign('distribucionpedidoid')->references('distribucionpedidoid')->on('distribucion_pedido_almacen')->cascadeOnDelete();
                $table->foreign('productodistribucionid')->references('productodistribucionid')->on('producto_distribucion');
            });
        }

        if (Schema::hasTable('usuario') && ! Schema::hasTable('operador_planta')) {
            Schema::create('operador_planta', function (Blueprint $table) {
                $table->id('operadorplantaid');
                $table->string('nombre', 100);
                $table->string('apellido', 100);
                $table->string('usuario', 60)->unique();
                $table->string('password_hash', 255);
                $table->string('email', 100)->nullable();
                $table->unsignedBigInteger('usuarioid')->nullable()->unique();
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (Schema::hasTable('lote_produccion_pedido') && ! Schema::hasTable('almacenaje_lote_produccion')) {
            Schema::create('almacenaje_lote_produccion', function (Blueprint $table) {
                $table->id('almacenajeloteid');
                $table->unsignedBigInteger('loteproduccionpedidoid');
                $table->string('ubicacion', 100);
                $table->string('condicion', 100);
                $table->decimal('cantidad', 15, 4);
                $table->string('observaciones', 500)->nullable();
                $table->decimal('latitud_recojo', 10, 8)->nullable();
                $table->decimal('longitud_recojo', 11, 8)->nullable();
                $table->string('direccion_recojo', 500)->nullable();
                $table->string('referencia_recojo', 255)->nullable();
                $table->timestamp('fecha_almacenaje')->useCurrent();
                $table->timestamp('fecha_retiro')->nullable();
                $table->timestamps();
                $table->foreign('loteproduccionpedidoid')->references('loteproduccionpedidoid')->on('lote_produccion_pedido')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        if (! Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('almacenaje_lote_produccion');
        Schema::dropIfExists('operador_planta');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('distribucion_detalle_pedido_almacen');
        Schema::dropIfExists('distribucion_pedido_almacen');
        Schema::dropIfExists('distribucion_detalle_salida');
        Schema::dropIfExists('distribucion_salida');
        Schema::dropIfExists('distribucion_detalle_ingreso');
        Schema::dropIfExists('distribucion_ingreso');
        Schema::dropIfExists('distribucion_tipo_salida');
        Schema::dropIfExists('distribucion_tipo_ingreso');
        Schema::dropIfExists('inventario_almacen_envio');
        Schema::dropIfExists('respuesta_proveedor_solicitud');
        Schema::dropIfExists('detalle_solicitud_material');
        Schema::dropIfExists('solicitud_material_pedido');
        Schema::dropIfExists('evaluacion_final_lote_produccion');
        Schema::dropIfExists('producto_destino_pedido');
        Schema::dropIfExists('lote_produccion_materia_prima');
        Schema::dropIfExists('almacenaje_lote_produccion');
        Schema::dropIfExists('lote_produccion_pedido');
        Schema::dropIfExists('qrtoken_asignacion');
        Schema::dropIfExists('firma_transportista_envio');
        Schema::dropIfExists('firma_recepcion_envio');
        Schema::dropIfExists('notificacion_usuario');
        Schema::dropIfExists('calificacion_envio');
        Schema::dropIfExists('checklist_incidente_envio_detalle');
        Schema::dropIfExists('checklist_incidente_envio');
        Schema::dropIfExists('checklist_condicion_logistica_detalle');
        Schema::dropIfExists('asignacion_carga');
        Schema::dropIfExists('historial_estado_envio');
        Schema::dropIfExists('carga_envio');
        Schema::dropIfExists('direccion_geo_segmento');
        Schema::dropIfExists('direccion_geo_envio');
        Schema::dropIfExists('recogida_entrega');
        Schema::dropIfExists('perfil_transportista');
        Schema::dropIfExists('vehiculo');
        Schema::dropIfExists('estado_qrtoken');
        Schema::dropIfExists('tipo_incidente_transporte');
        Schema::dropIfExists('condicion_transporte');
        Schema::dropIfExists('catalogo_carga');
        Schema::dropIfExists('motivo_cancelacion_envio');
        Schema::dropIfExists('tipo_transporte');
        Schema::dropIfExists('estado_asignacion_multiple_catalogo');
        Schema::dropIfExists('estado_envio_catalogo');
        Schema::dropIfExists('estado_transportista');
        Schema::dropIfExists('estado_vehiculo');
        Schema::dropIfExists('tipo_vehiculo');
    }
};
