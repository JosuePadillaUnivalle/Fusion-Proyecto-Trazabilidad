<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // En SQLite la columna imagenurl ya está definida como TEXT.
            return;
        }

        // Tabla usuario
        if (Schema::hasTable('usuario')) {
            DB::statement('ALTER TABLE usuario ALTER COLUMN imagenurl TYPE TEXT');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('usuario')) {
            DB::statement('ALTER TABLE usuario ALTER COLUMN imagenurl TYPE VARCHAR(255)');
        }
    }
};
