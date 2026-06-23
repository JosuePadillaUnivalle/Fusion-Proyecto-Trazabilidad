<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ConsolidacionRolesPermisosSeeder::class,
            RolePermissionSeeder::class,
            CatalogosOperacionAgricolaSeeder::class,
            AdminUserSeeder::class,
            DatosPruebaSeeder::class,
            // DemoCatalogosBaseSeeder::class, // Ejecutar manualmente por bloque cuando se requiera
            // DemoUsuariosAlmacenesActoresSeeder::class, // Ejecutar manualmente por bloque cuando se requiera
            // DemoInventarioMovimientosSeeder::class, // Ejecutar manualmente por bloque cuando se requiera
            // DemoLotesProduccionActividadesSeeder::class, // Ejecutar manualmente por bloque cuando se requiera
            // DemoPedidosVentasCertificacionesSeeder::class, // Ejecutar manualmente por bloque cuando se requiera
            // DemoEnviosAsignacionesRutasSeeder::class, // Ejecutar manualmente por bloque cuando se requiera
            // DemoReportesPanelesFinalSeeder::class, // Ejecutar manualmente por bloque cuando se requiera
            // DemoProduccionInventarioExtraSeeder::class, // Ejecutar manualmente por bloque cuando se requiera
            // DemoEnviosCatalogosExtraSeeder::class, // Ejecutar manualmente por bloque cuando se requiera
            // ClimaSeeder::class, // Uncomment if needed
        ]);
    }
}
