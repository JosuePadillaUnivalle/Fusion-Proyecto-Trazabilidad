<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Lote;
use App\Models\Usuario;
use App\Models\UsuarioNotificacion;

class NotificacionUsuarioService
{
    public function notificar(
        Usuario|int $destinatario,
        string $tipo,
        string $titulo,
        ?string $mensaje = null,
        ?string $enlace = null,
        ?string $referenciaTipo = null,
        ?int $referenciaId = null,
    ): UsuarioNotificacion {
        $usuarioid = $destinatario instanceof Usuario ? (int) $destinatario->usuarioid : (int) $destinatario;

        return UsuarioNotificacion::create([
            'usuarioid' => $usuarioid,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'enlace' => $enlace,
            'referencia_tipo' => $referenciaTipo,
            'referencia_id' => $referenciaId,
            'creado_en' => now(),
        ]);
    }

    public function loteAsignado(Lote $lote, ?int $anteriorUsuarioid = null): void
    {
        if (! $lote->usuarioid) {
            return;
        }

        if ($anteriorUsuarioid && (int) $anteriorUsuarioid === (int) $lote->usuarioid) {
            return;
        }

        $this->notificar(
            (int) $lote->usuarioid,
            'lote_asignado',
            'Nuevo lote asignado',
            "Se te asignó el lote «{$lote->nombre}».",
            route('lotes.show', $lote),
            'lote',
            (int) $lote->loteid,
        );
    }

    public function actividadAsignada(Actividad $actividad): void
    {
        if (! $actividad->usuarioid) {
            return;
        }

        $loteNombre = $actividad->lote?->nombre ?? 'lote';
        $this->notificar(
            (int) $actividad->usuarioid,
            'actividad_asignada',
            'Nueva actividad asignada',
            "Actividad «{$actividad->descripcion}» en {$loteNombre}.",
            route('actividades.show', $actividad),
            'actividad',
            (int) $actividad->actividadid,
        );
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, UsuarioNotificacion> */
    public function noLeidasPara(int $usuarioid, int $limite = 10)
    {
        return UsuarioNotificacion::query()
            ->where('usuarioid', $usuarioid)
            ->whereNull('leida_at')
            ->orderByDesc('creado_en')
            ->limit($limite)
            ->get();
    }

    public function contarNoLeidas(int $usuarioid): int
    {
        return UsuarioNotificacion::query()
            ->where('usuarioid', $usuarioid)
            ->whereNull('leida_at')
            ->count();
    }

    public function marcarLeida(UsuarioNotificacion $notificacion, int $usuarioid): void
    {
        if ((int) $notificacion->usuarioid !== $usuarioid) {
            return;
        }

        if ($notificacion->leida_at === null) {
            $notificacion->update(['leida_at' => now()]);
        }
    }
}
