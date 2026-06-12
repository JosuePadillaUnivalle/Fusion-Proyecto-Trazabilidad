<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class DashboardFiltros
{
    /** Atajos de periodo (chips rápidos). */
    public const PERIODOS_RAPIDOS = [
        'mes_actual' => 'Este mes',
        'trimestre' => '3 meses',
        'semestre' => '6 meses',
        'anio' => 'Este año',
    ];

    public const PERIODOS_EXTRA = [
        'mes_anterior' => 'Mes anterior',
        'todo' => 'Todo',
    ];

    public function __construct(
        public readonly string $periodo,
        public readonly ?int $anioHistorico = null,
        public readonly ?string $desde = null,
        public readonly ?string $hasta = null,
        public readonly ?int $cultivoId = null,
        public readonly ?int $loteId = null,
        public readonly ?int $estadoLoteId = null,
    ) {}

    public static function desdeRequest(Request $request): self
    {
        $periodo = (string) $request->input('periodo', 'semestre');
        $periodosValidos = array_merge(self::PERIODOS_RAPIDOS, self::PERIODOS_EXTRA);
        if (! array_key_exists($periodo, $periodosValidos)) {
            $periodo = 'semestre';
        }

        $anioHistorico = $request->filled('anio') ? (int) $request->input('anio') : null;
        $desde = $request->filled('desde') ? (string) $request->input('desde') : null;
        $hasta = $request->filled('hasta') ? (string) $request->input('hasta') : null;

        return new self(
            $periodo,
            $anioHistorico ?: null,
            $desde,
            $hasta,
            $request->filled('cultivo') ? (int) $request->input('cultivo') : null,
            $request->filled('lote') ? (int) $request->input('lote') : null,
            $request->filled('estado_lote') ? (int) $request->input('estado_lote') : null,
        );
    }

    public function etiquetaPeriodo(): string
    {
        if ($this->usaRangoPersonalizado()) {
            return Carbon::parse($this->desde)->format('d/m/Y').' – '.Carbon::parse($this->hasta)->format('d/m/Y');
        }

        if ($this->anioHistorico) {
            return 'Año '.$this->anioHistorico;
        }

        return self::PERIODOS_RAPIDOS[$this->periodo]
            ?? self::PERIODOS_EXTRA[$this->periodo]
            ?? $this->periodo;
    }

    public function etiquetaGrafico(): string
    {
        $meses = $this->mesesParaGrafico();
        if ($meses === []) {
            return $this->etiquetaPeriodo();
        }

        $primero = $meses[0];
        $ultimo = $meses[array_key_last($meses)];

        if (count($meses) === 1) {
            return $primero['nombre'].' '.$primero['año'];
        }

        if ($primero['año'] === $ultimo['año']) {
            return $primero['nombre'].' – '.$ultimo['nombre'].' '.$ultimo['año'];
        }

        return $primero['nombre'].' '.$primero['año'].' – '.$ultimo['nombre'].' '.$ultimo['año'];
    }

    public function usaRangoPersonalizado(): bool
    {
        return $this->desde && $this->hasta;
    }

    public function tieneFiltrosActivos(): bool
    {
        return $this->cultivoId
            || $this->loteId
            || $this->estadoLoteId
            || $this->anioHistorico
            || $this->usaRangoPersonalizado()
            || $this->periodo !== 'semestre';
    }

    /**
     * @return array<int, string>
     */
    public static function aniosDisponibles(): array
    {
        $actual = (int) now()->year;
        $anios = [];
        for ($y = $actual; $y >= $actual - 3; $y--) {
            $anios[$y] = (string) $y;
        }

        return $anios;
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    public function rangoFechas(): array
    {
        if ($this->usaRangoPersonalizado()) {
            return [
                Carbon::parse($this->desde)->startOfDay(),
                Carbon::parse($this->hasta)->endOfDay(),
            ];
        }

        if ($this->anioHistorico) {
            return [
                Carbon::create($this->anioHistorico, 1, 1)->startOfDay(),
                Carbon::create($this->anioHistorico, 12, 31)->endOfDay(),
            ];
        }

        $now = now();

        return match ($this->periodo) {
            'mes_actual' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'mes_anterior' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'trimestre' => [$now->copy()->subMonths(2)->startOfMonth(), $now->copy()->endOfMonth()],
            'semestre' => [$now->copy()->subMonths(5)->startOfMonth(), $now->copy()->endOfMonth()],
            'anio' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [null, null],
        };
    }

    public function tieneRango(): bool
    {
        [$desde] = $this->rangoFechas();

        return $desde !== null;
    }

    public function aplicarFecha(Builder $query, string $column): Builder
    {
        [$desde, $hasta] = $this->rangoFechas();
        if ($desde && $hasta) {
            $query->whereBetween($column, [
                $desde->copy()->startOfDay()->toDateTimeString(),
                $hasta->copy()->endOfDay()->toDateTimeString(),
            ]);
        }

        return $query;
    }

    public function aplicarCultivoEnLote(Builder $query, string $loteFk = 'loteid'): Builder
    {
        if ($this->loteId) {
            $query->where($loteFk, $this->loteId);
        } elseif ($this->cultivoId || $this->estadoLoteId) {
            $query->whereHas('lote', function (Builder $q) {
                if ($this->cultivoId) {
                    $q->where('cultivoid', $this->cultivoId);
                }
                if ($this->estadoLoteId) {
                    $q->where('estadolotetipoid', $this->estadoLoteId);
                }
            });
        }

        return $query;
    }

    public function aplicarEnLote(Builder $query): Builder
    {
        if ($this->loteId) {
            $query->where('loteid', $this->loteId);
        }
        if ($this->cultivoId) {
            $query->where('cultivoid', $this->cultivoId);
        }
        if ($this->estadoLoteId) {
            $query->where('estadolotetipoid', $this->estadoLoteId);
        }

        return $query;
    }

    /**
     * @return array<int, array{mes: int, año: int, nombre: string}>
     */
    public function mesesParaGrafico(): array
    {
        $mesesNombres = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        if ($this->usaRangoPersonalizado()) {
            return $this->mesesEntre(
                Carbon::parse($this->desde)->startOfMonth(),
                Carbon::parse($this->hasta)->endOfMonth(),
                $mesesNombres,
            );
        }

        if ($this->anioHistorico) {
            $meses = [];
            for ($m = 1; $m <= 12; $m++) {
                $meses[] = ['mes' => $m, 'año' => $this->anioHistorico, 'nombre' => $mesesNombres[$m - 1]];
            }

            return $meses;
        }

        [$desde, $hasta] = $this->rangoFechas();
        if ($desde && $hasta) {
            return $this->mesesEntre($desde->copy()->startOfMonth(), $hasta->copy()->endOfMonth(), $mesesNombres);
        }

        // Todo el historial: últimos 12 meses
        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $meses[] = ['mes' => $fecha->month, 'año' => $fecha->year, 'nombre' => $mesesNombres[$fecha->month - 1]];
        }

        return $meses;
    }

    /**
     * @return array<string, mixed>
     */
    public function queryParams(): array
    {
        return array_filter([
            'periodo' => $this->periodo,
            'anio' => $this->anioHistorico,
            'desde' => $this->desde,
            'hasta' => $this->hasta,
            'cultivo' => $this->cultivoId,
            'lote' => $this->loteId,
            'estado_lote' => $this->estadoLoteId,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @return array<int, array{mes: int, año: int, nombre: string}>
     */
    private function mesesEntre(Carbon $desde, Carbon $hasta, array $mesesNombres): array
    {
        $meses = [];
        $cursor = $desde->copy();
        while ($cursor <= $hasta) {
            $meses[] = [
                'mes' => $cursor->month,
                'año' => $cursor->year,
                'nombre' => $mesesNombres[$cursor->month - 1],
            ];
            $cursor->addMonth();
        }

        return $meses;
    }
}
