<?php

namespace Database\Seeders;

use App\Models\Insumo;
use App\Models\Lote;
use App\Support\InsumoCatalogo;
use App\Support\InsumoImagenCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Restaura datos operativos ricos + fotos reales de insumos.
 * No borra la base completa: añade lotes/ejemplos y corrige nombres/fotos.
 *
 * php artisan db:seed --class=RestaurarDatosOperativosSeeder --force
 */
class RestaurarDatosOperativosSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Restaurando datos operativos AgroFusion...');

        // 1) Quitar nombres de demo
        if (Schema::hasTable('lote')) {
            Lote::query()
                ->where('nombre', 'like', '%Flujo Completo%')
                ->orWhere('nombre', 'like', '%flujo completo%')
                ->orWhere('codigo_trazabilidad', 'TRAZ-FLUJO-2026-001')
                ->update([
                    'nombre' => 'Lote Zanahoria Valle Tiquipaya',
                    'codigo_trazabilidad' => 'TRAZ-ZAN-TIQ-2026-001',
                ]);
            $this->command?->info('Lotes: nombres demo renombrados.');
        }

        // 2) Catálogo de insumos + imágenes locales
        InsumoCatalogo::asegurarInsumosCampo();
        InsumoCatalogo::rellenarImagenesInsumosOperativos();
        $this->forzarImagenesLocalesInsumos();
        $this->command?->info('Insumos: fotos locales aplicadas.');

        // 3) Varios lotes + actividades + producciones
        $this->call(DemoLotesProduccionActividadesSeeder::class);

        // 4) Stock / inventario / paneles extra (si existen tablas)
        if (Schema::hasTable('almacen')) {
            $this->callQuietly(InventarioModuloSeeder::class);
            $this->callQuietly(AlmacenPiraiStockSeeder::class);
            $this->callQuietly(DemoProduccionInventarioExtraSeeder::class);
            $this->callQuietly(DemoLoteInsumoEjemplosSeeder::class);
        }

        // 5) Recrear recorrido zanahoria (nombres reales, sin "Flujo Completo")
        $this->call(FlujoCompletoTrazabilidadQrSeeder::class);

        // 6) Reaplicar fotos (por si algún seeder las sobrescribió)
        $this->forzarImagenesLocalesInsumos();

        $totalLotes = Schema::hasTable('lote') ? Lote::query()->count() : 0;
        $totalInsumos = Schema::hasTable('insumo') ? Insumo::query()->count() : 0;
        $this->command?->info("Listo. Lotes: {$totalLotes} · Insumos: {$totalInsumos}");
    }

    private function callQuietly(string $class): void
    {
        try {
            $this->call($class);
        } catch (\Throwable $e) {
            $this->command?->warn($class.' omitido: '.$e->getMessage());
        }
    }

    private function forzarImagenesLocalesInsumos(): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasColumn('insumo', 'imagenurl')) {
            return;
        }

        Insumo::query()->with('tipo')->chunkById(100, function ($insumos) {
            foreach ($insumos as $insumo) {
                $slug = InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre);
                $url = InsumoImagenCatalogo::urlPorNombreYTipo((string) $insumo->nombre, $slug);

                // Preferir siempre ruta pública local images/insumos/...
                if (preg_match('#(?:images/insumos/[^?/]+)#', $url, $m)) {
                    $nueva = $m[0];
                } elseif (str_starts_with($url, 'images/insumos/') || str_starts_with($url, 'insumos/')) {
                    $nueva = $url;
                } else {
                    // Si asset() devolvió URL absoluta, intentar mapear por nombre
                    $local = null;
                    $n = mb_strtolower(trim((string) $insumo->nombre));
                    foreach ([
                        'glifosato' => 'images/insumos/herbicida-glifosato.jpg',
                        'piretroides' => 'images/insumos/insecticida-piretroides.jpg',
                        'fungicida' => 'images/insumos/fungicida-cobre.jpg',
                        'compost' => 'images/insumos/abono-compost.jpg',
                        'npk' => 'images/insumos/fertilizante-npk.jpg',
                        'urea' => 'images/insumos/urea-granulada.jpg',
                        'neem' => 'images/insumos/bioinsecticida-neem.jpg',
                        'zanahoria' => 'images/insumos/semilla-zanahoria.jpg',
                        'papa' => 'images/insumos/papa-amarilla.jpg',
                    ] as $frag => $path) {
                        if (str_contains($n, $frag)) {
                            $local = $path;
                            break;
                        }
                    }
                    $nueva = $local ?? $url;
                }

                if ((string) $insumo->imagenurl !== $nueva) {
                    $insumo->imagenurl = $nueva;
                    $insumo->save();
                }
            }
        });
    }
}
