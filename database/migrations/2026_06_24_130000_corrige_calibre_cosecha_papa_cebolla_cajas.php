<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * En campo la cosecha se almacena en cajas; el saco es empaque de traslado agrícola→planta.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('catalogo_tamano_conteo') || ! Schema::hasTable('insumo') || ! Schema::hasTable('tipo_empaque')) {
            return;
        }

        $cajaId = DB::table('tipo_empaque')->where('nombre', 'Caja de cartón')->value('tipoempaqueid');
        $sacoId = DB::table('tipo_empaque')->where('nombre', 'Saco')->value('tipoempaqueid');

        if (! $cajaId || ! $sacoId) {
            return;
        }

        $insumoIds = DB::table('insumo')
            ->where(function ($q) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ['%papa%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%cebolla%']);
            })
            ->pluck('insumoid');

        if ($insumoIds->isEmpty()) {
            return;
        }

        DB::table('catalogo_tamano_conteo')
            ->where('tipoempaqueid', $sacoId)
            ->whereIn('insumoid', $insumoIds)
            ->update(['tipoempaqueid' => $cajaId]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('catalogo_tamano_conteo') || ! Schema::hasTable('insumo') || ! Schema::hasTable('tipo_empaque')) {
            return;
        }

        $cajaId = DB::table('tipo_empaque')->where('nombre', 'Caja de cartón')->value('tipoempaqueid');
        $sacoId = DB::table('tipo_empaque')->where('nombre', 'Saco')->value('tipoempaqueid');

        if (! $cajaId || ! $sacoId) {
            return;
        }

        $insumoIds = DB::table('insumo')
            ->where(function ($q) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ['%papa%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%cebolla%']);
            })
            ->pluck('insumoid');

        if ($insumoIds->isEmpty()) {
            return;
        }

        DB::table('catalogo_tamano_conteo')
            ->where('tipoempaqueid', $cajaId)
            ->whereIn('insumoid', $insumoIds)
            ->update(['tipoempaqueid' => $sacoId]);
    }
};
