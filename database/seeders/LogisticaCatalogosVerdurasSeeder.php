<?php

namespace Database\Seeders;

use App\Models\CatalogoTamanoConteo;
use App\Models\Insumo;
use App\Models\TipoEmpaque;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogos logísticos para verduras: empaques con medidas, calibres, tipos de transporte/vehículo.
 */
class LogisticaCatalogosVerdurasSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTiposTransporte();
        $this->purgeTiposTransporteObsoletos();
        $this->seedTiposVehiculo();
        $this->purgeTiposVehiculoObsoletos();
        $this->seedTiposEmpaque();
        $this->seedCondicionesOrgTrack();
        $this->seedIncidentesOrgTrack();
        $this->seedCalibresVerduras();
        $this->call(FlotaVehiculosPorAmbitoSeeder::class);
    }

    private function seedTiposTransporte(): void
    {
        if (! Schema::hasTable('tipo_transporte')) {
            return;
        }

        $now = now();
        foreach (
            [
                ['nombre' => 'Refrigerado', 'codigo' => 'REFRIGERADO', 'descripcion' => 'Cadena de frío activa'],
                ['nombre' => 'Isotérmico', 'codigo' => 'ISOTERMICO', 'descripcion' => 'Aislamiento térmico sin refrigeración activa'],
                ['nombre' => 'Multitemperatura', 'codigo' => 'MULTITEMPERATURA', 'descripcion' => 'Zonas con distintas temperaturas'],
                ['nombre' => 'Carga general', 'codigo' => 'CARGA_GENERAL', 'descripcion' => 'Mercancía estándar sin control térmico'],
            ] as $row
        ) {
            DB::table('tipo_transporte')->updateOrInsert(
                ['nombre' => $row['nombre']],
                array_merge($row, ['updated_at' => $now, 'created_at' => $now])
            );
        }
    }

    private function purgeTiposTransporteObsoletos(): void
    {
        if (! Schema::hasTable('tipo_transporte')) {
            return;
        }

        DB::table('tipo_transporte')->where('nombre', 'Terrestre')->delete();
    }

    private function seedTiposVehiculo(): void
    {
        if (! Schema::hasTable('tipo_vehiculo')) {
            return;
        }

        $now = now();
        $rows = [
            ['nombre' => 'Camioneta', 'codigo' => 'CAMIONETA', 'tamano' => 'pequeno', 'capacidad_kg' => 1500, 'capacidad_m3' => 8],
            ['nombre' => 'Camión pequeño', 'codigo' => 'CAMION_PQ', 'tamano' => 'mediano', 'capacidad_kg' => 3500, 'capacidad_m3' => 15],
            ['nombre' => 'Camión grande', 'codigo' => 'CAMION_GR', 'tamano' => 'grande', 'capacidad_kg' => 10000, 'capacidad_m3' => 40],
        ];

        foreach ($rows as $row) {
            DB::table('tipo_vehiculo')->updateOrInsert(
                ['nombre' => $row['nombre']],
                array_merge($row, [
                    'licencia_requerida' => 'C',
                    'activo' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }
    }

    private function purgeTiposVehiculoObsoletos(): void
    {
        if (! Schema::hasTable('tipo_vehiculo')) {
            return;
        }

        DB::table('tipo_vehiculo')->whereIn('nombre', ['Camión', 'Motocicleta', 'Furgoneta'])->delete();
        DB::table('tipo_vehiculo')->where('codigo', 'FURGONETA')->delete();
    }

    private function seedTiposEmpaque(): void
    {
        if (! Schema::hasTable('tipo_empaque')) {
            return;
        }

        $empaques = [
            [
                'nombre' => 'Caja de cartón',
                'descripcion' => 'Caja estándar hortícola 60×40×30 cm',
                'largo_cm' => 60,
                'ancho_cm' => 40,
                'alto_cm' => 30,
                'tara_kg' => 1.2,
                'capacidad_unidades' => 50,
                'unidades_por_pallet' => 48,
            ],
            [
                'nombre' => 'Caja plástica',
                'descripcion' => 'Caja plástica reutilizable',
                'largo_cm' => 60,
                'ancho_cm' => 40,
                'alto_cm' => 30,
                'tara_kg' => 2.5,
                'capacidad_unidades' => 50,
                'unidades_por_pallet' => 48,
            ],
            [
                'nombre' => 'Bolsa plástica',
                'descripcion' => 'Bolsa de polietileno',
                'largo_cm' => 50,
                'ancho_cm' => 35,
                'alto_cm' => 15,
                'tara_kg' => 0.1,
                'capacidad_unidades' => 30,
                'unidades_por_pallet' => 80,
            ],
            [
                'nombre' => 'Bandeja',
                'descripcion' => 'Bandeja de poliestireno expandido',
                'largo_cm' => 40,
                'ancho_cm' => 30,
                'alto_cm' => 8,
                'tara_kg' => 0.15,
                'capacidad_unidades' => 20,
                'unidades_por_pallet' => 120,
            ],
            [
                'nombre' => 'Canasta',
                'descripcion' => 'Canasta plástica reutilizable',
                'largo_cm' => 50,
                'ancho_cm' => 35,
                'alto_cm' => 25,
                'tara_kg' => 0.8,
                'capacidad_unidades' => 35,
                'unidades_por_pallet' => 60,
            ],
            [
                'nombre' => 'Saco',
                'descripcion' => 'Saco de yute o polipropileno (tubérculos)',
                'largo_cm' => 80,
                'ancho_cm' => 50,
                'alto_cm' => 15,
                'tara_kg' => 0.5,
                'capacidad_unidades' => 100,
                'unidades_por_pallet' => 40,
            ],
            [
                'nombre' => 'Pallet',
                'descripcion' => 'Pallet estándar 120×100 cm',
                'largo_cm' => 120,
                'ancho_cm' => 100,
                'alto_cm' => 15,
                'tara_kg' => 25,
                'capacidad_unidades' => 48,
                'unidades_por_pallet' => 1,
            ],
        ];

        foreach ($empaques as $row) {
            TipoEmpaque::query()->updateOrCreate(
                ['nombre' => $row['nombre']],
                array_merge($row, ['activo' => true])
            );
        }
    }

    private function seedCondicionesOrgTrack(): void
    {
        if (! Schema::hasTable('condicion_transporte')) {
            return;
        }

        $now = now();
        foreach (
            [
                ['codigo' => 'COND001', 'titulo' => 'Luces delanteras', 'descripcion' => 'Verificar funcionamiento de luces delanteras'],
                ['codigo' => 'COND002', 'titulo' => 'Luces traseras', 'descripcion' => 'Verificar funcionamiento de luces traseras'],
                ['codigo' => 'COND003', 'titulo' => 'Neumáticos', 'descripcion' => 'Verificar estado y presión de neumáticos'],
                ['codigo' => 'COND004', 'titulo' => 'Frenos', 'descripcion' => 'Verificar sistema de frenos'],
                ['codigo' => 'COND005', 'titulo' => 'Limpieza interior', 'descripcion' => 'Verificar limpieza del área de carga'],
                ['codigo' => 'COND006', 'titulo' => 'Documentación', 'descripcion' => 'Verificar documentos del vehículo vigentes'],
                ['codigo' => 'COND007', 'titulo' => 'Combustible', 'descripcion' => 'Verificar nivel de combustible adecuado'],
                ['codigo' => 'COND008', 'titulo' => 'Sistema de refrigeración', 'descripcion' => 'Verificar temperatura si aplica'],
            ] as $row
        ) {
            DB::table('condicion_transporte')->updateOrInsert(
                ['codigo' => $row['codigo']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    private function seedIncidentesOrgTrack(): void
    {
        if (! Schema::hasTable('tipo_incidente_transporte')) {
            return;
        }

        $now = now();
        foreach (
            [
                ['codigo' => 'INC001', 'titulo' => 'Accidente de tráfico', 'descripcion' => 'Colisión o accidente vial'],
                ['codigo' => 'INC002', 'titulo' => 'Falla mecánica', 'descripcion' => 'Desperfecto mecánico del vehículo'],
                ['codigo' => 'INC003', 'titulo' => 'Retraso en tráfico', 'descripcion' => 'Demora por congestión vehicular'],
                ['codigo' => 'INC004', 'titulo' => 'Condiciones climáticas', 'descripcion' => 'Demora por mal tiempo'],
                ['codigo' => 'INC005', 'titulo' => 'Daño a la carga', 'descripcion' => 'Deterioro o daño del producto'],
                ['codigo' => 'INC006', 'titulo' => 'Robo o extravío', 'descripcion' => 'Pérdida parcial o total de la carga'],
                ['codigo' => 'INC007', 'titulo' => 'Bloqueo de carretera', 'descripcion' => 'Vía cerrada o bloqueada'],
            ] as $row
        ) {
            DB::table('tipo_incidente_transporte')->updateOrInsert(
                ['codigo' => $row['codigo']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    private function seedCalibresVerduras(): void
    {
        if (! Schema::hasTable('catalogo_tamano_conteo') || ! Schema::hasTable('insumo')) {
            return;
        }

        $caja = TipoEmpaque::query()->where('nombre', 'Caja de cartón')->first();
        $canasta = TipoEmpaque::query()->where('nombre', 'Canasta')->first();
        $saco = TipoEmpaque::query()->where('nombre', 'Saco')->first();
        $bandeja = TipoEmpaque::query()->where('nombre', 'Bandeja')->first();

        $catalogo = [
            ['patron' => '%tomate%', 'empaque' => $caja, 'calibres' => [
                ['nombre' => 'Pequeño (80-100 g)', 'conteo' => 258, 'peso' => 0.089],
                ['nombre' => 'Mediano (120-130 g)', 'conteo' => 184, 'peso' => 0.125],
                ['nombre' => 'Grande (160-180 g)', 'conteo' => 136, 'peso' => 0.169],
            ]],
            ['patron' => '%zanahoria%', 'empaque' => $caja, 'calibres' => [
                ['nombre' => 'Mediana (80-100 g)', 'conteo' => 120, 'peso' => 0.090],
                ['nombre' => 'Grande (120-150 g)', 'conteo' => 80, 'peso' => 0.135],
            ]],
            ['patron' => '%papa%', 'empaque' => $saco, 'calibres' => [
                ['nombre' => 'Mediana (150-200 g)', 'conteo' => 50, 'peso' => 0.175],
                ['nombre' => 'Grande (250-300 g)', 'conteo' => 35, 'peso' => 0.275],
            ]],
            ['patron' => '%lechuga%', 'empaque' => $canasta, 'calibres' => [
                ['nombre' => 'Unidad estándar (300-400 g)', 'conteo' => 24, 'peso' => 0.350],
                ['nombre' => 'Unidad grande (500-600 g)', 'conteo' => 18, 'peso' => 0.550],
            ]],
            ['patron' => '%cebolla%', 'empaque' => $saco, 'calibres' => [
                ['nombre' => 'Pequeña (80-100 g)', 'conteo' => 100, 'peso' => 0.090],
                ['nombre' => 'Mediana (120-150 g)', 'conteo' => 70, 'peso' => 0.135],
                ['nombre' => 'Grande (180-220 g)', 'conteo' => 50, 'peso' => 0.200],
            ]],
            ['patron' => '%brócoli%', 'empaque' => $caja, 'calibres' => [
                ['nombre' => '14 coronas — caja', 'conteo' => 14, 'peso' => 0.500],
            ]],
            ['patron' => '%brocoli%', 'empaque' => $caja, 'calibres' => [
                ['nombre' => '14 coronas — caja', 'conteo' => 14, 'peso' => 0.500],
            ]],
            ['patron' => '%pepino%', 'empaque' => $caja, 'calibres' => [
                ['nombre' => 'Mediano (200-250 g)', 'conteo' => 40, 'peso' => 0.225],
            ]],
            ['patron' => '%piment%', 'empaque' => $caja, 'calibres' => [
                ['nombre' => 'Mediano (150-180 g)', 'conteo' => 60, 'peso' => 0.165],
            ]],
            ['patron' => '%espinaca%', 'empaque' => $bandeja, 'calibres' => [
                ['nombre' => 'Manojo estándar (250 g)', 'conteo' => 12, 'peso' => 0.250],
            ]],
        ];

        foreach ($catalogo as $grupo) {
            $insumo = Insumo::query()
                ->whereRaw('LOWER(nombre) LIKE ?', [strtolower($grupo['patron'])])
                ->orderBy('insumoid')
                ->first();

            if (! $insumo) {
                continue;
            }

            foreach ($grupo['calibres'] as $cal) {
                CatalogoTamanoConteo::query()->updateOrCreate(
                    [
                        'insumoid' => $insumo->insumoid,
                        'nombre' => $cal['nombre'],
                    ],
                    [
                        'conteo_por_empaque' => $cal['conteo'],
                        'peso_promedio_kg' => $cal['peso'],
                        'tipoempaqueid' => $grupo['empaque']?->tipoempaqueid,
                        'activo' => true,
                    ]
                );
            }
        }
    }
}
