<?php

use App\Support\CultivoCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cultivo')) {
            return;
        }

        if (! Schema::hasColumn('cultivo', 'detalle')) {
            Schema::table('cultivo', function (Blueprint $table) {
                $table->string('detalle', 500)->nullable()->after('nombre');
            });
        }

        foreach (DB::table('cultivo')->get(['cultivoid', 'nombre', 'detalle']) as $row) {
            if (! empty($row->detalle)) {
                continue;
            }
            $detalle = CultivoCatalogo::detallePorNombre($row->nombre);
            if ($detalle) {
                DB::table('cultivo')->where('cultivoid', $row->cultivoid)->update(['detalle' => $detalle]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cultivo') && Schema::hasColumn('cultivo', 'detalle')) {
            Schema::table('cultivo', function (Blueprint $table) {
                $table->dropColumn('detalle');
            });
        }
    }
};
