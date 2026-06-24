<?php

namespace App\Support;

use App\Models\MaquinaVariablePlanta;
use App\Models\VariableEstandar;
use Illuminate\Support\Facades\Schema;

/**
 * Límites de parámetros: máquina (techo físico) y escala del catálogo (ej. calidad 1–10).
 */
class ParametroRangoPlanta
{
    /** @return array{min: float, max: float}|null */
    public static function limitesEscala(?string $unidad): ?array
    {
        if ($unidad === null || $unidad === '') {
            return null;
        }

        if (preg_match('/escala\s*(\d+(?:[.,]\d+)?)\s*[-–]\s*(\d+(?:[.,]\d+)?)/iu', (string) $unidad, $m)) {
            return [
                'min' => (float) str_replace(',', '.', $m[1]),
                'max' => (float) str_replace(',', '.', $m[2]),
            ];
        }

        return null;
    }

    /** @return array<int, array{min: float, max: float}> */
    public static function mapaLimitesMaquina(int $maquinaplantaid): array
    {
        if (! Schema::hasTable('maquina_variable_planta')) {
            return [];
        }

        $out = [];
        MaquinaVariablePlanta::query()
            ->where('maquinaplantaid', $maquinaplantaid)
            ->get(['variableestandarid', 'valor_minimo', 'valor_maximo'])
            ->each(function (MaquinaVariablePlanta $v) use (&$out) {
                $out[(int) $v->variableestandarid] = [
                    'min' => (float) $v->valor_minimo,
                    'max' => (float) $v->valor_maximo,
                ];
            });

        return $out;
    }

    /** @return array{min: float, max: float}|null */
    public static function limitesMaquina(int $maquinaplantaid, int $variableestandarid): ?array
    {
        return self::mapaLimitesMaquina($maquinaplantaid)[$variableestandarid] ?? null;
    }

    /**
     * @param  array{min: float, max: float}|null  $maq
     * @param  array{min: float, max: float}|null  $escala
     * @return array{min: float, max: float}|null
     */
    public static function combinarLimites(?array $maq, ?array $escala): ?array
    {
        if ($maq === null && $escala === null) {
            return null;
        }

        if ($maq === null) {
            return $escala;
        }

        if ($escala === null) {
            return $maq;
        }

        $min = max($maq['min'], $escala['min']);
        $max = min($maq['max'], $escala['max']);

        if ($max < $min) {
            return $maq;
        }

        return ['min' => $min, 'max' => $max];
    }

    /** @return array{min: float, max: float} */
    public static function limitesPermitidos(int $maquinaplantaid, int $variableestandarid, ?VariableEstandar $variable = null): ?array
    {
        $variable ??= VariableEstandar::query()->find($variableestandarid);
        $maq = $maquinaplantaid > 0 ? self::limitesMaquina($maquinaplantaid, $variableestandarid) : null;
        $escala = self::limitesEscala($variable?->unidad);

        return self::combinarLimites($maq, $escala);
    }

  public static function validarRango(
        ?int $maquinaplantaid,
        int $variableestandarid,
        float $min,
        float $max,
        ?string $nombreVariable = null,
        ?VariableEstandar $variable = null,
    ): ?string {
        if ($max < $min) {
            $label = $nombreVariable ?? 'El parámetro';

            return $label.': el máximo debe ser mayor o igual al mínimo.';
        }

        $variable ??= VariableEstandar::query()->find($variableestandarid);
        $label = $nombreVariable ?? ($variable?->nombre ?? 'Parámetro');

        if ($maquinaplantaid !== null && $maquinaplantaid > 0) {
            $maq = self::limitesMaquina($maquinaplantaid, $variableestandarid);
            if ($maq === null) {
                return $label.' no está definido en los parámetros estándar de la máquina seleccionada.';
            }
            if ($min < $maq['min'] || $max > $maq['max']) {
                return $label.' debe estar entre '.$maq['min'].' y '.$maq['max']
                    .' (límite estándar de la máquina).';
            }
        }

        $escala = self::limitesEscala($variable?->unidad);
        if ($escala !== null) {
            if ($min < $escala['min'] || $max > $escala['max']) {
                return $label.' debe estar entre '.$escala['min'].' y '.$escala['max'].'.';
            }
        }

        return null;
    }

    /**
     * @param  list<array{variableestandarid: int, valor_minimo: float|int|string, valor_maximo: float|int|string}>  $variables
     * @param  array<int, string>  $nombresPorId
     */
    public static function validarLista(?int $maquinaplantaid, array $variables, array $nombresPorId = []): ?string
    {
        foreach ($variables as $var) {
            $varId = (int) ($var['variableestandarid'] ?? 0);
            if ($varId <= 0) {
                continue;
            }

            $error = self::validarRango(
                $maquinaplantaid,
                $varId,
                (float) ($var['valor_minimo'] ?? 0),
                (float) ($var['valor_maximo'] ?? 0),
                $nombresPorId[$varId] ?? null,
            );

            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    public static function validarValorRegistrado(
        float $valor,
        float $min,
        float $max,
        ?string $nombreVariable = null,
    ): ?string {
        $label = $nombreVariable ?? 'El valor';

        if ($valor < $min || $valor > $max) {
            return $label.' debe estar entre '.$min.' y '.$max.' (registró '.$valor.').';
        }

        return null;
    }

    /**
     * Rango de proceso sugerido (más estrecho que el de la máquina).
     *
     * @return array{min: float, max: float}
     */
    public static function rangoProcesoDesdeMaquina(float $maqMin, float $maqMax, float $fraccionInset = 0.15): array
    {
        $span = $maqMax - $maqMin;
        if ($span <= 0) {
            return ['min' => $maqMin, 'max' => $maqMax];
        }

        $inset = max($span * $fraccionInset, $span * 0.05);

        return [
            'min' => round($maqMin + $inset, 2),
            'max' => round($maqMax - $inset, 2),
        ];
    }
}
