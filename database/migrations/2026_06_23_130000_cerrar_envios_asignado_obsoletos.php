<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return;
        }

        $fechaRecepcion = DB::connection()->getDriverName() === 'pgsql'
            ? DB::raw("COALESCE(fecha_asignacion, CURRENT_TIMESTAMP) + INTERVAL '2 days'")
            : DB::raw("datetime(COALESCE(fecha_asignacion, CURRENT_TIMESTAMP), '+2 days')");

        // Asignaciones agrícolas huérfanas: quedaron en «asignado» sin iniciar cierre operativo.
        DB::table('envio_asignacion_multiple')
            ->whereIn('estado', ['asignado', 'asignada'])
            ->whereNull('simulacion_inicio_at')
            ->whereNull('fecha_recepcion_planta')
            ->where('fecha_asignacion', '<', '2026-06-13 00:00:00')
            ->update([
                'estado' => 'recibido_planta',
                'fecha_recepcion_planta' => $fechaRecepcion,
            ]);
    }

    public function down(): void
    {
        // Sin reversión: corrección de datos operativos obsoletos.
    }
};
