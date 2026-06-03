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

        $almacen = AlmacenAmbito::scope(Almacen::query()->where('activo', true), AlmacenAmbito::PLANTA)
            ->orderBy('almacenid')
            ->first();

        if (! $almacen) {
            $almacen = Almacen::create([
                'nombre' => 'Almacén Materia Prima Planta',
                'descripcion' => 'Recepción de cosecha e insumos para industrialización',
                'ubicacion' => 'GPS -17.78330, -63.18210',
                'capacidad' => 50000,
                'ambito' => AlmacenAmbito::PLANTA,
                'activo' => true,
            ]);
        } elseif (Schema::hasColumn('almacen', 'ambito') && $almacen->ambito !== AlmacenAmbito::PLANTA) {
            $almacen->update(['ambito' => AlmacenAmbito::PLANTA]);
        }

        $kgId = UnidadMedida::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%kilogramo%'])
            ->orWhereRaw('LOWER(abreviatura) = ?', ['kg'])
            ->value('unidadmedidaid')
            ?? UnidadMedida::query()->value('unidadmedidaid');

        $lId = UnidadMedida::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%litro%'])
            ->orWhereRaw('LOWER(abreviatura) = ?', ['l'])
            ->value('unidadmedidaid')
            ?? $kgId;

        $tipoCosecha = TipoInsumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%siembra%'])
            ->orWhereRaw('LOWER(nombre) LIKE ?', ['%cosecha%'])
            ->first()
            ?? TipoInsumo::firstOrCreate(['nombre' => 'Material de Siembra']);

        $tipoInsumo = TipoInsumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%insumo%'])
            ->first()
            ?? TipoInsumo::firstOrCreate(['nombre' => 'Insumo de procesamiento']);

        $usuarioId = Usuario::query()->where('role', 'planta')->value('usuarioid')
            ?? Usuario::query()->where('role', 'admin')->value('usuarioid');

        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->first();

        $insumos = [
            [
                'nombre' => 'Papa industrial Monalisa',
                'tipo' => $tipoCosecha,
                'um' => $kgId,
                'stock' => 2850.0,
                'min' => 500,
                'desc' => 'Papa de mesa recibida de productores del valle. Uso: snacks y puré.',
            ],
            [
                'nombre' => 'Cebolla blanca granel',
                'tipo' => $tipoCosecha,
                'um' => $kgId,
                'stock' => 1240.0,
                'min' => 200,
                'desc' => 'Cebolla de primera calidad para salsas y condimentos.',
            ],
            [
                'nombre' => 'Aceite vegetal refinado',
                'tipo' => $tipoInsumo,
                'um' => $lId,
                'stock' => 680.0,
                'min' => 100,
                'desc' => 'Aceite de girasol para fritura industrial.',
            ],
        ];

        DB::transaction(function () use ($insumos, $almacen, $usuarioId, $tipoIngreso) {
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
                    $existe = AlmacenMovimiento::query()->where('referencia', $ref)->exists();
                    if (! $existe) {
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
            }
        });

        $this->command?->info('Insumos de planta listos en «'.$almacen->nombre.'»: Papa industrial, Cebolla blanca, Aceite vegetal.');
    }
}
