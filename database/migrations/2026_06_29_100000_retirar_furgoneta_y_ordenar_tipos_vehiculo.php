<?php

use App\Support\TipoVehiculoCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tipo_vehiculo')) {
            return;
        }

        $this->actualizarTamanos();

        $furgonetaId = DB::table('tipo_vehiculo')->where('codigo', 'FURGONETA')->value('tipovehiculoid');
        if (! $furgonetaId) {
            return;
        }

        $camionetaId = DB::table('tipo_vehiculo')->where('codigo', 'CAMIONETA')->value('tipovehiculoid');

        $placasDemo = ['SCZ-MOD-04', 'SCZ-PLT-04', 'SCZ-MAY-04'];
        if (Schema::hasTable('vehiculo')) {
            foreach ($placasDemo as $placa) {
                $this->eliminarVehiculoPorPlaca($placa);
            }

            if ($camionetaId) {
                DB::table('vehiculo')
                    ->where('tipovehiculoid', $furgonetaId)
                    ->update(['tipovehiculoid' => $camionetaId]);
            }
        }

        if (Schema::hasTable('tipo_vehiculo_tipo_transporte')) {
            DB::table('tipo_vehiculo_tipo_transporte')->where('tipovehiculoid', $furgonetaId)->delete();
        }

        DB::table('tipo_vehiculo')->where('tipovehiculoid', $furgonetaId)->delete();
    }

    public function down(): void
    {
        // Sin reversión automática de furgoneta.
    }

    private function actualizarTamanos(): void
    {
        foreach (TipoVehiculoCatalogo::TAMANO_POR_CODIGO as $codigo => $tamano) {
            DB::table('tipo_vehiculo')
                ->where('codigo', $codigo)
                ->update([
                    'tamano' => $tamano,
                    'activo' => true,
                    'updated_at' => now(),
                ]);
        }
    }

    private function eliminarVehiculoPorPlaca(string $placa): void
    {
        $vehiculoId = DB::table('vehiculo')->where('placa', $placa)->value('vehiculoid');
        if (! $vehiculoId) {
            return;
        }

        if (Schema::hasTable('ruta_distribucion') && Schema::hasColumn('ruta_distribucion', 'vehiculoid')) {
            DB::table('ruta_distribucion')->where('vehiculoid', $vehiculoId)->update(['vehiculoid' => null]);
        }

        if (Schema::hasTable('pedido_distribucion') && Schema::hasColumn('pedido_distribucion', 'vehiculoid')) {
            DB::table('pedido_distribucion')->where('vehiculoid', $vehiculoId)->update(['vehiculoid' => null]);
        }

        if (Schema::hasTable('perfil_transportista') && Schema::hasColumn('perfil_transportista', 'vehiculoid')) {
            DB::table('perfil_transportista')->where('vehiculoid', $vehiculoId)->update(['vehiculoid' => null]);
        }

        if (Schema::hasTable('vehiculo_tipo_transporte')) {
            DB::table('vehiculo_tipo_transporte')->where('vehiculoid', $vehiculoId)->delete();
        }

        DB::table('vehiculo')->where('vehiculoid', $vehiculoId)->delete();
    }
};
