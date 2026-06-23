<?php

use App\Support\RutaDistribucionCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ruta_distribucion')) {
            return;
        }

        $rutasPdv = DB::table('ruta_distribucion as r')
            ->join('ruta_distribucion_parada as p', 'p.rutadistribucionid', '=', 'r.rutadistribucionid')
            ->where('p.tipo', RutaDistribucionCatalogo::PARADA_ENTREGA_PDV)
            ->whereNull('r.almacen_mayorista_destinoid')
            ->where(function ($q) {
                $q->whereNull('r.tipo_ruta')
                    ->orWhere('r.tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_MAYORISTA_PDV);
            })
            ->distinct()
            ->pluck('r.rutadistribucionid');

        foreach ($rutasPdv as $rutaId) {
            $ruta = DB::table('ruta_distribucion')->where('rutadistribucionid', $rutaId)->first();
            if ($ruta === null) {
                continue;
            }

            $origenMayorista = $ruta->almacen_mayorista_origenid;
            if ($origenMayorista === null && $ruta->almacen_planta_origenid) {
                $origenMayorista = $ruta->almacen_planta_origenid;
                DB::table('ruta_distribucion')
                    ->where('rutadistribucionid', $rutaId)
                    ->update([
                        'almacen_mayorista_origenid' => $origenMayorista,
                        'almacen_planta_origenid' => null,
                        'tipo_ruta' => RutaDistribucionCatalogo::TIPO_RUTA_MAYORISTA_PDV,
                    ]);
            }

            DB::table('ruta_distribucion_parada')
                ->where('rutadistribucionid', $rutaId)
                ->where('tipo', RutaDistribucionCatalogo::PARADA_CARGA_PLANTA)
                ->update(['tipo' => RutaDistribucionCatalogo::PARADA_CARGA_MAYORISTA]);
        }
    }

    public function down(): void
    {
        // Corrección de datos operativos; no se revierte automáticamente.
    }
};
