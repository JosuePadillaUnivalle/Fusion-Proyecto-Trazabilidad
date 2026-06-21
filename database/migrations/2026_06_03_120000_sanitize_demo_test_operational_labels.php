<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> clave normalizada => nombre operativo */
    private array $renombresInsumo = [
        'test' => 'Fungicida cobre hidróxido',
        'test 2' => 'Herbicida glifosato 48%',
        'test 3' => 'Fungicida cobre hidróxido',
        'test 4' => 'Manguera de riego reforzada',
    ];

    /** @var array<string, string> */
    private array $renombresLote = [
        'lote demo manual f1' => 'Lote Tomate Norte Banzer',
        'prueba 1' => 'Lote Pimentón La Guardia',
    ];

    /** @var array<string, string> */
    private array $renombresAlmacen = [
        'almacén — mercado prueba' => 'Almacén Mercado Central',
        'almacen — mercado prueba' => 'Almacén Mercado Central',
    ];

    /** @var array<string, string> */
    private array $renombresPuntoVenta = [
        'mercado prueba' => 'Mercado Satélite Norte',
        'tienda demo minorista' => 'Mercado Satélite Norte',
    ];

    public function up(): void
    {
        if (Schema::hasTable('insumo')) {
            $this->renombrarPorMapa('insumo', 'insumoid', 'nombre', $this->renombresInsumo);
        }

        if (Schema::hasTable('lote')) {
            $this->renombrarPorMapa('lote', 'loteid', 'nombre', $this->renombresLote);
        }

        if (Schema::hasTable('almacen')) {
            $this->renombrarPorMapa('almacen', 'almacenid', 'nombre', $this->renombresAlmacen);
        }

        if (Schema::hasTable('punto_venta')) {
            $this->renombrarPorMapa('punto_venta', 'puntoventaid', 'nombre', $this->renombresPuntoVenta);
        }
    }

    public function down(): void
    {
        // No reversible: sustitución de etiquetas de prueba por nombres operativos.
    }

    /**
     * @param  array<string, string>  $mapa
     */
    private function renombrarPorMapa(string $tabla, string $pk, string $columna, array $mapa): void
    {
        foreach (DB::table($tabla)->get([$pk, $columna]) as $row) {
            $actual = (string) ($row->{$columna} ?? '');
            $clave = mb_strtolower(trim($actual));
            $nuevo = $mapa[$clave] ?? null;

            if ($nuevo === null || $nuevo === $actual) {
                continue;
            }

            DB::table($tabla)
                ->where($pk, $row->{$pk})
                ->update([$columna => $nuevo]);
        }
    }
};
