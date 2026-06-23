<?php

namespace App\Support;

use App\Models\Actividad;
use App\Models\Usuario;

final class AgricultorLoginNotificacion
{
    /**
     * Actividades de campo pendientes de completar.
     *
     * @return list<array{clave: string, titulo: string, lote: string, url: string}>
     */
    public static function actividadesPendientes(Usuario $user): array
    {
        if (! UsuarioRol::esAgricultorOperativo($user) || UsuarioRol::esJefeAgricultor($user)) {
            return [];
        }

        $items = Actividad::query()
            ->with(['lote', 'tipoActividad'])
            ->where('usuarioid', $user->usuarioid)
            ->whereNull('fechafin')
            ->orderByDesc('fechainicio')
            ->limit(10)
            ->get()
            ->map(fn (Actividad $a) => [
                'clave' => 'actividad:'.(int) $a->actividadid,
                'titulo' => $a->descripcion ?: ($a->tipoActividad?->nombre ?? 'Actividad de campo'),
                'lote' => $a->lote?->nombre ?? '—',
                'url' => route('actividades.show', $a),
            ])
            ->values()
            ->all();

        return LoginNotificacionAlcance::filtrarPendientes(
            LoginNotificacionAlcance::AGRICULTOR,
            (int) $user->usuarioid,
            $items
        );
    }
}
