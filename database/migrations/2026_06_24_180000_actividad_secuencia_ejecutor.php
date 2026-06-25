<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('actividad')) {
            return;
        }

        Schema::table('actividad', function (Blueprint $table) {
            if (! Schema::hasColumn('actividad', 'orden_secuencia')) {
                $table->unsignedInteger('orden_secuencia')->nullable()->after('detalle_json');
            }
            if (! Schema::hasColumn('actividad', 'usuarioid_ejecutor')) {
                $table->unsignedBigInteger('usuarioid_ejecutor')->nullable()->after('usuarioid');
            }
        });

        $porLote = DB::table('actividad')
            ->select('loteid')
            ->distinct()
            ->pluck('loteid');

        foreach ($porLote as $loteid) {
            $ids = DB::table('actividad')
                ->where('loteid', $loteid)
                ->orderBy('fechainicio')
                ->orderBy('actividadid')
                ->pluck('actividadid');

            $orden = 1;
            foreach ($ids as $actividadid) {
                DB::table('actividad')
                    ->where('actividadid', $actividadid)
                    ->whereNull('orden_secuencia')
                    ->update(['orden_secuencia' => $orden++]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('actividad')) {
            return;
        }

        Schema::table('actividad', function (Blueprint $table) {
            if (Schema::hasColumn('actividad', 'orden_secuencia')) {
                $table->dropColumn('orden_secuencia');
            }
            if (Schema::hasColumn('actividad', 'usuarioid_ejecutor')) {
                $table->dropColumn('usuarioid_ejecutor');
            }
        });
    }
};
