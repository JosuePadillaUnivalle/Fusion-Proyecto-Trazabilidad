<?php

namespace Database\Seeders;

use App\Models\ProduccionAlmacenamiento;
use Illuminate\Database\Seeder;

class BackfillProduccionAlmacenamientoClimaRealSeeder extends Seeder
{
    public function run(): void
    {
        $rows = ProduccionAlmacenamiento::query()
            ->with(['produccion.lote.cultivo', 'almacen.tipoAlmacen'])
            ->where('temperatura', 18)
            ->where('humedad', 60)
            ->where('temperatura_min', 12)
            ->where('temperatura_max', 24)
            ->where('humedad_min', 50)
            ->where('humedad_max', 70)
            ->get();

        $actualizados = 0;
        foreach ($rows as $r) {
            $cultivo = strtolower((string) optional(optional(optional($r->produccion)->lote)->cultivo)->nombre);
            $tipoAlmacen = strtolower((string) optional(optional($r->almacen)->tipoAlmacen)->nombre);

            // Valores por contexto real del proyecto (cultivos observados: tomate, papa, lechuga, cebolla, maiz).
            if (str_contains($cultivo, 'lechuga')) {
                $v = [4, 95, 2, 6, 90, 98];
            } elseif (str_contains($cultivo, 'tomate')) {
                $v = [12, 88, 10, 15, 85, 95];
            } elseif (str_contains($cultivo, 'papa')) {
                $v = [10, 75, 7, 13, 65, 85];
            } elseif (str_contains($cultivo, 'cebolla')) {
                $v = [7, 68, 4, 10, 60, 75];
            } elseif (str_contains($cultivo, 'maiz') || str_contains($cultivo, 'maíz')) {
                $v = [16, 55, 12, 20, 45, 65];
            } elseif (str_contains($tipoAlmacen, 'planta')) {
                $v = [8, 85, 4, 12, 75, 95];
            } elseif (str_contains($tipoAlmacen, 'secundario')) {
                $v = [20, 58, 16, 26, 48, 68];
            } else {
                $v = [18, 60, 14, 24, 50, 70];
            }

            $r->update([
                'temperatura' => $v[0],
                'humedad' => $v[1],
                'temperatura_min' => $v[2],
                'temperatura_max' => $v[3],
                'humedad_min' => $v[4],
                'humedad_max' => $v[5],
            ]);

            $actualizados++;
        }

        $this->command?->info("Backfill clima real aplicado a {$actualizados} registros.");
    }
}

