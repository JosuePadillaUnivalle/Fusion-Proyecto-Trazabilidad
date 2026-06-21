<?php

use App\Services\AlmacenRenombradoService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(AlmacenRenombradoService::class)->normalizarTodos();
    }

    public function down(): void
    {
        // No reversible: los nombres anteriores no se conservan.
    }
};
