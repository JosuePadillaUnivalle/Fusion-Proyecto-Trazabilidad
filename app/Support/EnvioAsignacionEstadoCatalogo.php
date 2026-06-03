<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class EnvioAsignacionEstadoCatalogo
{
    private const ALIASES = [
        'pendiente'   => ['pendiente', 'creada'],
        'asignado'    => ['asignado', 'asignada'],
        'en_ruta'     => ['en_ruta', 'en_transito', 'en_transporte_planta'],
        'en_transporte_planta' => ['en_transporte_planta', 'en_ruta', 'en_transito'],
        'recibido_planta' => ['recibido_planta', 'entregado', 'entregada'],
        'entregado'   => ['entregado', 'entregada', 'recibido_planta'],
        'cancelado'   => ['cancelada', 'cancelado'],
        'cancelada'   => ['cancelada', 'cancelado'],
        'creada'      => ['creada', 'pendiente'],
        'asignada'    => ['asignada', 'asignado'],
        'en_transito' => ['en_transito', 'en_ruta', 'en_transporte_planta'],
        'entregada'   => ['entregada', 'entregado', 'recibido_planta'],
    ];

    public static function etiqueta(?string $estado): string
    {
        $key = strtolower(trim((string) $estado));

        return match ($key) {
            'en_transporte_planta', 'en_ruta', 'en_transito' => 'En transporte hacia planta',
            'recibido_planta', 'entregado', 'entregada' => 'Recibido en planta',
            'asignado', 'asignada' => 'Asignado',
            'pendiente', 'creada' => 'Pendiente',
            'cancelado', 'cancelada' => 'Cancelado',
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }

    public static function resolveId(?string $estado): ?int
    {
        if ($estado === null || trim($estado) === '') {
            return null;
        }

        if (! Schema::hasTable('estado_asignacion_multiple_catalogo')) {
            return null;
        }

        $key = strtolower(trim($estado));
        $candidates = self::ALIASES[$key] ?? [$key];

        foreach ($candidates as $nombre) {
            $id = DB::table('estado_asignacion_multiple_catalogo')
                ->where('nombre', $nombre)
                ->value('estadoasignacioncatalogoid');

            if ($id !== null) {
                return (int) $id;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function applyToAttributes(array $attributes): array
    {
        if (! array_key_exists('estado', $attributes)) {
            return $attributes;
        }

        $catalogId = self::resolveId(
            is_string($attributes['estado']) ? $attributes['estado'] : null
        );

        if ($catalogId !== null) {
            $attributes['estadoasignacioncatalogoid'] = $catalogId;
        }

        $estado = strtolower(trim((string) ($attributes['estado'] ?? '')));
        if (! in_array($estado, ['cancelado', 'cancelada'], true)) {
            $attributes['motivocancelacionid'] = null;
        }

        return $attributes;
    }
}
