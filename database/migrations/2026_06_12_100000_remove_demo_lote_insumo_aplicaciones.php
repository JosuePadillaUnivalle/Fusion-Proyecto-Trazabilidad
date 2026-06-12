<?php

use App\Support\RegistroDemo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loteinsumo')) {
            return;
        }

        RegistroDemo::limpiarLoteInsumosDemo();

        // Pruebas manuales sin marcador demo (mismo módulo de ejemplos).
        $pruebas = DB::table('loteinsumo')
            ->where(function ($q) {
                $q->whereNull('observaciones')->orWhere('observaciones', '');
            })
            ->whereIn('loteid', function ($sub) {
                $sub->select('loteid')
                    ->from('lote')
                    ->whereIn('nombre', [
                        'Lote Norte A1',
                        'Lote Sur C3',
                        'Lote Central D4',
                        'Lote de Papa',
                        'Lote De Tomate',
                        'Lote F1',
                        'Lote Este B2',
                        'Lote Oeste E5',
                    ]);
            })
            ->get();

        foreach ($pruebas as $row) {
            if (Schema::hasTable('insumo')) {
                DB::table('insumo')
                    ->where('insumoid', $row->insumoid)
                    ->increment('stock', $row->cantidadusada);
            }

            DB::table('loteinsumo')->where('loteinsumoid', $row->loteinsumoid)->delete();
        }
    }

    public function down(): void
    {
        // No reversible: datos de demostración eliminados a propósito.
    }
};
