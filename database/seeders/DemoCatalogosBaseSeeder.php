<?php

namespace Database\Seeders;

use App\Models\Cultivo;
use App\Models\DestinoProduccion;
use App\Models\EstadoLoteTipo;
use App\Models\Prioridad;
use App\Models\TipoActividad;
use App\Models\TipoInsumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Support\CultivoCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoCatalogosBaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUnidadesMedida();
        $this->seedCultivos();
        $this->seedTiposMovimientoAlmacen();
        $this->seedEstadosLote();
        $this->seedTiposActividad();
        $this->seedTiposInsumo();
        $this->seedPrioridades();
        $this->seedDestinosProduccion();
    }

    private function seedUnidadesMedida(): void
    {
        if (!Schema::hasTable('unidadmedida')) {
            return;
        }

        $hasAbreviatura = Schema::hasColumn('unidadmedida', 'abreviatura');
        $hasCategoria = Schema::hasColumn('unidadmedida', 'categoria');

        $items = [
            ['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'categoria' => 'peso'],
            ['nombre' => 'Gramo', 'abreviatura' => 'g', 'categoria' => 'peso'],
            ['nombre' => 'Quintal', 'abreviatura' => 'qq', 'categoria' => 'peso'],
            ['nombre' => 'Litro', 'abreviatura' => 'l', 'categoria' => 'volumen'],
            ['nombre' => 'Mililitro', 'abreviatura' => 'ml', 'categoria' => 'volumen'],
            ['nombre' => 'Metro', 'abreviatura' => 'm', 'categoria' => 'longitud'],
            ['nombre' => 'Unidad', 'abreviatura' => 'und', 'categoria' => 'cantidad'],
            ['nombre' => 'Hectárea', 'abreviatura' => 'ha', 'categoria' => 'superficie'],
        ];

        foreach ($items as $item) {
            $data = ['nombre' => $item['nombre']];
            if ($hasAbreviatura) {
                $data['abreviatura'] = $item['abreviatura'];
            }
            if ($hasCategoria) {
                $data['categoria'] = $item['categoria'];
            }
            UnidadMedida::updateOrCreate(['nombre' => $item['nombre']], $data);
        }
    }

    private function seedCultivos(): void
    {
        if (!Schema::hasTable('cultivo')) {
            return;
        }

        foreach (CultivoCatalogo::variantes() as $cultivo) {
            Cultivo::updateOrCreate(
                ['nombre' => $cultivo['nombre']],
                ['nombre' => $cultivo['nombre']]
            );
        }
    }

    private function seedTiposMovimientoAlmacen(): void
    {
        if (!Schema::hasTable('tipo_movimiento_almacen')) {
            return;
        }

        $items = [
            ['nombre' => 'Compra', 'naturaleza' => 'ingreso'],
            ['nombre' => 'Producción recibida', 'naturaleza' => 'ingreso'],
            ['nombre' => 'Devolución', 'naturaleza' => 'ingreso'],
            ['nombre' => 'Ajuste positivo', 'naturaleza' => 'ingreso'],
            ['nombre' => 'Envío', 'naturaleza' => 'salida'],
            ['nombre' => 'Consumo interno', 'naturaleza' => 'salida'],
            ['nombre' => 'Merma', 'naturaleza' => 'salida'],
            ['nombre' => 'Ajuste negativo', 'naturaleza' => 'salida'],
        ];

        foreach ($items as $item) {
            TipoMovimientoAlmacen::updateOrCreate(
                ['nombre' => $item['nombre'], 'naturaleza' => $item['naturaleza']],
                ['activo' => true]
            );
        }
    }

    private function seedEstadosLote(): void
    {
        if (!Schema::hasTable('estadolote_tipo')) {
            return;
        }

        $hasDescripcion = Schema::hasColumn('estadolote_tipo', 'descripcion');
        $items = [
            ['nombre' => 'Planificado', 'descripcion' => 'El lote fue creado pero aún no se ha sembrado.'],
            ['nombre' => 'Sembrado', 'descripcion' => 'La siembra ya fue realizada.'],
            ['nombre' => 'En crecimiento', 'descripcion' => 'El cultivo está desarrollándose.'],
            ['nombre' => 'Listo para cosecha', 'descripcion' => 'El cultivo alcanzó las condiciones para ser cosechado.'],
            ['nombre' => 'Cosechado', 'descripcion' => 'La producción fue recolectada.'],
            ['nombre' => 'Finalizado', 'descripcion' => 'El ciclo del lote terminó y ya no se realizarán más actividades.'],
        ];

        foreach ($items as $item) {
            $data = ['nombre' => $item['nombre']];
            if ($hasDescripcion) {
                $data['descripcion'] = $item['descripcion'];
            }
            EstadoLoteTipo::updateOrCreate(['nombre' => $item['nombre']], $data);
        }
    }

    private function seedTiposActividad(): void
    {
        if (!Schema::hasTable('tipoactividad')) {
            return;
        }

        $hasDescripcion = Schema::hasColumn('tipoactividad', 'descripcion');
        $items = ['Siembra', 'Riego', 'Fertilización', 'Cosecha', 'Control de plagas'];

        foreach ($items as $nombre) {
            $data = ['nombre' => $nombre];
            if ($hasDescripcion) {
                $data['descripcion'] = $nombre;
            }
            TipoActividad::updateOrCreate(['nombre' => $nombre], $data);
        }
    }

    private function seedTiposInsumo(): void
    {
        if (!Schema::hasTable('tipoinsumo')) {
            return;
        }

        foreach (\App\Support\InsumoCatalogo::TIPOS as $nombre) {
            TipoInsumo::updateOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
        }
    }

    private function seedPrioridades(): void
    {
        if (!Schema::hasTable('prioridad')) {
            return;
        }

        foreach (['Baja', 'Media', 'Alta', 'Urgente'] as $nombre) {
            Prioridad::firstOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
        }
    }

    private function seedDestinosProduccion(): void
    {
        if (!Schema::hasTable('destinoproduccion')) {
            return;
        }

        foreach (['Almacenamiento', 'Venta', 'Procesamiento', 'Envío'] as $nombre) {
            DestinoProduccion::firstOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
        }
    }
}
