<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuario') && !Schema::hasColumn('usuario', 'role')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->string('role', 50)->nullable()->after('passwordhash');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('usuario') && Schema::hasColumn('usuario', 'role')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};

