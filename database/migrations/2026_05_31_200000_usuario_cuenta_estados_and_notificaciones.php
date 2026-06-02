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
                if (! Schema::hasColumn('usuario', 'estado_cuenta')) {
                    $table->string('estado_cuenta', 20)->default('aprobado')->after('activo');
                }
                if (! Schema::hasColumn('usuario', 'ci_nit')) {
                    $table->string('ci_nit', 30)->nullable()->unique()->after('telefono');
                }
                if (! Schema::hasColumn('usuario', 'carta_motivacion')) {
                    $table->text('carta_motivacion')->nullable()->after('informacionadicional');
                }
                if (! Schema::hasColumn('usuario', 'rol_solicitado')) {
                    $table->string('rol_solicitado', 50)->nullable()->after('carta_motivacion');
                }
                if (! Schema::hasColumn('usuario', 'motivo_rechazo')) {
                    $table->text('motivo_rechazo')->nullable()->after('rol_solicitado');
                }
                if (! Schema::hasColumn('usuario', 'revisado_por')) {
                    $table->unsignedBigInteger('revisado_por')->nullable()->after('motivo_rechazo');
                }
                if (! Schema::hasColumn('usuario', 'fecha_revision')) {
                    $table->dateTime('fecha_revision')->nullable()->after('revisado_por');
                }
            });
        }

        if (! Schema::hasTable('usuario_notificacion')) {
            Schema::create('usuario_notificacion', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('usuarioid');
                $table->string('tipo', 60);
                $table->string('titulo', 200);
                $table->text('mensaje')->nullable();
                $table->string('enlace', 500)->nullable();
                $table->string('referencia_tipo', 80)->nullable();
                $table->unsignedBigInteger('referencia_id')->nullable();
                $table->dateTime('leida_at')->nullable();
                $table->dateTime('creado_en');

                $table->index(['usuarioid', 'leida_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_notificacion');

        if (Schema::hasTable('usuario')) {
            Schema::table('usuario', function (Blueprint $table) {
                foreach (['estado_cuenta', 'ci_nit', 'carta_motivacion', 'rol_solicitado', 'motivo_rechazo', 'revisado_por', 'fecha_revision'] as $col) {
                    if (Schema::hasColumn('usuario', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
