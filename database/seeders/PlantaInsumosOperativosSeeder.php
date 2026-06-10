<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\TipoInsumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Support\AlmacenAmbito;
use App\Support\InsumoCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Materias primas operativas en almacén de planta (sin prefijos demo).
 * Cubre las líneas de transformación del catálogo (papas, salsas, jugos, snacks, etc.).
 *
 * php artisan db:seed --class=PlantaInsumosOperativosSeeder
 */
class PlantaInsumosOperativosSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasTable('almacen')) {
            return;
        }

        InsumoCatalogo::asegurarCatalogosBase();
        AlmacenAmbito::asegurarAmbitosEnRegistros();

        $almacen = $this->resolverAlmacenPlanta();
        if (! $almacen) {
            $this->command?->warn('PlantaInsumosOperativosSeeder: no hay almacén de planta activo.');

            return;
        }

        $kgId = $this->unidadId(['kg', 'kilogramo']);
        $lId = $this->unidadId(['l', 'litro']) ?? $kgId;

        $tipoCosecha = TipoInsumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%siembra%'])
            ->orWhereRaw('LOWER(nombre) LIKE ?', ['%cosecha%'])
            ->first()
            ?? TipoInsumo::firstOrCreate(['nombre' => 'Material de Siembra']);

        $tipoInsumo = TipoInsumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%insumo%'])
            ->whereRaw('LOWER(nombre) NOT LIKE ?', ['%siembra%'])
            ->first()
            ?? TipoInsumo::firstOrCreate(['nombre' => 'Insumo de procesamiento']);

        $usuarioId = Usuario::query()->where('role', 'planta')->value('usuarioid')
            ?? Usuario::query()->where('role', 'admin')->value('usuarioid');

        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->first();

        $insumos = [
            // Tubérculos y hortalizas — papas, purés, chips, fritas
            ['nombre' => 'Papa industrial Monalisa', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 2740.0, 'min' => 500, 'desc' => 'Papa de mesa para snacks, puré y fritas.'],
            ['nombre' => 'Papa rubíola granel', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 1680.0, 'min' => 300, 'desc' => 'Variedad roja para chips y laminados.'],
            ['nombre' => 'Zanahoria fresca Imperator', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 1920.0, 'min' => 250, 'desc' => 'Zanahoria de campo para conserva, puré y IQF.'],
            ['nombre' => 'Cebolla blanca granel', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 1235.0, 'min' => 200, 'desc' => 'Cebolla de primera para salsas, cubos IQF y bases.'],
            ['nombre' => 'Cebolla colorada granel', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 640.0, 'min' => 120, 'desc' => 'Cebolla morada para salsas gourmet.'],
            ['nombre' => 'Tomate pera granel', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 2100.0, 'min' => 400, 'desc' => 'Tomate maduro para salsa, ketchup y concentrado.'],
            ['nombre' => 'Lechuga crespa granel', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 380.0, 'min' => 80, 'desc' => 'Hoja fresca para mix de ensalada.'],
            ['nombre' => 'Repollo blanco granel', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 520.0, 'min' => 100, 'desc' => 'Repollo para mix vegetal y ensaladas.'],
            ['nombre' => 'Mandioca fresca', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 1450.0, 'min' => 300, 'desc' => 'Yuca para harina precocida y almidón.'],
            ['nombre' => 'Maíz grano amarillo', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 980.0, 'min' => 200, 'desc' => 'Grano seco para extrusión y snacks.'],
            // Frutas — jugos pasteurizados
            ['nombre' => 'Naranja Valencia', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 860.0, 'min' => 150, 'desc' => 'Fruta para jugo pasteurizado y néctar.'],
            ['nombre' => 'Mango Tommy', 'tipo' => $tipoCosecha, 'um' => $kgId, 'stock' => 720.0, 'min' => 120, 'desc' => 'Mango de pulpa para jugo y puré de fruta.'],
            // Insumos de procesamiento
            ['nombre' => 'Aceite vegetal refinado', 'tipo' => $tipoInsumo, 'um' => $lId, 'stock' => 680.0, 'min' => 100, 'desc' => 'Aceite de girasol para fritura industrial.'],
            ['nombre' => 'Harina de trigo industrial', 'tipo' => $tipoInsumo, 'um' => $kgId, 'stock' => 1150.0, 'min' => 200, 'desc' => 'Harina para galletas, masas y breading.'],
            ['nombre' => 'Azúcar refinada', 'tipo' => $tipoInsumo, 'um' => $kgId, 'stock' => 540.0, 'min' => 100, 'desc' => 'Endulzante para jugos, salsas y masas.'],
            ['nombre' => 'Sal refinada', 'tipo' => $tipoInsumo, 'um' => $kgId, 'stock' => 320.0, 'min' => 50, 'desc' => 'Condimento y conservación.'],
            ['nombre' => 'Vinagre blanco', 'tipo' => $tipoInsumo, 'um' => $lId, 'stock' => 210.0, 'min' => 40, 'desc' => 'Acidificante para salsas y escabeches.'],
            ['nombre' => 'Agua tratada', 'tipo' => $tipoInsumo, 'um' => $lId, 'stock' => 5000.0, 'min' => 500, 'desc' => 'Agua de proceso para dilución, lavado y cocción.'],
        ];

        $creados = 0;

        DB::transaction(function () use ($insumos, $almacen, $usuarioId, $tipoIngreso, &$creados) {
            foreach ($insumos as $def) {
                if (! $def['um']) {
                    continue;
                }

                $insumo = Insumo::updateOrCreate(
                    [
                        'nombre' => $def['nombre'],
                        'almacenid' => $almacen->almacenid,
                    ],
                    [
                        'tipoinsumoid' => $def['tipo']->tipoinsumoid,
                        'unidadmedidaid' => $def['um'],
                        'stock' => $def['stock'],
                        'stockminimo' => $def['min'],
                        'descripcion' => $def['desc'],
                        'preciounitario' => 0,
                    ]
                );

                if ($tipoIngreso && $usuarioId && Schema::hasTable('almacen_movimiento')) {
                    $ref = 'ING-PLANTA-'.$insumo->insumoid;
                    if (! AlmacenMovimiento::query()->where('referencia', $ref)->exists()) {
                        AlmacenMovimiento::create([
                            'almacenid' => $almacen->almacenid,
                            'insumoid' => $insumo->insumoid,
                            'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                            'usuarioid' => $usuarioId,
                            'fecha' => now()->toDateString(),
                            'cantidad' => $def['stock'],
                            'referencia' => $ref,
                            'destino_motivo' => $almacen->nombre,
                            'observaciones' => 'Stock inicial materia prima planta',
                        ]);
                    }
                }

                $creados++;
            }
        });

        $this->command?->info("Materias primas de planta: {$creados} ítems en «{$almacen->nombre}».");
    }

    private function resolverAlmacenPlanta(): ?Almacen
    {
        $remanso = AlmacenAmbito::scope(
            Almacen::query()->where('activo', true)->where('nombre', DemoAlmacenPlantaPruebaSeeder::ALMACEN_NOMBRE),
            AlmacenAmbito::PLANTA
        )->first();

        if ($remanso) {
            return $remanso;
        }

        $existente = AlmacenAmbito::scope(Almacen::query()->where('activo', true), AlmacenAmbito::PLANTA)
            ->orderBy('almacenid')
            ->first();

        if ($existente) {
            return $existente;
        }

        return Almacen::create([
            'nombre' => 'Almacén Materia Prima Planta',
            'descripcion' => 'Recepción de cosecha e insumos para industrialización',
            'ubicacion' => 'GPS -17.78330, -63.18210',
            'capacidad' => 50000,
            'ambito' => AlmacenAmbito::PLANTA,
            'activo' => true,
        ]);
    }

    /** @param  list<string>  $tokens */
    private function unidadId(array $tokens): ?int
    {
        $query = UnidadMedida::query();
        foreach ($tokens as $i => $token) {
            $like = '%'.$token.'%';
            if ($i === 0) {
                $query->where(function ($q) use ($like, $token) {
                    $q->whereRaw('LOWER(nombre) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(TRIM(COALESCE(abreviatura, \'\'))) = ?', [$token]);
                });
            } else {
                $query->orWhere(function ($q) use ($like, $token) {
                    $q->whereRaw('LOWER(nombre) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(TRIM(COALESCE(abreviatura, \'\'))) = ?', [$token]);
                });
            }
        }

        return $query->value('unidadmedidaid') ?? UnidadMedida::query()->value('unidadmedidaid');
    }
}
