<?php

use App\Models\Insumo;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use App\Services\InsumoEliminacionService;
use App\Support\AlmacenAmbito;
use App\Support\InsumoCatalogo;
use App\Support\InsumoImagenCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insumo')) {
            return;
        }

        InsumoCatalogo::asegurarCatalogosBase();
        $svc = app(InsumoEliminacionService::class);

        $tipoSemillaId = TipoInsumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%siembra%'])
            ->value('tipoinsumoid');

        $kgId = UnidadMedida::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%kilogramo%'])
            ->value('unidadmedidaid');

        $almacenAgricolaId = AlmacenAmbito::scope(
            \App\Models\Almacen::query()->where('activo', true),
            AlmacenAmbito::AGRICOLA
        )->orderBy('almacenid')->value('almacenid');

        $this->transferirStockPorNombre($svc, 'Cebolla blanca granel', 'Cebolla Blanca');
        $this->transferirStockPorNombre($svc, 'Cebolla colorada granel', 'Cebolla Morada');
        $this->transferirStockPorNombre($svc, 'Papa rubíola granel', 'Papa Rubíola');
        $this->transferirStockPorNombre($svc, 'Tomate pera granel', 'Tomate Perita');
        $this->transferirStockPorNombre($svc, 'Lechuga crespa granel', 'Lechuga Crespa');
        $this->transferirStockPorNombre($svc, 'Repollo blanco granel', 'Repollo Blanco');

        $this->renombrarSiExiste('Papa industrial Monalisa', 'Papa harinosa');
        $this->renombrarSiExiste('Tomate', 'Tomate Perita');
        $this->renombrarSiExiste('Cebolla', 'Cebolla Blanca');

        $this->fusionarDuplicados($svc, 'Lechuga', 'Lechuga Crespa');
        $this->fusionarDuplicados($svc, 'Zanahoria', 'Zanahoria Imperator');
        $this->fusionarDuplicados($svc, 'Zanahoria fresca Imperator', 'Zanahoria Imperator');

        if ($tipoSemillaId && $kgId) {
            $this->asegurarVariedadSiFalta(
                'Papa amarilla',
                (int) $tipoSemillaId,
                (int) $kgId,
                $almacenAgricolaId ? (int) $almacenAgricolaId : null,
                600.0
            );
        }

        Insumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%granel%'])
            ->orderBy('insumoid')
            ->each(function (Insumo $insumo) use ($svc): void {
                $svc->eliminar($insumo);
            });

        Insumo::query()->each(function (Insumo $insumo): void {
            InsumoEliminacionService::aplicarDosisReferencia($insumo, true);
            if (! InsumoImagenCatalogo::esImagenPersonalizada((string) ($insumo->imagenurl ?? ''))) {
                $slug = InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre ?? '');
                $insumo->update([
                    'imagenurl' => InsumoImagenCatalogo::urlPorNombreYTipo($insumo->nombre, $slug ?? ''),
                ]);
            }
        });

        InsumoCatalogo::consolidarMaterialSiembraPorNombre();
    }

    public function down(): void
    {
        // No reversible.
    }

    private function transferirStockPorNombre(InsumoEliminacionService $svc, string $desdeNombre, string $haciaNombre): void
    {
        $desde = Insumo::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($desdeNombre)])->first();
        if (! $desde) {
            return;
        }

        $hacia = Insumo::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($haciaNombre)])->first();
        if ($hacia) {
            $hacia->stock = (float) $hacia->stock + (float) $desde->stock;
            $hacia->save();
            $svc->fusionarEn((int) $desde->insumoid, (int) $hacia->insumoid);

            return;
        }

        $desde->update(['nombre' => $haciaNombre]);
    }

    private function renombrarSiExiste(string $actual, string $nuevo): void
    {
        $existenteNuevo = Insumo::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nuevo)])->first();
        $actualRow = Insumo::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($actual)])->first();

        if (! $actualRow || mb_strtolower($actual) === mb_strtolower($nuevo)) {
            return;
        }

        if ($existenteNuevo && (int) $existenteNuevo->insumoid !== (int) $actualRow->insumoid) {
            $existenteNuevo->stock = (float) $existenteNuevo->stock + (float) $actualRow->stock;
            $existenteNuevo->save();
            app(InsumoEliminacionService::class)->fusionarEn((int) $actualRow->insumoid, (int) $existenteNuevo->insumoid);

            return;
        }

        $actualRow->update(['nombre' => $nuevo]);
    }

    private function fusionarDuplicados(InsumoEliminacionService $svc, string $desdeNombre, string $haciaNombre): void
    {
        if (mb_strtolower($desdeNombre) === mb_strtolower($haciaNombre)) {
            return;
        }

        $desde = Insumo::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($desdeNombre)])->first();
        $hacia = Insumo::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($haciaNombre)])->first();

        if (! $desde || ! $hacia || (int) $desde->insumoid === (int) $hacia->insumoid) {
            return;
        }

        $hacia->stock = (float) $hacia->stock + (float) $desde->stock;
        $hacia->save();
        $svc->fusionarEn((int) $desde->insumoid, (int) $hacia->insumoid);
    }

    private function asegurarVariedadSiFalta(
        string $nombre,
        int $tipoSemillaId,
        int $kgId,
        ?int $almacenAgricolaId,
        float $stockInicial
    ): void {
        if (Insumo::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->exists()) {
            return;
        }

        $insumo = new Insumo([
            'nombre' => $nombre,
            'tipoinsumoid' => $tipoSemillaId,
            'unidadmedidaid' => $kgId,
            'stock' => $stockInicial,
            'stockminimo' => InsumoCatalogo::UMBRAL_ALERTA_STOCK,
        ]);
        if ($almacenAgricolaId) {
            $insumo->almacenid = $almacenAgricolaId;
        }
        $insumo->save();
    }
};
