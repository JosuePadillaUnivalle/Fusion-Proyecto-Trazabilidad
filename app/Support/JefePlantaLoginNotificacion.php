<?php

namespace App\Support;

use App\Models\RutaDistribucion;
use App\Models\Usuario;
use Carbon\Carbon;

final class JefePlantaLoginNotificacion
{
    /**
     * Traslados planta → mayorista pendientes de aprobación que el jefe aún no vio en modal.
     *
     * @return list<array{clave: string, codigo: string, url: string, destino: string, productos: string}>
     */
    public static function trasladosPendientesDesdeLogin(Usuario $user, ?Carbon $ultimoLoginPrevio): array
    {
        if (! UsuarioRol::esJefePlanta($user) && ! UsuarioRol::esAdminGlobal($user)) {
            return [];
        }

        $items = RutaDistribucion::query()
            ->where('tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA)
            ->where('estado', RutaDistribucionCatalogo::ESTADO_PENDIENTE_APROBACION)
            ->with(['almacenMayoristaDestino', 'detallesTraslado'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->filter(function (RutaDistribucion $ruta) use ($ultimoLoginPrevio) {
                if (! self::esPendienteRelevante($ruta, $ultimoLoginPrevio)) {
                    return false;
                }

                return true;
            })
            ->map(function (RutaDistribucion $ruta) {
                $destino = $ruta->almacenMayoristaDestino?->nombre ?? 'Centro mayorista';
                $n = $ruta->detallesTraslado?->count() ?? 0;
                $productos = $n === 1 ? '1 producto' : $n.' productos';

                return [
                    'clave' => JefePlantaTrasladoNotificacionVista::claveRuta((int) $ruta->rutadistribucionid),
                    'codigo' => $ruta->codigo ?? $ruta->nombre ?? 'Traslado #'.$ruta->rutadistribucionid,
                    'url' => route('logistica.traslados-planta.show', $ruta),
                    'destino' => $destino,
                    'productos' => $productos,
                ];
            })
            ->values()
            ->all();

        return JefePlantaTrasladoNotificacionVista::filtrarPendientes((int) $user->usuarioid, $items);
    }

    private static function esPendienteRelevante(RutaDistribucion $ruta, ?Carbon $ultimoLoginPrevio): bool
    {
        $fecha = $ruta->updated_at ?? $ruta->created_at;
        if ($fecha === null) {
            return true;
        }

        if ($ultimoLoginPrevio === null) {
            return $fecha->greaterThanOrEqualTo(now()->subDays(7));
        }

        return $fecha->greaterThan($ultimoLoginPrevio);
    }
}
