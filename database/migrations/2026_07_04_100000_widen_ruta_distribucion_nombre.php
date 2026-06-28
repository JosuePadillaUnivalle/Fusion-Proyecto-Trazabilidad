<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ruta_distribucion') || ! Schema::hasColumn('ruta_distribucion', 'nombre')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ruta_distribucion ALTER COLUMN nombre TYPE VARCHAR(255)');
        } elseif ($driver === 'sqlite') {
            // SQLite no admite ALTER COLUMN; el esquema local se corrige en migrate:fresh.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('ruta_distribucion') || ! Schema::hasColumn('ruta_distribucion', 'nombre')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ruta_distribucion ALTER COLUMN nombre TYPE VARCHAR(150)');
        }
    }
};
