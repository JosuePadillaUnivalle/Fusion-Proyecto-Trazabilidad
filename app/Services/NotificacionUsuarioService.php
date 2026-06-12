<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\AsignacionEtapaPlanta;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Lote;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\UsuarioNotificacion;
use App\Support\EnvioPedidoService;
use App\Support\PedidoCatalogo;
use App\Support\UsuarioRol;

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
            'enlace' => $this->enlaceInterno($enlace),
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
            route('lotes.show', $lote, false),
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
            route('actividades.show', $actividad, false),
            'actividad',
            (int) $actividad->actividadid,
        );
    }

    public function llegadaDestinoReportada(EnvioAsignacionMultiple $asignacion, Usuario $transportista): void
    {
        $codigo = $asignacion->externo_envio_id ?? '#'.$asignacion->envioasignacionmultipleid;
        $nombre = trim(($transportista->nombre ?? '').' '.($transportista->apellido ?? '')) ?: ($transportista->nombreusuario ?? 'Transportista');
        $planta = $asignacion->pedido?->nombre_planta;

        $mensaje = $planta
            ? "{$nombre} reportó la llegada del envío {$codigo} a {$planta}."
            : "{$nombre} reportó la llegada del envío {$codigo} a destino.";

        $admins = Usuario::query()
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereIn('role', ['admin', 'Admin'])
                    ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['admin', 'Admin']));
            })
            ->get();

        foreach ($admins as $admin) {
            $this->notificar(
                $admin,
                'envio_llegada_destino',
                'Envío recibido en planta',
                $mensaje,
                route('logistica.asignaciones.listado', ['q' => $codigo], false),
                'envio_asignacion',
                (int) $asignacion->envioasignacionmultipleid,
            );
        }
    }

    public function envioListoParaRecoger(EnvioAsignacionMultiple $asignacion): void
    {
        if (! $asignacion->transportista_usuarioid) {
            return;
        }

        $asignacion->loadMissing(['pedido.detalles']);
        $codigo = $asignacion->externo_envio_id
            ?? $asignacion->pedido?->numero_solicitud
            ?? '#'.$asignacion->envioasignacionmultipleid;
        $producto = $asignacion->pedido?->detalles?->first()?->cultivo_personalizado ?? 'Producto agrícola';
        $destino = $asignacion->pedido
            ? (EnvioPedidoService::etiquetaPlantaDestinoLista($asignacion->pedido) ?? 'planta de procesamiento')
            : 'planta de procesamiento';

        $this->notificar(
            (int) $asignacion->transportista_usuarioid,
            'envio_listo_recoger',
            'Envío listo para recoger',
            "El pedido {$codigo} ({$producto}) fue aceptado. Confirme la carga e inicie ruta hacia {$destino}.",
            route('logistica.asignaciones.show', $asignacion, false),
            'envio_asignacion',
            (int) $asignacion->envioasignacionmultipleid,
        );
    }

    public function pedidoPendienteAgricola(Pedido $pedido): void
    {
        if (! PedidoCatalogo::pendienteAprobacionAgricola($pedido)) {
            return;
        }

        $pedido->loadMissing('detalles');
        $producto = $pedido->detalles->first()?->cultivo_personalizado ?? 'Producto agrícola';
        $totalKg = number_format((float) $pedido->detalles->sum('cantidad'), 2);

        $jefes = Usuario::query()
            ->where('activo', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'jefe_agricultor'))
            ->get();

        foreach ($jefes as $jefe) {
            $this->notificar(
                $jefe,
                'pedido_pendiente_agricola',
                'Pedido pendiente de aceptar',
                "La solicitud {$pedido->numero_solicitud} ({$producto}, {$totalKg} kg) espera aprobación de producción agrícola.",
                route('agricola.pedidos.show', $pedido, false),
                'pedido',
                (int) $pedido->pedidoid,
            );
        }
    }

    public function etapaPlantaAsignada(AsignacionEtapaPlanta $asignacion): void
    {
        $operador = Usuario::query()->find($asignacion->operador_usuarioid);
        if (! $operador || ! UsuarioRol::esOperarioPlanta($operador)) {
            return;
        }

        $asignacion->loadMissing(['proceso', 'maquina', 'loteProduccion']);
        $proceso = $asignacion->proceso?->nombre ?? 'proceso';
        $maquina = $asignacion->maquina?->nombre ?? 'maquinaria';
        $lote = $asignacion->loteProduccion?->codigo_lote ?? 'lote';

        $this->notificar(
            (int) $operador->usuarioid,
            'etapa_planta_asignada',
            'Nueva tarea de transformación',
            "Se le asignó «{$proceso}» en {$maquina} — lote {$lote}.",
            route('tareas-planta.show', $asignacion, false),
            'asignacion_etapa_planta',
            (int) $asignacion->asignacionetapaplantaid,
        );
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, UsuarioNotificacion> */
    public function noLeidasPara(int $usuarioid, int $limite = 10, ?Usuario $usuario = null)
    {
        return $this->queryNoLeidas($usuarioid, $usuario)
            ->orderByDesc('creado_en')
            ->limit($limite)
            ->get();
    }

    public function contarNoLeidas(int $usuarioid, ?Usuario $usuario = null): int
    {
        return $this->queryNoLeidas($usuarioid, $usuario)->count();
    }

    public function esNotificacionOperarioPlanta(UsuarioNotificacion $notificacion): bool
    {
        return $notificacion->tipo === 'etapa_planta_asignada';
    }

    public function puedeRecibirNotificacionOperarioPlanta(?Usuario $usuario): bool
    {
        return UsuarioRol::esOperarioPlanta($usuario);
    }

    private function queryNoLeidas(int $usuarioid, ?Usuario $usuario = null)
    {
        $usuario ??= Usuario::query()->find($usuarioid);

        $query = UsuarioNotificacion::query()
            ->where('usuarioid', $usuarioid)
            ->whereNull('leida_at');

        if (! $this->puedeRecibirNotificacionOperarioPlanta($usuario)) {
            $query->where('tipo', '!=', 'etapa_planta_asignada');
        }

        return $query;
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

    /** Marca como leídas todas las notificaciones no leídas visibles para el usuario. */
    public function marcarTodasLeidas(int $usuarioid, ?Usuario $usuario = null): int
    {
        $ahora = now();

        return $this->queryNoLeidas($usuarioid, $usuario)
            ->update(['leida_at' => $ahora]);
    }

    /** Elimina alertas de tarea de transformación cuando la asignación ya fue completada. */
    public function descartarEtapaPlantaAsignada(int $asignacionId): void
    {
        UsuarioNotificacion::query()
            ->where('tipo', 'etapa_planta_asignada')
            ->where('referencia_tipo', 'asignacion_etapa_planta')
            ->where('referencia_id', $asignacionId)
            ->delete();
    }

    /** Marca como leídas las alertas de actividad asignada cuando ya fue completada. */
    public function descartarActividadAsignada(int $actividadId): void
    {
        UsuarioNotificacion::query()
            ->where('tipo', 'actividad_asignada')
            ->where('referencia_tipo', 'actividad')
            ->where('referencia_id', $actividadId)
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);
    }

    private function enlaceInterno(?string $enlace): ?string
    {
        if ($enlace === null || $enlace === '') {
            return null;
        }

        if (str_starts_with($enlace, '/')) {
            return $enlace;
        }

        $base = rtrim((string) config('app.url'), '/');
        if ($base !== '' && str_starts_with($enlace, $base)) {
            $relativo = substr($enlace, strlen($base));

            return $relativo === '' ? '/' : $relativo;
        }

        return $enlace;
    }
}
