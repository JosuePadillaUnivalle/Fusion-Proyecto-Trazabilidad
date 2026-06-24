<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RestaurarDatosLocalesCommand extends Command
{
    protected $signature = 'agrofusion:restaurar-datos-locales {--force : Sobrescribir aunque ya haya usuarios}';

    protected $description = 'Restaura database.sqlite desde database.snapshot.sqlite (datos reales con lotes y usuarios)';

    public function handle(): int
    {
        $destino = database_path('database.sqlite');
        $origen = database_path('database.snapshot.sqlite');

        if (! is_file($origen)) {
            $this->error('No existe database/database.snapshot.sqlite');
            $this->line('Restaura manualmente: git checkout 0dd37c7 -- database/database.sqlite');

            return self::FAILURE;
        }

        $usuarios = 0;
        if (is_file($destino)) {
            try {
                $usuarios = (int) DB::table('usuario')->count();
            } catch (\Throwable) {
                $usuarios = 0;
            }
        }

        if ($usuarios > 0 && ! $this->option('force')) {
            $this->info("La base ya tiene {$usuarios} usuario(s). Nada que hacer.");
            $this->line('Usa --force si quieres sobrescribir con el snapshot.');

            return self::SUCCESS;
        }

        if (! copy($origen, $destino)) {
            $this->error('No se pudo copiar el snapshot a database.sqlite');

            return self::FAILURE;
        }

        \Illuminate\Support\Facades\DB::purge('sqlite');
        \Illuminate\Support\Facades\DB::reconnect('sqlite');

        \App\Support\LocalDatabaseGuard::normalizarCredencialesDemo();

        $this->info('Base de datos restaurada desde database.snapshot.sqlite');
        $this->call('agrofusion:reparar-permisos');
        $this->call('agrofusion:asegurar-datos-demo');

        $total = (int) DB::table('usuario')->count();
        $lotes = (int) DB::table('lote')->count();
        $this->newLine();
        $this->info("Listo: {$total} usuarios, {$lotes} lotes.");
        $this->line('  admin@agrofusion.com / 12345');
        $this->line('  LuisGuerrero123@gmail.com / 12345');

        return self::SUCCESS;
    }
}
