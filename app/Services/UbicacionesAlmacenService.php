<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\DireccionLogistica;
use App\Models\Lote;
use App\Models\Pedido;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UbicacionesAlmacenService
{
    /**
     * @return list<array{grupo: string, items: list<array{valor: string, etiqueta: string, detalle: string, direccionlogisticaid: int|null}>}>
     */
    public function listarParaFormulario(?int $excluirAlmacenId = null): array
    {
        $vistos = [];
        $grupos = [
            'Direcciones logísticas' => collect(),
            'Ubicaciones de otros almacenes' => collect(),
            'Lotes y parcelas' => collect(),
            'Direcciones de pedidos' => collect(),
            'Áreas frecuentes' => collect(),
        ];

        if (Schema::hasTable('direccion_logistica')) {
            DireccionLogistica::query()
                ->when(Schema::hasColumn('direccion_logistica', 'activo'), fn ($q) => $q->where('activo', true))
                ->orderBy('nombre')
                ->get()
                ->each(function (DireccionLogistica $dir) use (&$grupos, &$vistos) {
                    $valor = $this->textoDireccionLogistica($dir);
                    if ($this->registrar($vistos, $valor)) {
                        $grupos['Direcciones logísticas']->push([
                            'valor' => $valor,
                            'etiqueta' => $dir->nombre,
                            'detalle' => trim(($dir->ciudad ?? '').($dir->referencia ? ' · '.$dir->referencia : '')),
                            'direccionlogisticaid' => (int) $dir->direccionlogisticaid,
                        ]);
                    }
                });
        }

        if (Schema::hasTable('almacen')) {
            Almacen::query()
                ->when($excluirAlmacenId, fn ($q) => $q->where('almacenid', '!=', $excluirAlmacenId))
                ->whereNotNull('ubicacion')
                ->where('ubicacion', '!=', '')
                ->orderBy('nombre')
                ->get(['almacenid', 'nombre', 'ubicacion'])
                ->each(function (Almacen $alm) use (&$grupos, &$vistos) {
                    $valor = trim((string) $alm->ubicacion);
                    if ($this->registrar($vistos, $valor)) {
                        $grupos['Ubicaciones de otros almacenes']->push([
                            'valor' => $valor,
                            'etiqueta' => $valor,
                            'detalle' => 'Almacén: '.$alm->nombre,
                            'direccionlogisticaid' => null,
                        ]);
                    }
                });
        }

        if (Schema::hasTable('lote')) {
            Lote::query()
                ->whereNotNull('ubicacion')
                ->where('ubicacion', '!=', '')
                ->orderByDesc('fechamodificacion')
                ->limit(40)
                ->get(['loteid', 'nombre', 'ubicacion'])
                ->each(function (Lote $lote) use (&$grupos, &$vistos) {
                    $valor = trim((string) $lote->ubicacion);
                    if ($this->registrar($vistos, $valor)) {
                        $grupos['Lotes y parcelas']->push([
                            'valor' => $valor,
                            'etiqueta' => $valor,
                            'detalle' => 'Lote: '.$lote->nombre,
                            'direccionlogisticaid' => null,
                        ]);
                    }
                });
        }

        if (Schema::hasTable('pedido') && Schema::hasColumn('pedido', 'direccion_texto')) {
            Pedido::query()
                ->whereNotNull('direccion_texto')
                ->where('direccion_texto', '!=', '')
                ->orderByDesc('pedidoid')
                ->limit(30)
                ->get(['pedidoid', 'numero_solicitud', 'direccion_texto', 'nombre_planta'])
                ->each(function (Pedido $pedido) use (&$grupos, &$vistos) {
                    $valor = trim((string) $pedido->direccion_texto);
                    if ($this->registrar($vistos, $valor)) {
                        $grupos['Direcciones de pedidos']->push([
                            'valor' => $valor,
                            'etiqueta' => $valor,
                            'detalle' => 'Pedido '.($pedido->numero_solicitud ?? '#'.$pedido->pedidoid)
                                .($pedido->nombre_planta ? ' · '.$pedido->nombre_planta : ''),
                            'direccionlogisticaid' => null,
                        ]);
                    }
                });
        }

        foreach (config('almacenes.ubicaciones_sugeridas', []) as $sugerida) {
            $valor = trim((string) $sugerida);
            if ($valor !== '' && $this->registrar($vistos, $valor)) {
                $grupos['Áreas frecuentes']->push([
                    'valor' => $valor,
                    'etiqueta' => $valor,
                    'detalle' => 'Sugerencia del sistema',
                    'direccionlogisticaid' => null,
                ]);
            }
        }

        $out = [];
        foreach ($grupos as $nombre => $items) {
            if ($items->isNotEmpty()) {
                $out[] = [
                    'grupo' => $nombre,
                    'items' => $items->sortBy('etiqueta', SORT_NATURAL | SORT_FLAG_CASE)->values()->all(),
                ];
            }
        }

        return $out;
    }

    private function textoDireccionLogistica(DireccionLogistica $dir): string
    {
        $partes = array_filter([
            trim((string) $dir->direccion_completa),
            trim((string) $dir->ciudad),
        ]);

        if ($partes !== []) {
            return implode(', ', $partes);
        }

        return trim((string) $dir->nombre);
    }

    private function registrar(array &$vistos, string $valor): bool
    {
        $valor = trim($valor);
        if ($valor === '') {
            return false;
        }

        $clave = Str::lower($valor);
        if (isset($vistos[$clave])) {
            return false;
        }

        $vistos[$clave] = true;

        return true;
    }
}
