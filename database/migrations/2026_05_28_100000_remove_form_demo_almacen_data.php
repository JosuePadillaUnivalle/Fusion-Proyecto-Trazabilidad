<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('almacen_movimiento')) {
            DB::table('almacen_movimiento')
                ->where('referencia', 'like', '%FORM-DEMO%')
                ->update(['referencia' => null]);
        }

        if (Schema::hasTable('distribucion_ingreso')) {
            DB::table('distribucion_ingreso')->where('codigo_comprobante', 'like', '%FORM-DEMO%')->delete();
        }

        if (Schema::hasTable('distribucion_salida')) {
            DB::table('distribucion_salida')->where('codigo_comprobante', 'like', '%FORM-DEMO%')->delete();
        }

        if (Schema::hasTable('envio_asignacion_multiple')) {
            DB::table('envio_asignacion_multiple')->where('externo_envio_id', 'like', '%FORM-DEMO%')->delete();
        }

        if (Schema::hasTable('pedido')) {
            DB::table('pedido')->where('numero_solicitud', 'like', 'PED-FORM-DEMO%')->delete();
        }
    }

    public function down(): void
    {
        // Sin restauración automática.
    }
};
