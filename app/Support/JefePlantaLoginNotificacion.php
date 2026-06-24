<?php

namespace App\Support;

use App\Models\RutaDistribucion;
use App\Models\Usuario;

final class JefePlantaLoginNotificacion
{
    /**
     * Traslados planta → mayorista pendientes de aprobación.
     *
     * @return list<array{clave: string, codigo: string, url: string, destino: string, productos: string}>
     */
    public static function trasladosPendientesDesdeLogin(Usuario $user, ?\Carbon\Carbon $ultimoLoginPrevio = null): array
    {
        if (! UsuarioRol::esJefePlanta($user) && ! UsuarioRol::esAdminGlobal($user)) {
            return [];
        }

        $items = RutaDistribucion::query()
            ->where('tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA)
            ->where('estado', RutaDistribucionCatalogo::ESTADO_PENDIENTE_APROBACION)
            ->with(['almacenMayoristaDestino', 'detallesTraslado'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function (RutaDistribucion $ruta) {
                $destino = $ruta->almacenMayoristaDestino?->nombre ?? 'Centro mayorista';
                $n = $ruta->detallesTraslado?->count() ?? 0;
                $productos = $n === 1 ? '1 producto' : $n.' productos';

                return [
                    'clave' => 'traslado:'.(int) $ruta->rutadistribucionid,
                    'codigo' => $ruta->codigo ?? $ruta->nombre ?? 'Traslado #'.$ruta->rutadistribucionid,
                    'url' => route('logistica.traslados-planta.show', $ruta),
                    'destino' => $destino,
                    'productos' => $productos,
                ];
            })
            ->values()
            ->all();

        return LoginNotificacionAlcance::filtrarPendientes(
            LoginNotificacionAlcance::JEFE_PLANTA_TRASLADO,
            (int) $user->usuarioid,
            $items
        );
    }
}
