<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuario')) {
            Schema::table('usuario', function (Blueprint $table) {
                if (! Schema::hasColumn('usuario', 'bienvenida_vista')) {
                    $table->boolean('bienvenida_vista')->default(true)->after('fecha_revision');
                }
                if (! Schema::hasColumn('usuario', 'nombreusuario_editado')) {
                    $table->boolean('nombreusuario_editado')->default(false)->after('bienvenida_vista');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('usuario')) {
            Schema::table('usuario', function (Blueprint $table) {
                foreach (['bienvenida_vista', 'nombreusuario_editado'] as $col) {
                    if (Schema::hasColumn('usuario', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
