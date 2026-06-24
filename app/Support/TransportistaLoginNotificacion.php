<?php

namespace App\Support;

use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;
use App\Models\Usuario;

final class TransportistaLoginNotificacion
{
    /**
     * Envíos y rutas activas asignadas al transportista que aún no descartó en modal.
     *
     * @return list<array{clave: string, codigo: string, url: string, producto: string}>
     */
    public static function nuevasAsignacionesDesdeLogin(Usuario $user, ?\Carbon\Carbon $ultimoLoginPrevio = null): array
    {
        if (! UsuarioRol::esTransportista($user)) {
            return [];
        }

        $user->loadMissing('perfilTransportista');
        $ambito = $user->perfilTransportista?->ambito_flota ?? TransportistaFlotaCatalogo::AGRICOLA;

        $items = collect();

        if ($ambito === TransportistaFlotaCatalogo::AGRICOLA) {
            self::agregarAsignacionesAgricolas($user, $items);
        }

        if (in_array($ambito, [TransportistaFlotaCatalogo::PLANTA, TransportistaFlotaCatalogo::MAYORISTA], true)) {
            self::agregarRutasPorAmbito($user, $items, $ambito);
        }

        return LoginNotificacionAlcance::filtrarPendientes(
            LoginNotificacionAlcance::TRANSPORTISTA,
            (int) $user->usuarioid,
            $items
                ->unique(fn (array $row) => $row['clave'])
                ->values()
                ->all()
        );
    }

    /** @param  \Illuminate\Support\Collection<int, array{clave: string, codigo: string, url: string, producto: string}>  $items */
    private static function agregarAsignacionesAgricolas(Usuario $user, \Illuminate\Support\Collection $items): void
    {
        EnvioAsignacionMultiple::query()
            ->where('transportista_usuarioid', $user->usuarioid)
            ->whereNotIn('estado', ['recibido_planta', 'entregado', 'entregada', 'cancelado', 'cancelada'])
            ->with(['pedido.detalles'])
            ->orderByDesc('fecha_asignacion')
            ->limit(10)
            ->get()
            ->each(function (EnvioAsignacionMultiple $a) use ($items) {
                if (EnvioAsignacionEstadoCatalogo::llegoADestino($a)) {
                    return;
                }

                $items->push([
                    'clave' => 'agricola:'.(int) $a->envioasignacionmultipleid,
                    'codigo' => $a->externo_envio_id ?? $a->pedido?->numero_solicitud ?? '#'.$a->envioasignacionmultipleid,
                    'url' => route('logistica.asignaciones.cierre.panel', $a),
                    'producto' => $a->pedido?->detalles?->first()?->cultivo_personalizado ?? 'Envío agrícola',
                ]);
            });
    }

    /** @param  \Illuminate\Support\Collection<int, array{clave: string, codigo: string, url: string, producto: string}>  $items */
    private static function agregarRutasPorAmbito(Usuario $user, \Illuminate\Support\Collection $items, string $ambito): void
    {
        $query = RutaDistribucion::query()
            ->where('transportista_usuarioid', $user->usuarioid)
            ->whereNotIn('estado', ['completada', 'cancelada', 'rechazada'])
            ->orderByDesc('updated_at')
            ->limit(10);

        if ($ambito === TransportistaFlotaCatalogo::PLANTA) {
            $query->where('tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA)
                ->with(['detallesTraslado.insumo']);
        } else {
            $query->where('tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_MAYORISTA_PDV)
                ->with(['pedidos.detalles']);
        }

        $query->get()->each(function (RutaDistribucion $r) use ($items, $user, $ambito) {
            if ($ambito === TransportistaFlotaCatalogo::PLANTA) {
                if (! in_array($r->estado, [
                    RutaDistribucionCatalogo::ESTADO_PLANIFICADA,
                    RutaDistribucionCatalogo::ESTADO_EN_RUTA,
                    RutaDistribucionCatalogo::ESTADO_PENDIENTE_APROBACION,
                ], true)) {
                    return;
                }

                $detalle = $r->detallesTraslado?->first();
                $items->push([
                    'clave' => 'ruta:'.(int) $r->rutadistribucionid,
                    'codigo' => $r->codigo ?? $r->nombre ?? 'Traslado #'.$r->rutadistribucionid,
                    'url' => RutaDistribucionNavegacion::urlVer($r, $user),
                    'producto' => $detalle?->insumo?->nombre ?? $detalle?->producto_nombre ?? 'Planta → Mayorista',
                ]);

                return;
            }

            $primer = $r->pedidos?->first()?->detalles?->first();
            $items->push([
                'clave' => 'ruta:'.(int) $r->rutadistribucionid,
                'codigo' => $r->codigo ?? $r->nombre ?? 'Ruta #'.$r->rutadistribucionid,
                'url' => RutaDistribucionNavegacion::urlVer($r, $user),
                'producto' => $primer?->producto_nombre ?? $primer?->cultivo_personalizado ?? 'Distribución',
            ]);
        });
    }
}
