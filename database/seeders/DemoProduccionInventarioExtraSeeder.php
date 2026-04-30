<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\EstadoLoteInsumo;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteInsumo;
use App\Models\MaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Models\Produccion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoProduccionInventarioExtraSeeder extends Seeder
{
    private const MARK = '[DEMO-XTRA1]';

    public function run(): void
    {
        $procesos = $this->seedProcesosPlanta();
        $maquinas = $this->seedMaquinasPlanta();
        $this->asociarProduccionesProcesoMaquina($procesos, $maquinas);
        $this->seedLoteInsumoAplicaciones();
        $this->seedProduccionAlmacenamientoExtra();
    }

    /**
     * @return array<string, ProcesoPlanta>
     */
    private function seedProcesosPlanta(): array
    {
        $out = [];

        if (! Schema::hasTable('proceso_planta')) {
            return $out;
        }

        $defs = [
            ['nombre' => 'Lavado y selección', 'descripcion' => 'Limpieza inicial y selección de productos agrícolas.', 'activo' => true],
            ['nombre' => 'Clasificación por calidad', 'descripcion' => 'Separación por tamaño, madurez y estado del producto.', 'activo' => true],
            ['nombre' => 'Empaque', 'descripcion' => 'Empaquetado del producto para almacenamiento o distribución.', 'activo' => true],
            ['nombre' => 'Control de calidad', 'descripcion' => 'Verificación final antes de certificación o envío.', 'activo' => true],
        ];

        foreach ($defs as $d) {
            $p = ProcesoPlanta::updateOrCreate(
                ['nombre' => $d['nombre']],
                ['descripcion' => $d['descripcion'], 'activo' => $d['activo']]
            );
            $out[$d['nombre']] = $p;
        }

        return $out;
    }

    /**
     * @return array<string, MaquinaPlanta>
     */
    private function seedMaquinasPlanta(): array
    {
        $out = [];

        if (! Schema::hasTable('maquina_planta')) {
            return $out;
        }

        $defs = [
            ['nombre' => 'Lavadora Industrial L-100', 'codigo' => 'L-100', 'tipo' => 'Lavado', 'ubicacion' => 'Área de limpieza', 'activo' => true],
            ['nombre' => 'Banda Clasificadora BC-20', 'codigo' => 'BC-20', 'tipo' => 'Clasificación', 'ubicacion' => 'Área de selección', 'activo' => true],
            ['nombre' => 'Selladora de Empaque SE-10', 'codigo' => 'SE-10', 'tipo' => 'Empaque', 'ubicacion' => 'Área de empaquetado', 'activo' => false],
            ['nombre' => 'Balanza Digital BD-500', 'codigo' => 'BD-500', 'tipo' => 'Pesaje', 'ubicacion' => 'Control de calidad', 'activo' => true],
        ];

        foreach ($defs as $d) {
            $desc = 'Tipo: '.$d['tipo'].'. Ubicación: '.$d['ubicacion'].'. '.self::MARK;
            $m = MaquinaPlanta::updateOrCreate(
                ['nombre' => $d['nombre']],
                ['codigo' => $d['codigo'], 'descripcion' => $desc, 'activo' => $d['activo']]
            );
            $out[$d['nombre']] = $m;
        }

        return $out;
    }

    /**
     * @param  array<string, ProcesoPlanta>  $procesos
     * @param  array<string, MaquinaPlanta>  $maquinas
     */
    private function asociarProduccionesProcesoMaquina(array $procesos, array $maquinas): void
    {
        if (! Schema::hasTable('produccion')) {
            return;
        }

        $cols = Schema::getColumnListing('produccion');
        $tieneProceso = in_array('procesoplantaid', $cols, true);
        $tieneMaquina = in_array('maquinaplantaid', $cols, true);

        if (! $tieneProceso && ! $tieneMaquina) {
            return;
        }

        $mapa = [
            'Tomate' => ['proceso' => 'Lavado y selección', 'maquina' => 'Lavadora Industrial L-100'],
            'Papa' => ['proceso' => 'Clasificación por calidad', 'maquina' => 'Banda Clasificadora BC-20'],
            'Lechuga' => ['proceso' => 'Empaque', 'maquina' => 'Selladora de Empaque SE-10'],
            'Cebolla' => ['proceso' => 'Control de calidad', 'maquina' => 'Balanza Digital BD-500'],
        ];

        foreach ($mapa as $cultivoNombre => $refs) {
            $prod = $this->ultimaProduccionPorCultivo($cultivoNombre);
            if (! $prod) {
                continue;
            }

            $payload = [];
            if ($tieneProceso && isset($procesos[$refs['proceso']])) {
                $payload['procesoplantaid'] = $procesos[$refs['proceso']]->procesoplantaid;
            }
            if ($tieneMaquina && isset($maquinas[$refs['maquina']])) {
                $payload['maquinaplantaid'] = $maquinas[$refs['maquina']]->maquinaplantaid;
            }

            if ($payload !== []) {
                $prod->update($payload);
            }
        }
    }

    private function ultimaProduccionPorCultivo(string $cultivoNombre): ?Produccion
    {
        $slug = mb_strtolower(trim($cultivoNombre));

        return Produccion::query()
            ->join('lote', 'produccion.loteid', '=', 'lote.loteid')
            ->join('cultivo', 'lote.cultivoid', '=', 'cultivo.cultivoid')
            ->whereRaw('LOWER(TRIM(cultivo.nombre)) = ?', [$slug])
            ->orderByDesc('produccion.produccionid')
            ->select('produccion.*')
            ->first();
    }

    private function seedLoteInsumoAplicaciones(): void
    {
        if (! Schema::hasTable('loteinsumo') || ! Schema::hasTable('estadoloteinsumo')) {
            $this->command?->warn(self::MARK.' Aplicaciones de insumo omitidas: tabla loteinsumo/estadoloteinsumo no existe.');

            return;
        }

        $estadoAplicado = EstadoLoteInsumo::firstOrCreate(
            ['nombre' => 'Aplicado'],
            ['nombre' => 'Aplicado']
        );

        $agricultor = Usuario::where('email', 'agricultor@agronexus.com')->first();
        if (! $agricultor) {
            return;
        }

        $items = [
            [
                'obs' => self::MARK.' Fertilizante Lote Norte A1',
                'lote' => 'Lote Norte A1',
                'insumoPrefer' => ['fertilizante', 'npk'],
                'fallbackNombreInsumo' => 'Tomate',
                'cantidad' => 25,
                'fecha' => '2026-04-18 09:00:00',
                'texto' => 'Aplicación preventiva para mejora de rendimiento.',
            ],
            [
                'obs' => self::MARK.' Semilla Lote Este B2',
                'lote' => 'Lote Este B2',
                'insumoPrefer' => ['semilla'],
                'fallbackNombreInsumo' => 'Papa',
                'cantidad' => 40,
                'fecha' => '2026-04-19 10:00:00',
                'texto' => 'Aplicación registrada para etapa de cultivo.',
            ],
            [
                'obs' => self::MARK.' Control plagas Lote Sur C3',
                'lote' => 'Lote Sur C3',
                'insumoPrefer' => ['bio', 'foliar', 'plaga'],
                'fallbackNombreInsumo' => 'Lechuga',
                'cantidad' => 12,
                'fecha' => '2026-04-20 11:00:00',
                'texto' => 'Control preventivo de plagas.',
            ],
        ];

        foreach ($items as $item) {
            $lote = Lote::where('nombre', $item['lote'])->first();
            if (! $lote) {
                continue;
            }

            $insumo = $this->resolverInsumoParaLote($item['insumoPrefer'], $item['fallbackNombreInsumo']);
            if (! $insumo) {
                continue;
            }

            LoteInsumo::firstOrCreate(
                ['observaciones' => $item['obs']],
                [
                    'loteid' => $lote->loteid,
                    'insumoid' => $insumo->insumoid,
                    'usuarioid' => $agricultor->usuarioid,
                    'cantidadusada' => $item['cantidad'],
                    'fechauo' => Carbon::parse($item['fecha']),
                    'costototal' => null,
                    'estadoloteinsumoid' => $estadoAplicado->estadoloteinsumoid,
                ]
            );
        }
    }

    /**
     * @param  array<int, string>  $preferTokens  palabras clave en nombre insumo (minúsculas)
     */
    private function resolverInsumoParaLote(array $preferTokens, string $fallbackNombreExacto): ?Insumo
    {
        $q = Insumo::query();
        foreach ($preferTokens as $tok) {
            $t = Insumo::whereRaw('LOWER(nombre) LIKE ?', ['%'.$tok.'%'])->orderBy('insumoid')->first();
            if ($t) {
                return $t;
            }
        }

        return Insumo::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($fallbackNombreExacto))])->first()
            ?? Insumo::where('nombre', $fallbackNombreExacto)->first();
    }

    private function seedProduccionAlmacenamientoExtra(): void
    {
        if (! Schema::hasTable('produccionalmacenamiento')) {
            $this->command?->warn(self::MARK.' Almacenamiento omitido: tabla produccionalmacenamiento no existe.');

            return;
        }

        $defs = [
            ['cultivo' => 'Tomate', 'almacen' => 'Almacén Central Santa Cruz', 'cantidad' => 1500, 'unidad' => 'kg', 'fecha' => '2026-04-22 08:00:00', 'obs' => 'Zona A - Estante 1 · Estado: Disponible · '.self::MARK.' Tomate'],
            ['cultivo' => 'Papa', 'almacen' => 'Almacén Central Santa Cruz', 'cantidad' => 2300, 'unidad' => 'kg', 'fecha' => '2026-04-23 08:00:00', 'obs' => 'Zona B - Estante 2 · Estado: Disponible · '.self::MARK.' Papa'],
            ['cultivo' => 'Lechuga', 'almacen' => 'Almacén Norte', 'cantidad' => 800, 'unidad' => 'und', 'fecha' => '2026-04-24 07:30:00', 'obs' => 'Cámara fría 1 · Estado: Refrigerado · '.self::MARK.' Lechuga'],
            ['cultivo' => 'Cebolla', 'almacen' => 'Almacén Planta Procesadora', 'cantidad' => 1200, 'unidad' => 'kg', 'fecha' => '2026-04-25 09:00:00', 'obs' => 'Zona C · Estado: Disponible · '.self::MARK.' Cebolla'],
        ];

        foreach ($defs as $d) {
            $prod = $this->ultimaProduccionPorCultivo($d['cultivo']);
            $alm = Almacen::where('nombre', $d['almacen'])->first();
            $umId = $this->unidadIdPorClave($d['unidad']);

            if (! $prod || ! $alm || ! $umId) {
                continue;
            }

            ProduccionAlmacenamiento::firstOrCreate(
                [
                    'produccionid' => $prod->produccionid,
                    'almacenid' => $alm->almacenid,
                    'observaciones' => $d['obs'],
                ],
                [
                    'cantidad' => $d['cantidad'],
                    'unidadmedidaid' => $umId,
                    'fechaentrada' => Carbon::parse($d['fecha']),
                    'fechasalida' => null,
                ]
            );
        }
    }

    private function unidadIdPorClave(string $clave): ?int
    {
        return match (mb_strtolower($clave)) {
            'kg' => UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['kilogramo'])->value('unidadmedidaid')
                ?? UnidadMedida::where('nombre', 'Kilogramo')->value('unidadmedidaid'),
            'und' => UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['unidad'])->value('unidadmedidaid')
                ?? UnidadMedida::where('nombre', 'Unidad')->value('unidadmedidaid'),
            default => null,
        };
    }
}
