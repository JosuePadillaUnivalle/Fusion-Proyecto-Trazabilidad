<?php

namespace App\Support;

use App\Models\EstadoVehiculo;
use App\Models\Vehiculo;

final class EstadoVehiculoCatalogo
{
    public const OPERATIVO = 'operativo';

    public const MANTENIMIENTO = 'mantenimiento';

    public const BAJA = 'baja';

    public static function idPorNombre(string $nombre): ?int
    {
        $id = EstadoVehiculo::query()
            ->whereRaw('LOWER(nombre) = ?', [strtolower($nombre)])
            ->value('estadovehiculoid');

        return $id !== null ? (int) $id : null;
    }

    public static function idOperativo(): ?int
    {
        return self::idPorNombre(self::OPERATIVO);
    }

    public static function idMantenimiento(): ?int
    {
        return self::idPorNombre(self::MANTENIMIENTO);
    }

    public static function nombreEstado(?Vehiculo $vehiculo): string
    {
        return strtolower(trim((string) ($vehiculo?->estadoVehiculo?->nombre ?? '')));
    }

    public static function enMantenimiento(Vehiculo $vehiculo): bool
    {
        return self::nombreEstado($vehiculo) === self::MANTENIMIENTO;
    }

    public static function enBaja(Vehiculo $vehiculo): bool
    {
        return self::nombreEstado($vehiculo) === self::BAJA;
    }

    public static function disponibleParaUso(Vehiculo $vehiculo): bool
    {
        if (! $vehiculo->activo) {
            return false;
        }

        $nombre = self::nombreEstado($vehiculo);

        if ($nombre === self::MANTENIMIENTO || $nombre === self::BAJA) {
            return false;
        }

        return $nombre === '' || $nombre === self::OPERATIVO;
    }

    public static function etiqueta(?Vehiculo $vehiculo): string
    {
        if (! $vehiculo) {
            return '—';
        }

        $nombre = self::nombreEstado($vehiculo);

        return match ($nombre) {
            self::MANTENIMIENTO => 'En mantenimiento',
            self::BAJA => 'De baja',
            self::OPERATIVO => 'Operativo',
            '' => $vehiculo->activo ? 'Operativo' : 'Inactivo',
            default => ucfirst($nombre),
        };
    }

    public static function badgeClase(?Vehiculo $vehiculo): string
    {
        $nombre = self::nombreEstado($vehiculo);

        return match ($nombre) {
            self::MANTENIMIENTO => 'badge-warning text-dark',
            self::BAJA => 'badge-secondary',
            self::OPERATIVO => 'badge-success',
            '' => ($vehiculo?->activo ?? false) ? 'badge-success' : 'badge-secondary',
            default => 'badge-light border',
        };
    }
}
