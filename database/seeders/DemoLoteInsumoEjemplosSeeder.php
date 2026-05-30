<?php

namespace Database\Seeders;

use App\Models\EstadoLoteInsumo;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteInsumo;
use App\Models\Usuario;
use App\Support\InsumoCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class DemoLoteInsumoEjemplosSeeder extends Seeder
{
    public const MARK = '[demo-lote-insumo-ejemplo]';

    public function run(): void
    {
        if (! Schema::hasTable('loteinsumo')) {
            return;
        }

        InsumoCatalogo::asegurarCatalogosBase();

        $estadoAplicado = EstadoLoteInsumo::firstOrCreate(
            ['nombre' => 'Aplicado'],
            ['nombre' => 'Aplicado']
        );

        $usuario = Usuario::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'agricultor'))
            ->orWhere('role', 'agricultor')
            ->first()
            ?? Usuario::query()->first();

        if (! $usuario) {
            $this->command?->warn('DemoLoteInsumoEjemplosSeeder: sin usuario.');

            return;
        }

        $lotes = Lote::orderBy('nombre')->limit(10)->get();
        $insumos = Insumo::query()
            ->whereIn('tipoinsumoid', InsumoCatalogo::tiposValidosIds())
            ->orderBy('nombre')
            ->get();

        if ($lotes->isEmpty() || $insumos->isEmpty()) {
            $this->command?->warn('DemoLoteInsumoEjemplosSeeder: faltan lotes o insumos válidos.');

            return;
        }

        $cantidades = [12.5, 8, 25, 3.5, 15, 6, 20, 4, 18, 10];
        $diasAtras = [1, 2, 3, 4, 5, 6, 7, 10, 12, 14];
        $notas = [
            'Fertilización de arranque.',
            'Aplicación foliar preventiva.',
            'Control de malezas en surco.',
            'Refuerzo nutricional media temporada.',
            'Tratamiento fungicida programado.',
            'Aplicación en zona norte del lote.',
            'Dosis de mantenimiento post-riego.',
            'Aplicación con equipo de aspersión.',
            'Refuerzo antes de evaluación de cosecha.',
            'Registro de insumo de apoyo al cultivo.',
        ];

        for ($i = 0; $i < 10; $i++) {
            $lote = $lotes[$i % $lotes->count()];
            $insumo = $insumos[$i % $insumos->count()];
            $marcador = self::MARK.'|'.$i;

            LoteInsumo::updateOrCreate(
                ['observaciones' => $marcador],
                [
                    'loteid' => $lote->loteid,
                    'insumoid' => $insumo->insumoid,
                    'usuarioid' => $usuario->usuarioid,
                    'cantidadusada' => $cantidades[$i],
                    'fechauo' => Carbon::now()->subDays($diasAtras[$i]),
                    'costototal' => 0,
                    'estadoloteinsumoid' => $estadoAplicado->estadoloteinsumoid,
                ]
            );
        }

        $this->command?->info('DemoLoteInsumoEjemplosSeeder: 10 aplicaciones de insumo creadas.');
    }
}
