<?php

namespace App\Support;

use App\Models\Actividad;
use App\Models\Lote;
use App\Models\Usuario;

final class AgricultorLoginNotificacion
{
    /**
     * Todas las actividades pendientes (para el panel de inicio del agricultor).
     *
     * @return list<array{clave: string, titulo: string, lote: string, url: string, prioridad: ?string, prioridad_badge: string}>
     */
    public static function todasActividadesPendientes(Usuario $user): array
    {
        return self::consultarActividadesPendientes($user);
    }

    /**
     * Solo actividades aún no vistas en el modal de login (una vez por tarea).
     *
     * @return list<array{clave: string, titulo: string, lote: string, url: string, prioridad: ?string, prioridad_badge: string}>
     */
    public static function actividadesPendientes(Usuario $user): array
    {
        $items = self::consultarActividadesPendientes($user);

        return LoginNotificacionAlcance::filtrarPendientes(
            LoginNotificacionAlcance::AGRICULTOR,
            (int) $user->usuarioid,
            $items
        );
    }

    /**
     * @return list<array{clave: string, titulo: string, lote: string, url: string, prioridad: ?string, prioridad_badge: string}>
     */
    private static function consultarActividadesPendientes(Usuario $user): array
    {
        if (! UsuarioRol::esAgricultorOperativo($user) || UsuarioRol::esJefeAgricultor($user)) {
            return [];
        }

        $actividades = Actividad::query()
            ->with(['lote', 'tipoActividad', 'prioridad'])
            ->where('usuarioid', $user->usuarioid)
            ->whereNull('fechafin')
            ->orderByDesc('fechainicio')
            ->limit(20)
            ->get();

        $filas = $actividades
            ->map(function (Actividad $a) {
                $fila = self::filaActividad($a);
                $fila['orden'] = $a->fechainicio?->getTimestamp() ?? 0;
                $fila['loteid'] = (int) ($a->loteid ?? 0);

                return $fila;
            });

        $loteIdsConActividad = $filas
            ->pluck('loteid')
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $trazabilidad = app(LoteTrazabilidadService::class);
        $planificadoId = EstadoLoteCatalogo::idPorSlug('planificado');

        $lotesPlanificados = Lote::query()
            ->where('usuarioid', $user->usuarioid)
            ->when(
                $planificadoId,
                fn ($q) => $q->where('estadolotetipoid', $planificadoId),
                fn ($q) => $q->whereHas(
                    'estadoTipo',
                    fn ($e) => $e->whereRaw('LOWER(TRIM(nombre)) = ?', ['planificado'])
                        ->orWhereRaw('LOWER(TRIM(nombre)) IN (?, ?)', ['planificación', 'planificacion'])
                )
            )
            ->orderByDesc('fechacreacion')
            ->limit(20)
            ->get()
            ->filter(fn (Lote $lote) => ! in_array((int) $lote->loteid, $loteIdsConActividad, true))
            ->filter(fn (Lote $lote) => ! $trazabilidad->siembraCompletada($lote))
            ->map(function (Lote $lote) {
                $fila = self::filaLotePlanificado($lote);
                $fila['orden'] = $lote->fechacreacion?->getTimestamp() ?? 0;
                $fila['loteid'] = (int) $lote->loteid;

                return $fila;
            });

        return $filas
            ->concat($lotesPlanificados)
            ->sortByDesc('orden')
            ->take(20)
            ->map(fn (array $row) => [
                'clave' => $row['clave'],
                'titulo' => $row['titulo'],
                'lote' => $row['lote'],
                'url' => $row['url'],
                'prioridad' => $row['prioridad'] ?? null,
                'prioridad_badge' => $row['prioridad_badge'] ?? 'secondary',
            ])
            ->values()
            ->all();
    }

    /** @return array{clave: string, titulo: string, lote: string, url: string, prioridad: ?string, prioridad_badge: string} */
    private static function filaLotePlanificado(Lote $lote): array
    {
        return [
            'clave' => 'lote-planificado:'.(int) $lote->loteid,
            'titulo' => 'Siembra',
            'lote' => $lote->nombre ?? '—',
            'url' => route('lotes.siembra.completar', $lote),
            'prioridad' => 'alta',
            'prioridad_badge' => PrioridadCatalogo::badgeClase('alta'),
        ];
    }

    /** @return array{clave: string, titulo: string, lote: string, url: string, prioridad: ?string, prioridad_badge: string} */
    private static function filaActividad(Actividad $a): array
    {
        $tipo = mb_strtolower(trim($a->tipoActividad?->nombre ?? ''));
        $url = str_contains($tipo, 'siembra') && $a->lote
            ? route('lotes.siembra.completar', $a->lote)
            : route('actividades.show', $a);

        return [
            'clave' => 'actividad:'.(int) $a->actividadid,
            'titulo' => $a->descripcion ?: ($a->tipoActividad?->nombre ?? 'Actividad de campo'),
            'lote' => $a->lote?->nombre ?? '—',
            'url' => $url,
            'prioridad' => $a->prioridad?->nombre,
            'prioridad_badge' => PrioridadCatalogo::badgeClase($a->prioridad?->nombre),
        ];
    }
}
