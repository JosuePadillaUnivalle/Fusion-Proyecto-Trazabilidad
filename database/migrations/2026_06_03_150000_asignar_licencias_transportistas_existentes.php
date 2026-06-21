<?php

use App\Support\TiposLicenciaBolivia;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuario')) {
            return;
        }

        $codigos = TiposLicenciaBolivia::codigos();
        if ($codigos === []) {
            $codigos = ['A', 'B', 'C', 'M', 'P', 'T'];
        }

        $transportistas = DB::table('usuario')
            ->where('role', 'transportista')
            ->orderBy('usuarioid')
            ->get(['usuarioid', 'tipo_licencia']);

        foreach ($transportistas as $i => $t) {
            $tipo = $codigos[$i % count($codigos)];
            $numLic = sprintf('%s-%07d', $tipo, 1_000_000 + (int) $t->usuarioid);

            if (Schema::hasColumn('usuario', 'tipo_licencia') && empty($t->tipo_licencia)) {
                DB::table('usuario')
                    ->where('usuarioid', $t->usuarioid)
                    ->update(['tipo_licencia' => $tipo]);
            }

            if (! Schema::hasTable('perfil_transportista')) {
                continue;
            }

            $perfil = DB::table('perfil_transportista')
                ->where('usuarioid', $t->usuarioid)
                ->first();

            $datosPerfil = [];
            if ($perfil) {
                if (empty($perfil->tipo_licencia)) {
                    $datosPerfil['tipo_licencia'] = $tipo;
                }
                if (empty($perfil->licencia)) {
                    $datosPerfil['licencia'] = $numLic;
                }
                if ($datosPerfil !== []) {
                    DB::table('perfil_transportista')
                        ->where('usuarioid', $t->usuarioid)
                        ->update($datosPerfil);
                }
            } else {
                DB::table('perfil_transportista')->insert([
                    'usuarioid' => $t->usuarioid,
                    'tipo_licencia' => $tipo,
                    'licencia' => $numLic,
                    'disponible' => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Sin reversión: las licencias asignadas pueden haberse editado manualmente.
    }
};
