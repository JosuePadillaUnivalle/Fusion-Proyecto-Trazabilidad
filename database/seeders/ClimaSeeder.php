<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClimaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Limpiar datos anteriores de prueba (opcional)
        // DB::table('clima')->whereNull('loteid')->delete();

        $data = [];
        $dias = 30; // Generar ultimos 30 dias

        for ($i = 0; $i < $dias; $i++) {
            $fecha = Carbon::now()->subDays($i);

            // Simular variaciones simples
            $temp = 25 + rand(-5, 5);
            $hum = 60 + rand(-10, 20);

            $data[] = [
                'loteid' => \App\Models\Lote::first()->loteid ?? 1,
                'fecha' => $fecha->format('Y-m-d H:i:s'),
                'temperatura' => $temp,
                'humedad' => $hum,
                'lluvia' => rand(0, 10) > 8 ? rand(5, 50) : 0, // 20% prob lluvia
                'observaciones' => 'Registro generado automáticamente',
            ];
        }

        DB::table('clima')->insert($data);
    }
}
