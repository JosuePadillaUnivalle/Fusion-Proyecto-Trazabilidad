<?php

namespace App\Support;

use App\Models\Almacen;
use App\Models\Insumo;

final class AlmacenEliminacionCatalogo
{
    /** @return array{ok: bool, titulo: string, mensaje: string} */
    public static function evaluar(Almacen $almacen): array
    {
        $stock = (float) Insumo::query()
            ->where('almacenid', $almacen->almacenid)
            ->sum('stock');

        if ($stock > 0) {
            return [
                'ok' => false,
                'titulo' => 'Almacén con stock',
                'mensaje' => 'No se puede eliminar «'.$almacen->nombre.'» mientras tenga inventario. '
                    .'Traslade o consuma todos los productos antes de eliminar el almacén.',
            ];
        }

        return ['ok' => true, 'titulo' => '', 'mensaje' => ''];
    }
}
