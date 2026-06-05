<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuario') && ! Schema::hasColumn('usuario', 'supervisor_usuarioid')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->unsignedBigInteger('supervisor_usuarioid')->nullable()->after('role');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('usuario') && Schema::hasColumn('usuario', 'supervisor_usuarioid')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('supervisor_usuarioid');
            });
        }
    }
};
