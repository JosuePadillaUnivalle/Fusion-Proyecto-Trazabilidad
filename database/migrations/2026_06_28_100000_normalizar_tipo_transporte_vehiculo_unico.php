<?php

use App\Support\VehiculoTransporteCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vehiculo_tipo_transporte') || ! Schema::hasTable('tipo_transporte')) {
            return;
        }

        $codigos = DB::table('tipo_transporte')
            ->whereNotNull('codigo')
            ->pluck('codigo', 'tipotransporteid');

        $vehiculos = DB::table('vehiculo_tipo_transporte')
            ->select('vehiculoid')
            ->groupBy('vehiculoid')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('vehiculoid');

        foreach ($vehiculos as $vehiculoId) {
            $filas = DB::table('vehiculo_tipo_transporte')
                ->where('vehiculoid', $vehiculoId)
                ->get()
                ->map(fn ($fila) => (object) [
                    'tipotransporteid' => (int) $fila->tipotransporteid,
                    'codigo' => $codigos[$fila->tipotransporteid] ?? null,
                ]);

            $principal = VehiculoTransporteCatalogo::idPrincipalDesdeColeccion($filas);
            if (! $principal) {
                continue;
            }

            DB::table('vehiculo_tipo_transporte')->where('vehiculoid', $vehiculoId)->delete();
            DB::table('vehiculo_tipo_transporte')->insert([
                'vehiculoid' => $vehiculoId,
                'tipotransporteid' => $principal,
            ]);
        }

        if (Schema::hasTable('tipo_vehiculo_tipo_transporte')) {
            $tiposVehiculo = DB::table('tipo_vehiculo_tipo_transporte')
                ->select('tipovehiculoid')
                ->groupBy('tipovehiculoid')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('tipovehiculoid');

            foreach ($tiposVehiculo as $tipoVehiculoId) {
                $filas = DB::table('tipo_vehiculo_tipo_transporte')
                    ->where('tipovehiculoid', $tipoVehiculoId)
                    ->get()
                    ->map(fn ($fila) => (object) [
                        'tipotransporteid' => (int) $fila->tipotransporteid,
                        'codigo' => $codigos[$fila->tipotransporteid] ?? null,
                    ]);

                $principal = VehiculoTransporteCatalogo::idPrincipalDesdeColeccion($filas);
                if (! $principal) {
                    continue;
                }

                DB::table('tipo_vehiculo_tipo_transporte')->where('tipovehiculoid', $tipoVehiculoId)->delete();
                DB::table('tipo_vehiculo_tipo_transporte')->insert([
                    'tipovehiculoid' => $tipoVehiculoId,
                    'tipotransporteid' => $principal,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Sin reversión: la normalización deja un único tipo por unidad.
    }
};
