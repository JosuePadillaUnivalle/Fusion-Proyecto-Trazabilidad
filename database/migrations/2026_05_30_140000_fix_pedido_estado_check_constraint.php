<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedido') || ! Schema::hasColumn('pedido', 'estado')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE pedido DROP CONSTRAINT IF EXISTS pedido_estado_check');
        DB::statement("ALTER TABLE pedido ADD CONSTRAINT pedido_estado_check CHECK (estado IN (
            'sin asignacion',
            'pendiente',
            'confirmado',
            'en produccion',
            'rechazado'
        ))");
    }

    public function down(): void
    {
        if (! Schema::hasTable('pedido') || Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE pedido DROP CONSTRAINT IF EXISTS pedido_estado_check');
        DB::statement("ALTER TABLE pedido ADD CONSTRAINT pedido_estado_check CHECK (estado IN (
            'pendiente',
            'confirmado',
            'en produccion',
            'rechazado'
        ))");
    }
};
