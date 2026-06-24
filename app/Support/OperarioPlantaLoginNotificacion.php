<?php

namespace App\Support;

use App\Models\AsignacionEtapaPlanta;
use App\Models\Usuario;

final class OperarioPlantaLoginNotificacion
{
    /**
     * Tareas de transformación pendientes del operario de planta.
     *
     * @return list<array{clave: string, proceso: string, maquina: string, lote: string, url: string}>
     */
    public static function nuevasTareasDesdeLogin(Usuario $user, ?\Carbon\Carbon $ultimoLoginPrevio = null): array
    {
        if (! UsuarioRol::esOperarioPlanta($user)) {
            return [];
        }

        $items = AsignacionEtapaPlanta::query()
            ->with(['proceso', 'maquina', 'loteProduccion'])
            ->where('operador_usuarioid', $user->usuarioid)
            ->pendientes()
            ->orderByDesc('creado_en')
            ->limit(10)
            ->get()
            ->map(fn (AsignacionEtapaPlanta $a) => [
                'clave' => 'tarea:'.(int) $a->asignacionetapaplantaid,
                'proceso' => $a->proceso?->nombre ?? 'Transformación',
                'maquina' => $a->maquina?->nombre ?? 'Maquinaria',
                'lote' => $a->loteProduccion?->codigo_lote ?? '—',
                'url' => route('tareas-planta.show', $a),
            ])
            ->values()
            ->all();

        return LoginNotificacionAlcance::filtrarPendientes(
            LoginNotificacionAlcance::OPERARIO_PLANTA,
            (int) $user->usuarioid,
            $items
        );
    }
}
