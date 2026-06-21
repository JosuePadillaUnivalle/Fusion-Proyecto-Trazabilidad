<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuario') && ! Schema::hasColumn('usuario', 'tipo_licencia')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->string('tipo_licencia', 20)->nullable()->after('ci_nit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('usuario') && Schema::hasColumn('usuario', 'tipo_licencia')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('tipo_licencia');
            });
        }
    }
};
