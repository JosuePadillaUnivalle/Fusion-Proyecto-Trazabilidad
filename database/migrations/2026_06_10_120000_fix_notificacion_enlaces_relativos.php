<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuario_notificacion')) {
            return;
        }

        $rows = DB::table('usuario_notificacion')->whereNotNull('enlace')->get(['id', 'enlace', 'tipo', 'usuarioid', 'leida_at']);

        foreach ($rows as $row) {
            $enlace = (string) $row->enlace;
            if (! str_starts_with($enlace, 'http')) {
                continue;
            }

            $path = parse_url($enlace, PHP_URL_PATH);
            if (! is_string($path) || $path === '') {
                continue;
            }

            $query = parse_url($enlace, PHP_URL_QUERY);
            $nuevo = $path.($query ? '?'.$query : '');

            DB::table('usuario_notificacion')->where('id', $row->id)->update(['enlace' => $nuevo]);
        }

        // Reactivar alerta de tarea pendiente para Carlos si la marcó leída al fallar el enlace.
        $carlosId = DB::table('usuario')->where('email', 'CarlosRueda123@gmail.com')->value('usuarioid');
        if ($carlosId) {
            DB::table('usuario_notificacion')
                ->where('usuarioid', $carlosId)
                ->where('tipo', 'etapa_planta_asignada')
                ->where('referencia_id', 2)
                ->update([
                    'leida_at' => null,
                    'enlace' => '/mis-tareas-planta/2',
                ]);
        }
    }

    public function down(): void
    {
        //
    }
};
