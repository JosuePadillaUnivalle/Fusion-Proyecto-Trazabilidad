<?php

namespace App\Support;

use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class EnvioListadoService
{
    /**
     * @return array<string, mixed>
     */
    public static function prepararListado(Request $request): array
    {
        return app(TransporteListadoUnificadoService::class)->prepararListado($request);
    }

    /**
     * @return Builder<Pedido>
     */
    public static function queryBase(
        ?Usuario $user,
        bool $esTransportista,
        Request $request,
        int $filtroTransportista = 0
    ): Builder {
        $query = Pedido::query()
            ->with([
                'detalles.insumo',
                'envioAsignacion.transportista.perfilTransportista.vehiculo',
                'envioAsignacion.asignadoPor',
                'envioAsignacion.ruta.paradas',
            ])
            ->orderByDesc('fechapedido')
            ->orderByDesc('pedidoid');

        PedidoCatalogo::aplicarFiltroLogistica($query);

        if ($esTransportista) {
            $query->whereHas('envioAsignacion', function ($q) use ($user) {
                $q->where('transportista_usuarioid', $user?->usuarioid)
                    ->whereNotNull('transportista_usuarioid');
            });
            $query->whereIn('estado', PedidoCatalogo::estadosListosParaLogistica());
        } else {
            if ($filtroTransportista > 0) {
                $query->whereHas('envioAsignacion', fn ($q) => $q->where('transportista_usuarioid', $filtroTransportista));
            }

            if ($request->filled('transportista_nombre')) {
                $nombre = $request->string('transportista_nombre')->trim()->toString();
                $query->whereHas('envioAsignacion.transportista', function ($q) use ($nombre) {
                    $q->where('nombre', 'like', "%{$nombre}%")
                        ->orWhere('apellido', 'like', "%{$nombre}%")
                        ->orWhere('nombreusuario', 'like', "%{$nombre}%")
                        ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$nombre}%"]);
                });
            }

            if ($request->filled('vehiculo')) {
                $placa = $request->string('vehiculo')->trim()->toString();
                $query->whereHas('envioAsignacion', fn ($q) => $q->where('vehiculo_ref', 'like', "%{$placa}%"));
            }

            if ($request->filled('estado_logistica')) {
                $estados = EnvioAsignacionEstadoCatalogo::estadosEquivalentes(
                    $request->string('estado_logistica')->toString()
                );
                $query->whereHas('envioAsignacion', fn ($q) => $q->whereIn('estado', $estados));
            }
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $query->where(function ($w) use ($term) {
                $w->where('numero_solicitud', 'like', "%{$term}%")
                    ->orWhere('nombre_planta', 'like', "%{$term}%")
                    ->orWhere('direccion_texto', 'like', "%{$term}%")
                    ->orWhereHas('detalles', fn ($d) => $d->where('cultivo_personalizado', 'like', "%{$term}%"))
                    ->orWhereHas('envioAsignacion', fn ($e) => $e->where('externo_envio_id', 'like', "%{$term}%"))
                    ->orWhereHas('envioAsignacion.transportista', function ($t) use ($term) {
                        $t->where('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%")
                            ->orWhere('nombreusuario', 'like', "%{$term}%");
                    })
                    ->orWhereHas('envioAsignacion.ruta', fn ($r) => $r->where('nombre', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->string('estado')->toString();
            $query->where('estado', $estado);

            if ($estado === PedidoCatalogo::ESTADO_CONFIRMADO) {
                $query->where(function (Builder $q) {
                    $q->whereDoesntHave('envioAsignacion')
                        ->orWhereHas('envioAsignacion', function (Builder $envio) {
                            $envio->whereNull('fecha_recepcion_planta')
                                ->whereNotIn('estado', ['recibido_planta', 'entregado', 'entregada']);
                        });
                });
            }
        }

        if ($request->filled('desde')) {
            $query->whereDate('fechapedido', '>=', $request->string('desde')->toString());
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fechapedido', '<=', $request->string('hasta')->toString());
        }

        if ($user && CampoJefeScope::debeAcotar($user) && ! $esTransportista) {
            $query->whereHas('envioAsignacion', function (Builder $envio) use ($user) {
                CampoJefeScope::aplicarEnEnvioAgricola($envio, $user);
            });
        }

        return $query;
    }

    /**
     * @return array<string, int>
     */
    public static function resumenDesdePedidos(?Usuario $user = null): array
    {
        $user ??= auth()->user();
        $base = EnvioAsignacionMultiple::query()
            ->whereHas('pedido', function (Builder $p) {
                PedidoCatalogo::aplicarFiltroLogistica($p);
            });

        if (CampoJefeScope::debeAcotar($user)) {
            CampoJefeScope::aplicarEnEnvioAgricola($base, $user);
        }

        return [
            'total' => (clone $base)->count(),
            'asignados' => (clone $base)->whereIn('estado', ['asignado', 'asignada', 'pendiente', 'creada'])->count(),
            'en_camino' => (clone $base)->whereIn('estado', ['en_transporte_planta', 'en_ruta', 'en_transito'])->count(),
            'recibidos' => (clone $base)->where(function ($q) {
                $q->whereIn('estado', ['recibido_planta', 'entregado', 'entregada'])
                    ->orWhereNotNull('fecha_recepcion_planta');
            })->count(),
            'recibidos_hoy' => (clone $base)->whereDate('fecha_recepcion_planta', now()->toDateString())->count(),
        ];
    }
}
