<?php

namespace App\Console\Commands;

use Database\Seeders\AdminUserSeeder;
use Database\Seeders\CatalogosOperacionAgricolaSeeder;
use Database\Seeders\ConsolidacionRolesPermisosSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class RepararPermisosAgrofusionCommand extends Command
{
    protected $signature = 'agrofusion:reparar-permisos';

    protected $description = 'Sincroniza roles Spatie, permisos y usuarios demo (soluciona errores 403 tras clonar)';

    public function handle(): int
    {
        $this->info('Sincronizando roles y permisos...');

        $this->call('db:seed', ['--class' => RoleSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => ConsolidacionRolesPermisosSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => CatalogosOperacionAgricolaSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->newLine();
        $this->info('Permisos reparados.');
        $this->line('  Admin: admin@agrofusion.com / 12345');

        return self::SUCCESS;
    }
}
