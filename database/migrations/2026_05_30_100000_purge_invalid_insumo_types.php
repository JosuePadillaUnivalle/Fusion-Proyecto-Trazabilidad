<?php

use App\Support\InsumoCatalogo;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        InsumoCatalogo::purgarInsumosConTipoInvalido();
    }

    public function down(): void
    {
        // No reversible: datos de demo con tipos obsoletos.
    }
};
