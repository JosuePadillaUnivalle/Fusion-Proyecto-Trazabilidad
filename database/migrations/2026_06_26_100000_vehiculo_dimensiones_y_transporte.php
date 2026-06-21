<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tipo_transporte') && ! Schema::hasColumn('tipo_transporte', 'codigo')) {
            Schema::table('tipo_transporte', function (Blueprint $table) {
                $table->string('codigo', 30)->nullable()->after('nombre');
            });

            $mapaCodigos = [
                'Refrigerado' => 'REFRIGERADO',
                'Isotérmico' => 'ISOTERMICO',
                'Multitemperatura' => 'MULTITEMPERATURA',
                'Carga general' => 'CARGA_GENERAL',
            ];

            foreach ($mapaCodigos as $nombre => $codigo) {
                DB::table('tipo_transporte')->where('nombre', $nombre)->update([
                    'codigo' => $codigo,
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('tipo_vehiculo')) {
            Schema::table('tipo_vehiculo', function (Blueprint $table) {
                if (! Schema::hasColumn('tipo_vehiculo', 'largo_m')) {
                    $table->decimal('largo_m', 6, 2)->nullable()->after('capacidad_m3');
                }
                if (! Schema::hasColumn('tipo_vehiculo', 'ancho_m')) {
                    $table->decimal('ancho_m', 6, 2)->nullable()->after('largo_m');
                }
                if (! Schema::hasColumn('tipo_vehiculo', 'alto_m')) {
                    $table->decimal('alto_m', 6, 2)->nullable()->after('ancho_m');
                }
                if (! Schema::hasColumn('tipo_vehiculo', 'factor_volumen_util')) {
                    $table->decimal('factor_volumen_util', 4, 3)->default(0.85)->after('alto_m');
                }
            });
        }

        if (Schema::hasTable('vehiculo')) {
            Schema::table('vehiculo', function (Blueprint $table) {
                if (! Schema::hasColumn('vehiculo', 'largo_m_override')) {
                    $table->decimal('largo_m_override', 6, 2)->nullable()->after('capacidad_m3_override');
                }
                if (! Schema::hasColumn('vehiculo', 'ancho_m_override')) {
                    $table->decimal('ancho_m_override', 6, 2)->nullable()->after('largo_m_override');
                }
                if (! Schema::hasColumn('vehiculo', 'alto_m_override')) {
                    $table->decimal('alto_m_override', 6, 2)->nullable()->after('ancho_m_override');
                }
            });
        }

        if (! Schema::hasTable('tipo_vehiculo_tipo_transporte')) {
            Schema::create('tipo_vehiculo_tipo_transporte', function (Blueprint $table) {
                $table->unsignedBigInteger('tipovehiculoid');
                $table->unsignedBigInteger('tipotransporteid');
                $table->primary(['tipovehiculoid', 'tipotransporteid'], 'tv_tt_pk');

                $table->foreign('tipovehiculoid')
                    ->references('tipovehiculoid')
                    ->on('tipo_vehiculo')
                    ->cascadeOnDelete();
                $table->foreign('tipotransporteid')
                    ->references('tipotransporteid')
                    ->on('tipo_transporte')
                    ->cascadeOnDelete();
            });
        }

        $this->seedDimensionesTiposVehiculo();
        $this->seedTiposTransportePorTipoVehiculo();
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_vehiculo_tipo_transporte');

        if (Schema::hasTable('vehiculo')) {
            Schema::table('vehiculo', function (Blueprint $table) {
                foreach (['alto_m_override', 'ancho_m_override', 'largo_m_override'] as $col) {
                    if (Schema::hasColumn('vehiculo', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('tipo_vehiculo')) {
            Schema::table('tipo_vehiculo', function (Blueprint $table) {
                foreach (['factor_volumen_util', 'alto_m', 'ancho_m', 'largo_m'] as $col) {
                    if (Schema::hasColumn('tipo_vehiculo', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('tipo_transporte') && Schema::hasColumn('tipo_transporte', 'codigo')) {
            Schema::table('tipo_transporte', function (Blueprint $table) {
                $table->dropColumn('codigo');
            });
        }
    }

    private function seedDimensionesTiposVehiculo(): void
    {
        if (! Schema::hasTable('tipo_vehiculo')) {
            return;
        }

        $defaults = [
            'CAMION_GR' => ['largo_m' => 7.20, 'ancho_m' => 2.45, 'alto_m' => 2.30],
            'CAMION_PQ' => ['largo_m' => 5.40, 'ancho_m' => 2.20, 'alto_m' => 2.10],
            'CAMIONETA' => ['largo_m' => 2.20, 'ancho_m' => 1.60, 'alto_m' => 1.40],
            'FURGONETA' => ['largo_m' => 3.00, 'ancho_m' => 1.80, 'alto_m' => 1.90],
        ];

        foreach ($defaults as $codigo => $dims) {
            DB::table('tipo_vehiculo')
                ->where('codigo', $codigo)
                ->update(array_merge($dims, [
                    'factor_volumen_util' => 0.85,
                    'updated_at' => now(),
                ]));
        }
    }

    private function seedTiposTransportePorTipoVehiculo(): void
    {
        if (! Schema::hasTable('tipo_vehiculo_tipo_transporte') || ! Schema::hasTable('tipo_transporte')) {
            return;
        }

        $codigosTransporte = DB::table('tipo_transporte')
            ->whereNotNull('codigo')
            ->pluck('tipotransporteid', 'codigo');

        if ($codigosTransporte->isEmpty()) {
            return;
        }

        $asignaciones = [
            'CAMION_GR' => ['MULTITEMPERATURA'],
            'CAMION_PQ' => ['REFRIGERADO'],
            'CAMIONETA' => ['CARGA_GENERAL'],
            'FURGONETA' => ['ISOTERMICO'],
        ];

        foreach ($asignaciones as $codigoVehiculo => $codigosPermitidos) {
            $tipoVehiculoId = DB::table('tipo_vehiculo')->where('codigo', $codigoVehiculo)->value('tipovehiculoid');
            if (! $tipoVehiculoId) {
                continue;
            }

            DB::table('tipo_vehiculo_tipo_transporte')->where('tipovehiculoid', $tipoVehiculoId)->delete();

            foreach ($codigosPermitidos as $codigoTransporte) {
                $tipoTransporteId = $codigosTransporte[$codigoTransporte] ?? null;
                if (! $tipoTransporteId) {
                    continue;
                }

                DB::table('tipo_vehiculo_tipo_transporte')->insert([
                    'tipovehiculoid' => $tipoVehiculoId,
                    'tipotransporteid' => $tipoTransporteId,
                ]);
            }
        }
    }
};
