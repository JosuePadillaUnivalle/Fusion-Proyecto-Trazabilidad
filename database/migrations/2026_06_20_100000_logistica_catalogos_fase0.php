<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tipo_empaque')) {
            Schema::table('tipo_empaque', function (Blueprint $table) {
                if (! Schema::hasColumn('tipo_empaque', 'largo_cm')) {
                    $table->decimal('largo_cm', 8, 2)->nullable()->after('descripcion');
                }
                if (! Schema::hasColumn('tipo_empaque', 'ancho_cm')) {
                    $table->decimal('ancho_cm', 8, 2)->nullable()->after('largo_cm');
                }
                if (! Schema::hasColumn('tipo_empaque', 'alto_cm')) {
                    $table->decimal('alto_cm', 8, 2)->nullable()->after('ancho_cm');
                }
                if (! Schema::hasColumn('tipo_empaque', 'tara_kg')) {
                    $table->decimal('tara_kg', 10, 3)->nullable()->after('alto_cm');
                }
                if (! Schema::hasColumn('tipo_empaque', 'capacidad_unidades')) {
                    $table->unsignedInteger('capacidad_unidades')->nullable()->after('tara_kg');
                }
                if (! Schema::hasColumn('tipo_empaque', 'unidades_por_pallet')) {
                    $table->unsignedInteger('unidades_por_pallet')->nullable()->after('capacidad_unidades');
                }
            });
        }

        if (! Schema::hasTable('catalogo_tamano_conteo')) {
            Schema::create('catalogo_tamano_conteo', function (Blueprint $table) {
                $table->id('catalogotamanoconteoid');
                $table->unsignedBigInteger('insumoid');
                $table->string('nombre', 150);
                $table->unsignedInteger('conteo_por_empaque');
                $table->decimal('peso_promedio_kg', 10, 4);
                $table->unsignedBigInteger('tipoempaqueid')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->foreign('insumoid')->references('insumoid')->on('insumo')->cascadeOnDelete();
                $table->foreign('tipoempaqueid')->references('tipoempaqueid')->on('tipo_empaque')->nullOnDelete();
                $table->unique(['insumoid', 'nombre'], 'catalogo_tamano_conteo_insumo_nombre_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_tamano_conteo');

        if (Schema::hasTable('tipo_empaque')) {
            Schema::table('tipo_empaque', function (Blueprint $table) {
                foreach (['unidades_por_pallet', 'capacidad_unidades', 'tara_kg', 'alto_cm', 'ancho_cm', 'largo_cm'] as $col) {
                    if (Schema::hasColumn('tipo_empaque', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
