<?php

namespace App\Console\Commands;

use App\Support\LocalDatabaseGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProtegerBdLocalCommand extends Command
{
    protected $signature = 'agrofusion:proteger-bd-local';

    protected $description = 'Restaura database.sqlite desde el snapshot si la base local quedó sin usuarios';

    public function handle(): int
    {
        if (! LocalDatabaseGuard::debeProteger()) {
            return self::SUCCESS;
        }

        if (LocalDatabaseGuard::asegurar(silencioso: false)) {
            $total = (int) DB::table('usuario')->count();
            $lotes = Schema::hasTable('lote') ? (int) DB::table('lote')->count() : 0;
            $this->warn('La base local estaba vacía. Se restauró desde database.snapshot.sqlite.');
            $this->info("Listo: {$total} usuarios, {$lotes} lotes.");
            $this->line('  admin@agrofusion.com / 12345');
            $this->line('  LuisGuerrero123@gmail.com / 12345');

            return self::SUCCESS;
        }

        return self::SUCCESS;
    }
}
