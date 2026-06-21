<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('actividad')) {
            return;
        }

        Schema::table('actividad', function (Blueprint $table) {
            if (! Schema::hasColumn('actividad', 'evidencia_foto_path')) {
                $table->string('evidencia_foto_path', 500)->nullable()->after('observaciones');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('actividad')) {
            return;
        }

        Schema::table('actividad', function (Blueprint $table) {
            if (Schema::hasColumn('actividad', 'evidencia_foto_path')) {
                $table->dropColumn('evidencia_foto_path');
            }
        });
    }
};
