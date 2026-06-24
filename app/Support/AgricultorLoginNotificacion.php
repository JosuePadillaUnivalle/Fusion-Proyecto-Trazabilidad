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
            ->map(function (Actividad $a) {
                $tipo = mb_strtolower(trim($a->tipoActividad?->nombre ?? ''));
                $url = str_contains($tipo, 'siembra') && $a->lote
                    ? route('lotes.siembra.completar', $a->lote)
                    : route('actividades.show', $a);

                return [
                    'clave' => 'actividad:'.(int) $a->actividadid,
                    'titulo' => $a->descripcion ?: ($a->tipoActividad?->nombre ?? 'Actividad de campo'),
                    'lote' => $a->lote?->nombre ?? '—',
                    'url' => $url,
                ];
            })
            ->values()
            ->all();

        return LoginNotificacionAlcance::filtrarPendientes(
            LoginNotificacionAlcance::AGRICULTOR,
            (int) $user->usuarioid,
            $items
        );
    }
}
