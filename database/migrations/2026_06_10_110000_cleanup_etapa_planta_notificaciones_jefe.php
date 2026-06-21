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

        $jefeIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', 'App\\Models\\Usuario')
            ->whereIn('roles.name', ['jefe_planta', 'admin'])
            ->pluck('model_has_roles.model_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        if ($jefeIds !== []) {
            DB::table('usuario_notificacion')
                ->where('tipo', 'etapa_planta_asignada')
                ->whereIn('usuarioid', $jefeIds)
                ->delete();

            DB::table('usuario')
                ->whereIn('usuarioid', $jefeIds)
                ->where('role', 'planta')
                ->update(['role' => 'jefe_planta']);
        }

        $plantaId = DB::table('usuario')
            ->where('email', 'planta@agrofusion.com')
            ->value('usuarioid');

        if (Schema::hasTable('asignacion_etapa_planta') && $jefeIds !== [] && $plantaId) {
            DB::table('asignacion_etapa_planta')
                ->where('estado', 'pendiente')
                ->whereIn('operador_usuarioid', $jefeIds)
                ->update(['operador_usuarioid' => (int) $plantaId]);
        }
    }

    public function down(): void
    {
        // Limpieza de datos incorrectos; no reversible.
    }
};
