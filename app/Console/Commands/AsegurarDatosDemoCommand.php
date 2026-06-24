<?php

namespace App\Console\Commands;

use Database\Seeders\AlmacenPiraiStockSeeder;
use Database\Seeders\MaquinaVariablePlantaSeeder;
use Database\Seeders\PdvMercadoAlvaroCompletoSeeder;
use Database\Seeders\PdvInventarioDemoSeeder;
use Database\Seeders\PlantillaPasoVariableSeeder;
use Database\Seeders\ProductosTerminadosPlantaMayoristaSeeder;
use Illuminate\Console\Command;

class AsegurarDatosDemoCommand extends Command
{
    protected $signature = 'agrofusion:asegurar-datos-demo';

    protected $description = 'Restaura stock demo en planta, mayorista y puntos de venta sin borrar inventario existente';

    public function handle(): int
    {
        $this->info('Asegurando inventario demo en planta, almacén mayorista y puntos de venta…');

        $this->call('db:seed', ['--class' => ProductosTerminadosPlantaMayoristaSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => AlmacenPiraiStockSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => PdvMercadoAlvaroCompletoSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => PdvInventarioDemoSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => MaquinaVariablePlantaSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => PlantillaPasoVariableSeeder::class, '--force' => true]);
        $this->call('agrofusion:migrar-rutas-lotes');

        $this->newLine();
        $this->info('Inventario demo listo (planta + mayorista + PDV).');

        return self::SUCCESS;
    }
}
