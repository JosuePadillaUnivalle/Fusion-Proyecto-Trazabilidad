<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** @var array<string, string> nombre legacy => nombre canónico */
    private array $aliases = [
        'Devolucion' => 'Devolución',
        'Produccion recibida' => 'Producción recibida',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('tipo_movimiento_almacen')) {
            return;
        }

        foreach ($this->aliases as $legacy => $canonical) {
            $this->mergeTipo($legacy, $canonical);
        }

        $this->dedupeByNormalizedName();
    }

    public function down(): void
    {
        // No reversible de forma segura sin perder historial.
    }

    private function mergeTipo(string $legacy, string $canonical): void
    {
        $legacyRow = DB::table('tipo_movimiento_almacen')->where('nombre', $legacy)->first();
        if (! $legacyRow) {
            return;
        }

        $canonicalRow = DB::table('tipo_movimiento_almacen')
            ->where('nombre', $canonical)
            ->where('naturaleza', $legacyRow->naturaleza)
            ->first();

        if ($canonicalRow && (int) $canonicalRow->tipo_movimiento_almacenid !== (int) $legacyRow->tipo_movimiento_almacenid) {
            if (Schema::hasTable('almacen_movimiento')) {
                DB::table('almacen_movimiento')
                    ->where('tipo_movimiento_almacenid', $legacyRow->tipo_movimiento_almacenid)
                    ->update(['tipo_movimiento_almacenid' => $canonicalRow->tipo_movimiento_almacenid]);
            }
            DB::table('tipo_movimiento_almacen')
                ->where('tipo_movimiento_almacenid', $legacyRow->tipo_movimiento_almacenid)
                ->delete();
        } else {
            DB::table('tipo_movimiento_almacen')
                ->where('tipo_movimiento_almacenid', $legacyRow->tipo_movimiento_almacenid)
                ->update(['nombre' => $canonical, 'updated_at' => now()]);
        }
    }

    private function dedupeByNormalizedName(): void
    {
        $rows = DB::table('tipo_movimiento_almacen')->orderBy('tipo_movimiento_almacenid')->get();
        $keepers = [];

        foreach ($rows as $row) {
            $key = Str::ascii(strtolower(trim($row->nombre))).'|'.$row->naturaleza;
            if (! isset($keepers[$key])) {
                $keepers[$key] = $row;

                continue;
            }

            $keep = $keepers[$key];
            if (Schema::hasTable('almacen_movimiento')) {
                DB::table('almacen_movimiento')
                    ->where('tipo_movimiento_almacenid', $row->tipo_movimiento_almacenid)
                    ->update(['tipo_movimiento_almacenid' => $keep->tipo_movimiento_almacenid]);
            }
            DB::table('tipo_movimiento_almacen')
                ->where('tipo_movimiento_almacenid', $row->tipo_movimiento_almacenid)
                ->delete();
        }
    }
};
