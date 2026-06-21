<?php

namespace Database\Seeders;

use App\Models\Cultivo;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Models\Insumo;
use App\Models\Usuario;
use App\Support\EstadoLoteCatalogo;
use App\Support\LoteDefaults;
use App\Support\TrazabilidadProductoPdvService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Lote agrícola de Zanahoria para trazabilidad PDV + códigos en insumos existentes.
 * php artisan db:seed --class=DemoLoteZanahoriaTrazabilidadSeeder
 */
class DemoLoteZanahoriaTrazabilidadSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('lote')) {
            return;
        }

        $cultivo = Cultivo::firstOrCreate(
            ['nombre' => 'Zanahoria'],
            ['nombre' => 'Zanahoria']
        );

        $agricultorId = Usuario::query()->where('role', 'agricultor')->value('usuarioid')
            ?? Usuario::query()->where('role', 'admin')->value('usuarioid');

        $estadoCosechado = EstadoLoteCatalogo::idPorSlug('cosechado')
            ?? EstadoLoteTipo::query()->whereRaw('LOWER(nombre) LIKE ?', ['%cosech%'])->value('estadolotetipoid')
            ?? EstadoLoteTipo::query()->value('estadolotetipoid');

        $data = LoteDefaults::enrich([
            'nombre' => 'Lote Zanahoria Imperator — Valle',
            'codigo_trazabilidad' => 'TRAZ-ZAN-2026-001',
            'cultivoid' => $cultivo->cultivoid,
            'usuarioid' => $agricultorId,
            'estadolotetipoid' => $estadoCosechado,
            'superficie' => 2.5,
            'ubicacion' => 'Valle de Tiquipaya, Cochabamba',
            'fechasiembra' => now()->subDays(75),
        ]);

        $lote = Lote::updateOrCreate(
            ['codigo_trazabilidad' => 'TRAZ-ZAN-2026-001'],
            $data
        );

        if (Schema::hasTable('historial_estados_lote') && $estadoCosechado) {
            HistorialEstadoLote::firstOrCreate(
                [
                    'loteid' => $lote->loteid,
                    'estadolotetipoid' => $estadoCosechado,
                    'observaciones' => 'Cosecha registrada — producto enviado a planta',
                ],
                [
                    'fecha_cambio' => now()->subDays(30),
                    'usuarioid' => $agricultorId,
                ]
            );
        }

        if (Schema::hasTable('insumo') && Schema::hasColumn('insumo', 'codigo_trazabilidad')) {
            $service = app(TrazabilidadProductoPdvService::class);
            Insumo::query()
                ->whereNull('codigo_trazabilidad')
                ->orWhere('codigo_trazabilidad', '')
                ->each(fn (Insumo $i) => $service->asegurarCodigo($i));
        }

        $this->command?->info('Lote agrícola Zanahoria listo (TRAZ-ZAN-2026-001). Códigos de trazabilidad PDV actualizados.');
    }
}
