<?php

use App\Support\DocumentoEntregaArchivo;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DocumentoEntregaArchivo::materializarTodosFaltantes(true);
    }

    public function down(): void
    {
        // No reversible.
    }
};
