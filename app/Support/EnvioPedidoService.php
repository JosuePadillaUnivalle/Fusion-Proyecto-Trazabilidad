<?php

namespace App\Support;

use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Models\PerfilTransportista;
use App\Models\Usuario;
use App\Models\Vehiculo;
use InvalidArgumentException;

final class EnvioPedidoService
{
    /** @var array<int, string> */
    private const ESTADOS_EN_RUTA_PLANTA = ['en_transporte_planta', 'en_ruta', 'en_transito'];

    /** @var array<int, string> */
    private const ESTADOS_ASIGNADO = ['asignado', 'asignada', 'pendiente', 'creada'];

    public static function placaTransportista(int $transportistaId): ?string
    {
        $perfil = PerfilTransportista::query()
            ->with('vehiculo')
            ->where('usuarioid', $transportistaId)
            ->first();

        return $perfil?->vehiculo?->placa;
    }

    public static function vehiculoIdDesdeEnvio(?EnvioAsignacionMultiple $envio): ?int
    {
        if ($envio === null) {
            return null;
        }

        if ($envio->vehiculo_ref) {
            $id = Vehiculo::query()
                ->where('placa', $envio->vehiculo_ref)
                ->value('vehiculoid');

            if ($id !== null) {
                return (int) $id;
            }
        }

        return PerfilTransportista::query()
            ->where('usuarioid', $envio->transportista_usuarioid)
            ->value('vehiculoid');
    }

    public static function resolverVehiculoAsignado(?EnvioAsignacionMultiple $envio): ?Vehiculo
    {
        if ($envio === null) {
            return null;
        }

        if ($envio->vehiculo_ref) {
            $vehiculo = Vehiculo::query()
                ->with('tipoVehiculo')
                ->where('placa', $envio->vehiculo_ref)
                ->first();

            if ($vehiculo) {
                return $vehiculo;
            }
        }

        return $envio->transportista?->perfilTransportista?->vehiculo;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function datosLogistica(?EnvioAsignacionMultiple $envio): ?array
    {
        if ($envio === null || ! $envio->transportista_usuarioid) {
            return null;
        }

        $transportista = $envio->transportista;
        $vehiculo = self::resolverVehiculoAsignado($envio);
        $tipo = $vehiculo?->tipoVehiculo?->nombre;

        $nombreVehiculo = trim(collect([$vehiculo?->marca, $vehiculo?->modelo])->filter()->implode(' '));
        if ($nombreVehiculo === '' && $tipo) {
            $nombreVehiculo = $tipo;
        }

        $estado = strtolower(trim((string) ($envio->estado ?? '')));
        $cargadoEnRuta = in_array($estado, self::ESTADOS_EN_RUTA_PLANTA, true);
        $recibidoPlanta = in_array($estado, ['recibido_planta', 'entregado', 'entregada'], true);

        return [
            'transportista_nombre' => trim(($transportista->nombre ?? '').' '.($transportista->apellido ?? '')),
            'transportista_usuarioid' => (int) $envio->transportista_usuarioid,
            'vehiculoid' => $vehiculo?->vehiculoid,
            'vehiculo_nombre' => $nombreVehiculo !== '' ? $nombreVehiculo : ($tipo ?? '—'),
            'placa' => $envio->vehiculo_ref ?? $vehiculo?->placa ?? '—',
            'estado' => $estado,
            'estado_etiqueta' => EnvioAsignacionEstadoCatalogo::etiqueta($estado),
            'asignado' => in_array($estado, self::ESTADOS_ASIGNADO, true) || $cargadoEnRuta || $recibidoPlanta,
            'cargado_en_ruta' => $cargadoEnRuta,
            'recibido_planta' => $recibidoPlanta,
            'fecha_asignacion' => $envio->fecha_asignacion,
            'asignado_por' => $envio->asignadoPor
                ? trim($envio->asignadoPor->nombre.' '.($envio->asignadoPor->apellido ?? ''))
                : null,
        ];
    }

    public static function asignarTransportistaYVehiculo(
        Pedido $pedido,
        int $transportistaId,
        int $vehiculoId,
        int $asignadoPorId,
        bool $permitirReasignar = true
    ): EnvioAsignacionMultiple {
        if (! PedidoCatalogo::puedeAsignarTransportista($pedido)) {
            throw new InvalidArgumentException('Producción agrícola debe aceptar el pedido y reservar stock antes de asignar transportista.');
        }

        $transportista = Usuario::query()
            ->where('usuarioid', $transportistaId)
            ->where('role', 'transportista')
            ->where('activo', true)
            ->first();

        if (! $transportista) {
            throw new InvalidArgumentException('El usuario seleccionado no es un transportista activo.');
        }

        $vehiculo = Vehiculo::query()
            ->where('vehiculoid', $vehiculoId)
            ->where('activo', true)
            ->first();

        if (! $vehiculo) {
            throw new InvalidArgumentException('El vehículo seleccionado no está disponible.');
        }

        $envioExistente = EnvioAsignacionMultiple::query()
            ->where(function ($q) use ($pedido) {
                $q->where('pedidoid', $pedido->pedidoid)
                    ->orWhere('externo_envio_id', $pedido->numero_solicitud);
            })
            ->first();

        if ($envioExistente?->transportista_usuarioid && ! $permitirReasignar) {
            throw new InvalidArgumentException('Este pedido ya tiene transportista asignado.');
        }

        $estadoActual = strtolower(trim((string) ($envioExistente?->estado ?? '')));
        $estadoNuevo = in_array($estadoActual, ['en_transporte_planta', 'en_ruta', 'en_transito', 'recibido_planta', 'entregado', 'entregada'], true)
            ? $estadoActual
            : 'asignado';

        return EnvioAsignacionMultiple::updateOrCreate(
            ['externo_envio_id' => $pedido->numero_solicitud],
            EnvioAsignacionEstadoCatalogo::applyToAttributes([
                'pedidoid' => $pedido->pedidoid,
                'transportista_usuarioid' => $transportista->usuarioid,
                'asignadopor_usuarioid' => $asignadoPorId,
                'vehiculo_ref' => $vehiculo->placa,
                'estado' => $estadoNuevo,
                'fecha_asignacion' => $envioExistente?->fecha_asignacion ?? now(),
            ])
        );
    }

    public static function confirmarCargaHaciaPlanta(EnvioAsignacionMultiple $envio): void
    {
        if (! $envio->transportista_usuarioid) {
            throw new InvalidArgumentException('El envío no tiene transportista asignado.');
        }

        $estado = strtolower(trim((string) ($envio->estado ?? '')));

        if (in_array($estado, self::ESTADOS_EN_RUTA_PLANTA, true)) {
            return;
        }

        if (! in_array($estado, self::ESTADOS_ASIGNADO, true)) {
            throw new InvalidArgumentException('Solo puede confirmar la carga cuando el envío está asignado.');
        }

        if ($envio->pedido && ! PedidoCatalogo::puedeAsignarTransportista($envio->pedido)) {
            throw new InvalidArgumentException('El pedido aún no está listo para el envío.');
        }

        $envio->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
            'estado' => 'en_transporte_planta',
            'fecha_asignacion' => $envio->fecha_asignacion ?? now(),
        ]));
    }
}
