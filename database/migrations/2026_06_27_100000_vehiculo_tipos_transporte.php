<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vehiculo_tipo_transporte')) {
            Schema::create('vehiculo_tipo_transporte', function (Blueprint $table) {
                $table->unsignedBigInteger('vehiculoid');
                $table->unsignedBigInteger('tipotransporteid');
                $table->primary(['vehiculoid', 'tipotransporteid'], 'veh_tt_pk');

                $table->foreign('vehiculoid')
                    ->references('vehiculoid')
                    ->on('vehiculo')
                    ->cascadeOnDelete();
                $table->foreign('tipotransporteid')
                    ->references('tipotransporteid')
                    ->on('tipo_transporte')
                    ->cascadeOnDelete();
            });
        }

        $this->alinearTamanoTipoVehiculo();
        $this->heredarTransporteDesdeTipo();
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_tipo_transporte');
    }

    private function alinearTamanoTipoVehiculo(): void
    {
        if (! Schema::hasTable('tipo_vehiculo')) {
            return;
        }

        $mapa = [
            'CAMION_GR' => 'extra_grande',
            'CAMION_PQ' => 'pequeno',
            'CAMIONETA' => 'pequeno',
            'FURGONETA' => 'mediano',
        ];

        foreach ($mapa as $codigo => $tamano) {
            DB::table('tipo_vehiculo')
                ->where('codigo', $codigo)
                ->update(['tamano' => $tamano, 'updated_at' => now()]);
        }
    }

    private function heredarTransporteDesdeTipo(): void
    {
        if (! Schema::hasTable('vehiculo_tipo_transporte') || ! Schema::hasTable('tipo_vehiculo_tipo_transporte')) {
            return;
        }

        $vehiculos = DB::table('vehiculo')->select('vehiculoid', 'tipovehiculoid')->get();

        foreach ($vehiculos as $vehiculo) {
            $yaTiene = DB::table('vehiculo_tipo_transporte')
                ->where('vehiculoid', $vehiculo->vehiculoid)
                ->exists();

            if ($yaTiene || ! $vehiculo->tipovehiculoid) {
                continue;
            }

            $tiposTransporte = DB::table('tipo_vehiculo_tipo_transporte')
                ->where('tipovehiculoid', $vehiculo->tipovehiculoid)
                ->pluck('tipotransporteid');

            foreach ($tiposTransporte as $tipoTransporteId) {
                DB::table('vehiculo_tipo_transporte')->insertOrIgnore([
                    'vehiculoid' => $vehiculo->vehiculoid,
                    'tipotransporteid' => $tipoTransporteId,
                ]);
            }
        }
    }
};
