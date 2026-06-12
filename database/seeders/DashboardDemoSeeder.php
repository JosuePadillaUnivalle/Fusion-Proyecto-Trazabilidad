<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\DestinoProduccion;
use App\Models\Lote;
use App\Models\Prioridad;
use App\Models\Produccion;
use App\Models\TipoActividad;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos demo para probar filtros del dashboard (fechas y años variados).
 * Ejecutar: php artisan db:seed --class=DashboardDemoSeeder
 */
class DashboardDemoSeeder extends Seeder
{
    private const MARK = '[DEMO-DASH]';

    public function run(): void
    {
        if (! Schema::hasTable('produccion')) {
            $this->command?->warn('Omitido: tabla produccion no existe.');

            return;
        }

        $kgId = UnidadMedida::query()
            ->whereRaw('LOWER(nombre) IN (?, ?)', ['kilogramo', 'kg'])
            ->value('unidadmedidaid')
            ?? UnidadMedida::query()->orderBy('unidadmedidaid')->value('unidadmedidaid');

        if (! $kgId) {
            $this->command?->warn('Omitido: sin unidad kilogramo.');

            return;
        }

        $usuario = Usuario::query()->orderBy('usuarioid')->first();
        if (! $usuario) {
            $this->command?->warn('Omitido: sin usuarios.');

            return;
        }

        $prioridadId = Schema::hasTable('prioridad')
            ? (Prioridad::query()->orderBy('prioridadid')->value('prioridadid'))
            : null;

        $destinoId = DestinoProduccion::query()->orderBy('destinoproduccionid')->value('destinoproduccionid');
        if (! $destinoId) {
            $this->command?->warn('Omitido: sin destinos de producción.');

            return;
        }

        $this->limpiarMarcados();

        $producciones = [
            ['cultivo' => 'Tomate', 'cantidad' => 420, 'fecha' => '2024-03-15'],
            ['cultivo' => 'Papa', 'cantidad' => 880, 'fecha' => '2024-07-22'],
            ['cultivo' => 'Lechuga', 'cantidad' => 310, 'fecha' => '2024-11-08'],
            ['cultivo' => 'Cebolla', 'cantidad' => 650, 'fecha' => '2025-02-10'],
            ['cultivo' => 'Maíz', 'cantidad' => 1200, 'fecha' => '2025-05-18'],
            ['cultivo' => 'Tomate', 'cantidad' => 540, 'fecha' => '2025-09-03'],
            ['cultivo' => 'Zanahoria Imperator', 'cantidad' => 195, 'fecha' => '2026-01-12'],
            ['cultivo' => 'Lechuga Crespa', 'cantidad' => 210, 'fecha' => '2026-03-20'],
            ['cultivo' => 'Papa', 'cantidad' => 760, 'fecha' => '2026-05-14'],
            ['cultivo' => 'Tomate', 'cantidad' => 390, 'fecha' => '2026-06-02'],
        ];

        $produccionIds = [];
        foreach ($producciones as $row) {
            $lote = $this->lotePorCultivo($row['cultivo']);
            if (! $lote) {
                continue;
            }

            $prod = Produccion::query()->create([
                'loteid' => $lote->loteid,
                'cantidad' => $row['cantidad'],
                'unidadmedidaid' => $kgId,
                'cantidad_base' => $row['cantidad'],
                'fechacosecha' => $row['fecha'],
                'destinoproduccionid' => $destinoId,
                'observaciones' => self::MARK.' Cosecha demo '.$row['cultivo'],
            ]);
            $produccionIds[] = $prod->produccionid;
        }

        $ventas = [
            ['prod_idx' => 0, 'cliente' => 'Mercado 16 de Julio', 'cantidad' => 120, 'precio' => 8.5, 'fecha' => '2024-04-01'],
            ['prod_idx' => 2, 'cliente' => 'Restaurante El Fogón', 'cantidad' => 80, 'precio' => 12, 'fecha' => '2024-12-15'],
            ['prod_idx' => 4, 'cliente' => 'Cooperativa Valle', 'cantidad' => 200, 'precio' => 6.2, 'fecha' => '2025-06-01'],
            ['prod_idx' => 5, 'cliente' => 'Supermercado Hiper', 'cantidad' => 150, 'precio' => 9.8, 'fecha' => '2025-10-20'],
            ['prod_idx' => 9, 'cliente' => 'Distribuidora Norte', 'cantidad' => 90, 'precio' => 11.5, 'fecha' => '2026-06-03'],
        ];

        if (Schema::hasTable('venta')) {
            foreach ($ventas as $v) {
                $prodId = $produccionIds[$v['prod_idx']] ?? null;
                if (! $prodId) {
                    continue;
                }
                Venta::query()->create([
                    'produccionid' => $prodId,
                    'cliente' => $v['cliente'],
                    'cantidad' => $v['cantidad'],
                    'unidadmedidaid' => $kgId,
                    'preciounitario' => $v['precio'],
                    'fechaventa' => $v['fecha'],
                    'observaciones' => self::MARK,
                ]);
            }
        }

        if (Schema::hasTable('actividad') && $prioridadId) {
            $tipos = [
                'Siembra' => '2024-01-20',
                'Riego' => '2024-06-10',
                'Fertilización' => '2025-03-05',
                'Control de plagas' => '2025-08-18',
                'Cosecha' => '2026-05-28',
            ];

            foreach ($tipos as $nombreTipo => $fecha) {
                $tipoId = TipoActividad::query()->where('nombre', $nombreTipo)->value('tipoactividadid');
                $lote = Lote::query()->whereHas('estadoTipo', fn ($q) => $q->whereNotIn('nombre', ['en descanso']))->first()
                    ?? Lote::query()->first();
                if (! $tipoId || ! $lote) {
                    continue;
                }

                Actividad::query()->create([
                    'loteid' => $lote->loteid,
                    'tipoactividadid' => $tipoId,
                    'usuarioid' => $usuario->usuarioid,
                    'prioridadid' => $prioridadId,
                    'fechainicio' => Carbon::parse($fecha)->startOfDay(),
                    'fechafin' => Carbon::parse($fecha)->addHours(3),
                    'descripcion' => self::MARK.' Actividad demo',
                ]);
            }
        }

        $this->command?->info('DashboardDemoSeeder: '.count($produccionIds).' producciones, ventas y actividades demo insertadas.');
    }

    private function lotePorCultivo(string $nombreCultivo): ?Lote
    {
        $cultivo = Cultivo::query()->where('nombre', $nombreCultivo)->first()
            ?? Cultivo::query()->where('nombre', 'like', '%'.explode(' ', $nombreCultivo)[0].'%')->first();

        if (! $cultivo) {
            return Lote::query()->orderBy('loteid')->first();
        }

        return Lote::query()->where('cultivoid', $cultivo->cultivoid)->orderBy('loteid')->first()
            ?? Lote::query()->orderBy('loteid')->first();
    }

    private function limpiarMarcados(): void
    {
        if (Schema::hasTable('venta')) {
            Venta::query()->where('observaciones', self::MARK)->delete();
        }

        $prodIds = Produccion::query()
            ->where('observaciones', 'like', '%'.self::MARK.'%')
            ->pluck('produccionid');

        if ($prodIds->isNotEmpty() && Schema::hasTable('venta')) {
            Venta::query()->whereIn('produccionid', $prodIds)->delete();
        }

        Produccion::query()->where('observaciones', 'like', '%'.self::MARK.'%')->delete();

        if (Schema::hasTable('actividad')) {
            Actividad::query()->where('descripcion', 'like', '%'.self::MARK.'%')->delete();
        }
    }
}
