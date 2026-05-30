<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('almacen') || ! Schema::hasColumn('almacen', 'tipoalmacenid')) {
            return;
        }

        Schema::table('almacen', function (Blueprint $table) {
            $table->unsignedBigInteger('tipoalmacenid')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('almacen') || ! Schema::hasColumn('almacen', 'tipoalmacenid')) {
            return;
        }

        Schema::table('almacen', function (Blueprint $table) {
            $table->unsignedBigInteger('tipoalmacenid')->nullable(false)->change();
        });
    }
};
