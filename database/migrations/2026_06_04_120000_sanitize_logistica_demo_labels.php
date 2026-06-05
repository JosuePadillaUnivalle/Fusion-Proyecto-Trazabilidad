<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function limpiarEtiqueta(?string $texto): ?string
    {
        if ($texto === null || $texto === '') {
            return $texto;
        }

        $limpio = preg_replace(
            '/\[(?:DEMO(?:-[A-Z0-9]+)?|MOD-(?:PANEL|LOG|ENV)(?:-[A-Z0-9]+)?|DEMO-XTRA\d*)\]\s*/i',
            '',
            $texto
        );

        return trim($limpio ?? $texto);
    }

    public function up(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('documento_entrega')) {
            foreach (DB::table('documento_entrega')->get(['documentoentregaid', 'titulo']) as $row) {
                $titulo = $this->limpiarEtiqueta($row->titulo);
                if ($titulo !== $row->titulo) {
                    DB::table('documento_entrega')
                        ->where('documentoentregaid', $row->documentoentregaid)
                        ->update(['titulo' => $titulo]);
                }
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('incidente_envio')) {
            foreach (DB::table('incidente_envio')->get(['incidenteenvioid', 'descripcion', 'nota_resolucion']) as $row) {
                $updates = [];
                $desc = $this->limpiarEtiqueta($row->descripcion);
                if ($desc !== $row->descripcion) {
                    $updates['descripcion'] = $desc;
                }
                $nota = $this->limpiarEtiqueta($row->nota_resolucion);
                if ($nota !== $row->nota_resolucion) {
                    $updates['nota_resolucion'] = $nota;
                }
                if ($updates !== []) {
                    DB::table('incidente_envio')
                        ->where('incidenteenvioid', $row->incidenteenvioid)
                        ->update($updates);
                }
            }
        }
    }

    public function down(): void
    {
        // No reversible: solo limpieza de etiquetas demo en textos visibles.
    }
};
