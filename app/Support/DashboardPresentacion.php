<?php

namespace App\Support;

use Carbon\Carbon;

final class DashboardPresentacion
{
    /**
     * @return array{icon: string, class: string}
     */
    public static function actividadIcono(?string $nombreTipo): array
    {
        $n = self::normalizar($nombreTipo);

        $reglas = [
            ['keys' => ['riego'], 'icon' => 'fa-tint', 'class' => 'activity-riego'],
            ['keys' => ['siembra'], 'icon' => 'fa-seedling', 'class' => 'activity-siembra'],
            ['keys' => ['cosecha'], 'icon' => 'fa-truck-loading', 'class' => 'activity-cosecha'],
            ['keys' => ['fertiliz'], 'icon' => 'fa-flask', 'class' => 'activity-fertilizacion'],
            ['keys' => ['plaga', 'fumig', 'control'], 'icon' => 'fa-bug', 'class' => 'activity-plagas'],
            ['keys' => ['labranza', 'prepar'], 'icon' => 'fa-tractor', 'class' => 'activity-labranza'],
            ['keys' => ['poda', 'raleo'], 'icon' => 'fa-cut', 'class' => 'activity-poda'],
            ['keys' => ['monitoreo', 'inspeccion'], 'icon' => 'fa-search', 'class' => 'activity-monitoreo'],
        ];

        foreach ($reglas as $regla) {
            foreach ($regla['keys'] as $key) {
                if (str_contains($n, $key)) {
                    return ['icon' => $regla['icon'], 'class' => $regla['class']];
                }
            }
        }

        return ['icon' => 'fa-clipboard-list', 'class' => 'activity-default'];
    }

    public static function actividadFechaTexto(mixed $fechaInicio): string
    {
        if (empty($fechaInicio)) {
            return '';
        }

        $fecha = Carbon::parse($fechaInicio)->locale('es');

        if ($fecha->isFuture()) {
            return 'Programada · '.$fecha->diffForHumans();
        }

        return $fecha->diffForHumans();
    }

    /**
     * @return array<int, array{icon: string, tone: string, value: string, label: string, hint: string}>
     */
    public static function resumenEstadistico(array $stats): array
    {
        return [
            [
                'icon' => 'fa-map-marked-alt',
                'tone' => 'green',
                'value' => number_format((float) ($stats['hectareas_totales'] ?? 0), 0),
                'label' => 'Hectáreas',
                'hint' => 'Superficie total registrada',
            ],
            [
                'icon' => 'fa-weight-hanging',
                'tone' => 'teal',
                'value' => number_format((float) ($stats['produccion_mes_kg'] ?? 0), 0).' kg',
                'label' => 'Producción',
                'hint' => 'Cosecha del mes actual',
            ],
            [
                'icon' => 'fa-tasks',
                'tone' => 'blue',
                'value' => (string) ($stats['total_actividades'] ?? 0),
                'label' => 'Actividades',
                'hint' => 'Registros en el sistema',
            ],
            [
                'icon' => 'fa-users',
                'tone' => 'indigo',
                'value' => (string) ($stats['usuarios'] ?? 0),
                'label' => 'Usuarios',
                'hint' => 'Agricultores',
            ],
            [
                'icon' => 'fa-boxes',
                'tone' => 'orange',
                'value' => (string) ($stats['total_insumos'] ?? 0),
                'label' => 'Insumos',
                'hint' => 'Ítems en inventario',
            ],
            [
                'icon' => 'fa-coins',
                'tone' => 'gold',
                'value' => 'Bs.'.number_format((float) ($stats['ventas_mes'] ?? 0), 0),
                'label' => 'Ventas',
                'hint' => 'Ventas del mes',
            ],
        ];
    }

    private static function normalizar(?string $texto): string
    {
        $t = mb_strtolower(trim((string) $texto));
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);

        return $ascii !== false ? strtolower($ascii) : $t;
    }
}
