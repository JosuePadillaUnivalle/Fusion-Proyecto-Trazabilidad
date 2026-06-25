<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuario') || ! Schema::hasColumn('usuario', 'telefono')) {
            return;
        }

        DB::table('usuario')
            ->where(function ($q) {
                $q->where('email', 'LuisGuerrero123@gmail.com')
                    ->orWhere('nombreusuario', 'lguerrero8718');
            })
            ->update(['telefono' => '79638529']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('usuario') || ! Schema::hasColumn('usuario', 'telefono')) {
            return;
        }

        DB::table('usuario')
            ->where(function ($q) {
                $q->where('email', 'LuisGuerrero123@gmail.com')
                    ->orWhere('nombreusuario', 'lguerrero8718');
            })
            ->where('telefono', '79638529')
            ->update(['telefono' => null]);
    }
};
