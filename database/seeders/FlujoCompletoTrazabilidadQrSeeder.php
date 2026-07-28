<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\Almacen;
use App\Models\AlmacenajeLoteProduccion;
use App\Models\AlmacenMovimiento;
use App\Models\CertificacionLote;
use App\Models\Cultivo;
use App\Models\DetallePedido;
use App\Models\DetallePedidoDistribucion;
use App\Models\DetalleTrasladoPlantaMayorista;
use App\Models\EstadoLoteInsumo;
use App\Models\EnvioAsignacionMultiple;
use App\Models\EvaluacionFinalLoteProduccion;
use App\Models\HistorialEstadoLote;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\Lote;
use App\Models\LoteInsumo;
use App\Models\LoteProduccionPedido;
use App\Models\MaquinaPlanta;
use App\Models\Pedido;
use App\Models\PedidoDistribucion;
use App\Models\Prioridad;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Models\Produccion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\PuntoVenta;
use App\Models\RegistroProcesoMaquinaPlanta;
use App\Models\RutaDistribucion;
use App\Models\TipoActividad;
use App\Models\TipoAlmacen;
use App\Models\TipoEmpaque;
use App\Models\TipoInsumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Services\PuntoVentaAlmacenService;
use App\Support\AlmacenAmbito;
use App\Support\EstadoLoteCatalogo;
use App\Support\InsumoCatalogo;
use App\Support\LoteDefaults;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\RutaDistribucionCatalogo;
use App\Support\TrazabilidadProductoPdvService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Flujo completo de trazabilidad para demo / pitch:
 * Campo (siembra → insumos → cosecha) → envío a planta → procesamiento con máquinas
 * → traslado a mayorista → distribución a minorista (PDV) → QR público.
 *
 * php artisan db:seed --class=FlujoCompletoTrazabilidadQrSeeder
 */
class FlujoCompletoTrazabilidadQrSeeder extends Seeder
{
    private const MARK = '[FLUJO-COMPLETO-QR]';

    private const LOTE_CODIGO = 'TRAZ-FLUJO-2026-001';

    private const LOTE_NOMBRE = 'Lote Zanahoria Flujo Completo';

    private const PRODUCTO = 'Zanahoria Imperator envasada';

    private const PRODUCTO_PDV = 'Zanahoria Imperator envasada · Bolsa 1 kg';

    private const LOTE_PLANTA_CODIGO = 'LPP-FLUJO-2026-001';

    private const PEDIDO_AGRICOLA = 'PED-FLUJO-2026-001';

    private const ENVIO_AGRICOLA = 'ENV-FLUJO-2026-001';

    private const RUTA_MAYORISTA = 'RUT-PM-FLUJO-001';

    private const PEDIDO_PDV = 'PDV-FLUJO-2026-001';

    private const QR_CODIGO = 'TRZ-PDV-FLUJO-202601';

    private const COSECHA_KG = 500.0;

    private const STOCK_PDV_KG = 40.0;

    private const UNIDADES_PDV = 40;

    public function run(): void
    {
        if (! Schema::hasTable('lote') || ! Schema::hasTable('insumo')) {
            $this->command?->error('Tablas lote/insumo no disponibles.');

            return;
        }

        $this->asegurarCatalogos();

        DB::transaction(function () {
            $this->limpiarDemoAnterior();

            $usuarios = $this->resolverUsuarios();
            $almacenes = $this->resolverAlmacenes();
            $kgId = $this->unidadKgId();
            $haId = LoteDefaults::unidadHectareaId() ?? $kgId;
            $prioridadId = Prioridad::query()->whereRaw('LOWER(nombre) = ?', ['media'])->value('prioridadid')
                ?? Prioridad::query()->value('prioridadid');

            $lote = $this->seedLoteAgricola($usuarios['agricultor'], $haId);
            $this->seedActividadesEInsumos($lote, $usuarios['agricultor'], $almacenes['agricola'], $kgId, $prioridadId);
            $produccionAlmacen = $this->seedCosechaYCertificacion($lote, $usuarios['agricultor'], $almacenes['agricola'], $kgId);
            $pedidoAgricola = $this->seedPedidoEnvioPlanta(
                $lote,
                $produccionAlmacen,
                $usuarios,
                $almacenes['agricola']
            );
            $lotePlanta = $this->seedProcesamientoPlanta(
                $lote,
                $pedidoAgricola,
                $usuarios['planta'],
                $almacenes['planta'],
                $kgId
            );
            [$insumoPlanta, $presentacionPlanta] = $this->seedProductoTerminadoPlanta(
                $lotePlanta,
                $almacenes['planta'],
                $usuarios['planta'],
                $kgId
            );
            $this->seedTrasladoMayorista(
                $insumoPlanta,
                $presentacionPlanta,
                $lotePlanta,
                $usuarios,
                $almacenes
            );
            $insumoMayorista = $this->seedStockMayorista(
                $almacenes['mayorista'],
                $kgId,
                $usuarios['mayorista'] ?? $usuarios['admin']
            );
            $presentacionMay = $this->asegurarPresentacionBolsa1Kg($insumoMayorista);
            $punto = $this->resolverPuntoVenta($usuarios['minorista'] ?? $usuarios['admin']);
            $almacenPdv = app(PuntoVentaAlmacenService::class)->crearAlmacenParaPuntoVenta($punto);
            $punto->refresh();

            $this->seedPedidoDistribucionPdv(
                $punto,
                $almacenes,
                $insumoMayorista,
                $presentacionMay,
                $usuarios['admin']
            );

            $insumoPdv = Insumo::updateOrCreate(
                [
                    'nombre' => self::PRODUCTO_PDV,
                    'almacenid' => $almacenPdv->almacenid,
                ],
                [
                    'tipoinsumoid' => InsumoCatalogo::tipoProductoTerminadoId()
                        ?? TipoInsumo::query()->value('tipoinsumoid'),
                    'unidadmedidaid' => $kgId,
                    'stock' => self::STOCK_PDV_KG,
                    'stockminimo' => 5,
                    'descripcion' => 'Producto procesado recibido del mayorista — trazabilidad lote '
                        .self::LOTE_CODIGO.' — '.self::PEDIDO_PDV.' '.self::MARK,
                    'codigo_trazabilidad' => self::QR_CODIGO,
                ]
            );

            $this->seedMovimientoRecepcionPdv($punto, $insumoPdv, $usuarios['admin']);

            $urlQr = app(TrazabilidadProductoPdvService::class)->urlPublica($insumoPdv);

            $this->command?->newLine();
            $this->command?->info('══════════════════════════════════════════════════════');
            $this->command?->info('  FLUJO COMPLETO LISTO — AgroFusion');
            $this->command?->info('══════════════════════════════════════════════════════');
            $this->command?->info('  Lote agrícola:  '.self::LOTE_NOMBRE.' ('.self::LOTE_CODIGO.')');
            $this->command?->info('  Lote planta:    '.self::LOTE_PLANTA_CODIGO);
            $this->command?->info('  Producto PDV:   '.self::PRODUCTO_PDV);
            $this->command?->info('  Código QR:      '.self::QR_CODIGO);
            $this->command?->info('  URL pública:    '.$urlQr);
            $this->command?->info('  Lotes web:      '.rtrim((string) config('app.url'), '/').'/lotes');
            $this->command?->info('══════════════════════════════════════════════════════');
        });
    }

    private function asegurarCatalogos(): void
    {
        // Roles + usuarios demo (necesario en BD vacía de Railway)
        if (class_exists(RoleSeeder::class)) {
            $this->call(RoleSeeder::class);
        }
        if (class_exists(ConsolidacionRolesPermisosSeeder::class)) {
            $this->call(ConsolidacionRolesPermisosSeeder::class);
        }
        if (class_exists(RolePermissionSeeder::class)) {
            $this->call(RolePermissionSeeder::class);
        }
        if (class_exists(AdminUserSeeder::class)) {
            $this->call(AdminUserSeeder::class);
        }
        if (class_exists(CreateOperationalRoleUsersSeeder::class)) {
            $this->call(CreateOperationalRoleUsersSeeder::class);
        }
        if (class_exists(MayoristaDemoSeeder::class)) {
            $this->call(MayoristaDemoSeeder::class);
        }

        $this->call(CatalogosOperacionAgricolaSeeder::class);
        InsumoCatalogo::asegurarCatalogosBase();
        TipoInsumo::firstOrCreate(
            ['nombre' => 'Producto terminado'],
            ['nombre' => 'Producto terminado']
        );

        if (Schema::hasTable('proceso_planta')) {
            $this->call(ProcesosPlantaOperativosSeeder::class);
        }
        if (Schema::hasTable('maquina_planta')) {
            $this->call(MaquinasProcesoPlantaSeeder::class);
        }

        if (Schema::hasTable('estadolote_tipo')) {
            foreach (['planificado', 'sembrado', 'en_crecimiento', 'listo_para_cosecha', 'cosechado', 'certificado', 'finalizado'] as $slug) {
                EstadoLoteCatalogo::idPorSlug($slug);
            }
        }
    }

    private function limpiarDemoAnterior(): void
    {
        $loteIds = Lote::query()
            ->where('codigo_trazabilidad', self::LOTE_CODIGO)
            ->orWhere('nombre', self::LOTE_NOMBRE)
            ->pluck('loteid');

        if ($loteIds->isNotEmpty()) {
            Actividad::query()->whereIn('loteid', $loteIds)->delete();
            LoteInsumo::query()->whereIn('loteid', $loteIds)->delete();
            CertificacionLote::query()->whereIn('loteid', $loteIds)->delete();
            HistorialEstadoLote::query()->whereIn('loteid', $loteIds)->delete();

            $prodIds = Produccion::query()->whereIn('loteid', $loteIds)->pluck('produccionid');
            if ($prodIds->isNotEmpty() && Schema::hasTable('produccionalmacenamiento')) {
                ProduccionAlmacenamiento::query()->whereIn('produccionid', $prodIds)->delete();
            }
            Produccion::query()->whereIn('loteid', $loteIds)->delete();
            RegistroProcesoMaquinaPlanta::query()->whereIn('loteid', $loteIds)->delete();
        }

        $pedidoAgr = Pedido::query()->where('numero_solicitud', self::PEDIDO_AGRICOLA)->first();
        if ($pedidoAgr) {
            EnvioAsignacionMultiple::query()->where('pedidoid', $pedidoAgr->pedidoid)->delete();
            DetallePedido::query()->where('pedidoid', $pedidoAgr->pedidoid)->delete();
            $pedidoAgr->delete();
        }

        $lotePlanta = LoteProduccionPedido::query()
            ->where('codigo_lote', self::LOTE_PLANTA_CODIGO)
            ->first();
        if ($lotePlanta) {
            EvaluacionFinalLoteProduccion::query()
                ->where('loteproduccionpedidoid', $lotePlanta->loteproduccionpedidoid)
                ->delete();
            AlmacenajeLoteProduccion::query()
                ->where('loteproduccionpedidoid', $lotePlanta->loteproduccionpedidoid)
                ->delete();
            RegistroProcesoMaquinaPlanta::query()
                ->where('loteproduccionpedidoid', $lotePlanta->loteproduccionpedidoid)
                ->delete();
            $lotePlanta->delete();
        }

        $ruta = RutaDistribucion::query()->where('codigo', self::RUTA_MAYORISTA)->first();
        if ($ruta) {
            DetalleTrasladoPlantaMayorista::query()
                ->where('rutadistribucionid', $ruta->rutadistribucionid)
                ->delete();
            $ruta->delete();
        }

        $pedidoPdv = PedidoDistribucion::query()
            ->where('numero_solicitud', self::PEDIDO_PDV)
            ->orWhere('observaciones', 'like', '%'.self::MARK.'%')
            ->get();
        foreach ($pedidoPdv as $p) {
            DetallePedidoDistribucion::query()
                ->where('pedidodistribucionid', $p->pedidodistribucionid)
                ->delete();
            AlmacenMovimiento::query()->where('referencia', $p->numero_solicitud)->delete();
            $p->delete();
        }

        Insumo::query()
            ->where('codigo_trazabilidad', self::QR_CODIGO)
            ->orWhere('descripcion', 'like', '%'.self::MARK.'%')
            ->delete();

        AlmacenMovimiento::query()
            ->where('observaciones', 'like', '%'.self::MARK.'%')
            ->orWhere('referencia', self::ENVIO_AGRICOLA)
            ->orWhere('referencia', self::RUTA_MAYORISTA)
            ->delete();

        if ($loteIds->isNotEmpty()) {
            Lote::query()->whereIn('loteid', $loteIds)->delete();
        }
    }

    /** @return array{admin: Usuario, agricultor: Usuario, planta: Usuario, transportista: ?Usuario, mayorista: ?Usuario, minorista: ?Usuario} */
    private function resolverUsuarios(): array
    {
        $admin = Usuario::query()->where('role', 'admin')->where('activo', true)->first()
            ?? Usuario::query()->where('activo', true)->first();

        if (! $admin) {
            throw new \RuntimeException('No hay usuario admin. Ejecute AdminUserSeeder primero.');
        }

        return [
            'admin' => $admin,
            'agricultor' => Usuario::query()->where('role', 'agricultor')->first() ?? $admin,
            'planta' => Usuario::query()->where('role', 'planta')->first() ?? $admin,
            'transportista' => Usuario::query()->where('role', 'transportista')->first(),
            'mayorista' => Usuario::query()->where('role', 'mayorista')->first(),
            'minorista' => Usuario::query()->where('role', 'minorista')->first()
                ?? Usuario::query()->where('email', 'minorista@agrofusion.com')->first(),
        ];
    }

    /** @return array{agricola: Almacen, planta: Almacen, mayorista: Almacen} */
    private function resolverAlmacenes(): array
    {
        $kgId = $this->unidadKgId();

        $agricola = Almacen::query()
            ->where('activo', true)
            ->where(function ($q) {
                $q->where('ambito', AlmacenAmbito::AGRICOLA)
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%agrícola%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%agricola%']);
            })
            ->orderBy('almacenid')
            ->first();

        if (! $agricola) {
            $tipo = TipoAlmacen::firstOrCreate(
                ['nombre' => 'Agrícola'],
                ['descripcion' => 'Almacén agrícola']
            );
            $agricola = Almacen::create([
                'nombre' => 'Almacén Agrícola Flujo Demo',
                'descripcion' => 'Almacén campo — '.self::MARK,
                'ubicacion' => 'Valle de Tiquipaya, Cochabamba',
                'capacidad' => 5000,
                'unidadmedidaid' => $kgId,
                'tipoalmacenid' => $tipo->tipoalmacenid,
                'ambito' => AlmacenAmbito::AGRICOLA,
                'activo' => true,
            ]);
        }

        $planta = Almacen::query()
            ->where('activo', true)
            ->where('ambito', AlmacenAmbito::PLANTA)
            ->orderBy('almacenid')
            ->first();

        if (! $planta) {
            $tipo = TipoAlmacen::firstOrCreate(
                ['nombre' => 'Planta'],
                ['descripcion' => 'Almacén de planta']
            );
            $planta = Almacen::create([
                'nombre' => 'Almacén Planta Flujo Demo',
                'descripcion' => 'Productos terminados — '.self::MARK,
                'ubicacion' => 'Parque Industrial, Santa Cruz',
                'capacidad' => 10000,
                'unidadmedidaid' => $kgId,
                'tipoalmacenid' => $tipo->tipoalmacenid,
                'ambito' => AlmacenAmbito::PLANTA,
                'activo' => true,
            ]);
        }

        $mayorista = Almacen::query()
            ->where('activo', true)
            ->where('ambito', AlmacenAmbito::MAYORISTA)
            ->orderBy('almacenid')
            ->first();

        if (! $mayorista) {
            $tipo = TipoAlmacen::firstOrCreate(
                ['nombre' => 'Mayorista'],
                ['descripcion' => 'Centro mayorista']
            );
            $mayorista = Almacen::create([
                'nombre' => 'Centro Mayorista Flujo Demo',
                'descripcion' => 'Distribución a PDV — '.self::MARK,
                'ubicacion' => 'Av. Grigotá, Santa Cruz',
                'capacidad' => 8000,
                'unidadmedidaid' => $kgId,
                'tipoalmacenid' => $tipo->tipoalmacenid,
                'ambito' => AlmacenAmbito::MAYORISTA,
                'activo' => true,
            ]);
        }

        return compact('agricola', 'planta', 'mayorista');
    }

    private function seedLoteAgricola(Usuario $agricultor, ?int $haId): Lote
    {
        $cultivo = Cultivo::firstOrCreate(['nombre' => 'Zanahoria'], ['nombre' => 'Zanahoria']);
        $estadoFinal = EstadoLoteCatalogo::idPorSlug('finalizado')
            ?? EstadoLoteCatalogo::idPorSlug('cosechado')
            ?? EstadoLoteCatalogo::idPorSlug('certificado');

        $data = LoteDefaults::enrich([
            'nombre' => self::LOTE_NOMBRE,
            'codigo_trazabilidad' => self::LOTE_CODIGO,
            'cultivoid' => $cultivo->cultivoid,
            'usuarioid' => $agricultor->usuarioid,
            'estadolotetipoid' => $estadoFinal,
            'superficie' => 2.8,
            'unidadsuperficieid' => $haId,
            'ubicacion' => 'Valle de Tiquipaya, Cochabamba — Parcela Flujo',
            'latitud' => -17.3380,
            'longitud' => -66.2150,
        ], false);

        $lote = Lote::updateOrCreate(
            ['codigo_trazabilidad' => self::LOTE_CODIGO],
            $data
        );

        $lote->fechasiembra = Carbon::now()->subDays(90);
        $lote->save();

        $historial = [
            ['slug' => 'planificado', 'dias' => 95, 'obs' => 'Lote creado y planificado'],
            ['slug' => 'sembrado', 'dias' => 90, 'obs' => 'Siembra de zanahoria Imperator'],
            ['slug' => 'en_crecimiento', 'dias' => 70, 'obs' => 'Manejo en campo: riego y fertilización'],
            ['slug' => 'listo_para_cosecha', 'dias' => 35, 'obs' => 'Listo para cosecha'],
            ['slug' => 'cosechado', 'dias' => 30, 'obs' => 'Cosecha registrada'],
            ['slug' => 'certificado', 'dias' => 28, 'obs' => 'Certificación de calidad en campo'],
            ['slug' => 'finalizado', 'dias' => 27, 'obs' => 'Producto enviado a almacén agrícola / planta'],
        ];

        foreach ($historial as $h) {
            $estadoId = EstadoLoteCatalogo::idPorSlug($h['slug']);
            if (! $estadoId) {
                continue;
            }
            HistorialEstadoLote::firstOrCreate(
                [
                    'loteid' => $lote->loteid,
                    'estadolotetipoid' => $estadoId,
                    'observaciones' => $h['obs'].' '.self::MARK,
                ],
                [
                    'fecha_cambio' => Carbon::now()->subDays($h['dias']),
                    'usuarioid' => $agricultor->usuarioid,
                ]
            );
        }

        return $lote->fresh();
    }

    private function seedActividadesEInsumos(
        Lote $lote,
        Usuario $agricultor,
        Almacen $almacenAgricola,
        ?int $kgId,
        ?int $prioridadId
    ): void {
        $tipoSiembra = TipoActividad::query()->whereRaw('LOWER(nombre) = ?', ['siembra'])->value('tipoactividadid');
        $tipoRiego = TipoActividad::query()->whereRaw('LOWER(nombre) = ?', ['riego'])->value('tipoactividadid');
        $tipoFert = TipoActividad::query()->whereRaw('LOWER(nombre) LIKE ?', ['%fertiliz%'])->value('tipoactividadid');
        $tipoPlaga = TipoActividad::query()->whereRaw('LOWER(nombre) LIKE ?', ['%plaga%'])->value('tipoactividadid');
        $tipoCosecha = TipoActividad::query()->whereRaw('LOWER(nombre) = ?', ['cosecha'])->value('tipoactividadid');

        $tipoSemilla = TipoInsumo::query()->whereRaw('LOWER(nombre) LIKE ?', ['%siembra%'])->value('tipoinsumoid')
            ?? TipoInsumo::query()->value('tipoinsumoid');
        $tipoFertilizante = TipoInsumo::query()->whereRaw('LOWER(nombre) LIKE ?', ['%fertiliz%'])->value('tipoinsumoid')
            ?? $tipoSemilla;
        $tipoPesticida = TipoInsumo::query()->whereRaw('LOWER(nombre) LIKE ?', ['%plaga%'])->value('tipoinsumoid')
            ?? $tipoSemilla;

        $estadoInsumoAplicado = EstadoLoteInsumo::firstOrCreate(
            ['nombre' => 'Aplicado'],
            ['nombre' => 'Aplicado']
        )->estadoloteinsumoid;

        $semilla = Insumo::updateOrCreate(
            ['nombre' => 'Semilla Zanahoria Imperator', 'almacenid' => $almacenAgricola->almacenid],
            [
                'tipoinsumoid' => $tipoSemilla,
                'unidadmedidaid' => $kgId,
                'stock' => 80,
                'stockminimo' => 5,
                'descripcion' => 'Material de siembra — '.self::MARK,
            ]
        );
        $fertilizante = Insumo::updateOrCreate(
            ['nombre' => 'Fertilizante NPK 15-15-15', 'almacenid' => $almacenAgricola->almacenid],
            [
                'tipoinsumoid' => $tipoFertilizante,
                'unidadmedidaid' => $kgId,
                'stock' => 200,
                'stockminimo' => 20,
                'descripcion' => 'Fertilización de crecimiento — '.self::MARK,
            ]
        );
        $pesticida = Insumo::updateOrCreate(
            ['nombre' => 'Bioinsecticida neem', 'almacenid' => $almacenAgricola->almacenid],
            [
                'tipoinsumoid' => $tipoPesticida,
                'unidadmedidaid' => $kgId,
                'stock' => 50,
                'stockminimo' => 5,
                'descripcion' => 'Control de plagas — '.self::MARK,
            ]
        );

        $lote->insumosemillaid = $semilla->insumoid;
        $lote->cantidad_semilla_planificada = 12;
        $lote->save();

        $actividades = [
            [
                'tipo' => $tipoSiembra,
                'desc' => 'Siembra de zanahoria Imperator',
                'inicio' => 90,
                'fin' => 90,
                'orden' => 1,
                'insumo' => $semilla,
                'cant' => 12,
            ],
            [
                'tipo' => $tipoRiego,
                'desc' => 'Riego por goteo — primera semana',
                'inicio' => 85,
                'fin' => 85,
                'orden' => 2,
                'insumo' => null,
                'cant' => null,
            ],
            [
                'tipo' => $tipoFert,
                'desc' => 'Fertilización NPK en crecimiento',
                'inicio' => 70,
                'fin' => 70,
                'orden' => 3,
                'insumo' => $fertilizante,
                'cant' => 45,
            ],
            [
                'tipo' => $tipoPlaga,
                'desc' => 'Aplicación de bioinsecticida',
                'inicio' => 55,
                'fin' => 55,
                'orden' => 4,
                'insumo' => $pesticida,
                'cant' => 8,
            ],
            [
                'tipo' => $tipoRiego,
                'desc' => 'Riego de mantenimiento pre-cosecha',
                'inicio' => 40,
                'fin' => 40,
                'orden' => 5,
                'insumo' => null,
                'cant' => null,
            ],
            [
                'tipo' => $tipoCosecha,
                'desc' => 'Cosecha manual de zanahoria',
                'inicio' => 30,
                'fin' => 30,
                'orden' => 6,
                'insumo' => null,
                'cant' => null,
            ],
        ];

        foreach ($actividades as $a) {
            if (! $a['tipo']) {
                continue;
            }

            $keys = [
                'loteid' => $lote->loteid,
                'tipoactividadid' => $a['tipo'],
                'descripcion' => $a['desc'],
            ];
            $vals = [
                'usuarioid' => $agricultor->usuarioid,
                'usuarioid_ejecutor' => $agricultor->usuarioid,
                'fechainicio' => Carbon::now()->subDays($a['inicio'])->setTime(8, 0),
                'fechafin' => Carbon::now()->subDays($a['fin'])->setTime(12, 0),
                'prioridadid' => $prioridadId,
                'observaciones' => self::MARK,
            ];
            if (Schema::hasColumn('actividad', 'orden_secuencia')) {
                $keys['orden_secuencia'] = $a['orden'];
                $vals['orden_secuencia'] = $a['orden'];
            }

            $actividad = Actividad::updateOrCreate($keys, $vals);

            if ($a['insumo'] && $a['cant'] !== null) {
                LoteInsumo::updateOrCreate(
                    [
                        'loteid' => $lote->loteid,
                        'insumoid' => $a['insumo']->insumoid,
                        'actividadid' => $actividad->actividadid,
                    ],
                    [
                        'usuarioid' => $agricultor->usuarioid,
                        'cantidadusada' => $a['cant'],
                        'fechauo' => Carbon::now()->subDays($a['inicio']),
                        'estadoloteinsumoid' => $estadoInsumoAplicado,
                        'observaciones' => 'Aplicación en campo — '.self::MARK,
                    ]
                );
            }
        }
    }

    private function seedCosechaYCertificacion(
        Lote $lote,
        Usuario $agricultor,
        Almacen $almacenAgricola,
        ?int $kgId
    ): ?ProduccionAlmacenamiento {
        $destinoId = null;
        if (Schema::hasTable('destinoproduccion')) {
            $destinoId = DB::table('destinoproduccion')
                ->whereRaw('LOWER(nombre) LIKE ?', ['%almacen%'])
                ->orWhereRaw('LOWER(nombre) LIKE ?', ['%planta%'])
                ->value('destinoproduccionid')
                ?? DB::table('destinoproduccion')->value('destinoproduccionid');
        }

        $produccion = Produccion::updateOrCreate(
            [
                'loteid' => $lote->loteid,
                'observaciones' => self::MARK.' Cosecha principal',
            ],
            [
                'cantidad' => self::COSECHA_KG,
                'unidadmedidaid' => $kgId,
                'cantidad_base' => self::COSECHA_KG,
                'fechacosecha' => Carbon::now()->subDays(30)->toDateString(),
                'destinoproduccionid' => $destinoId,
                'almacendestinoid' => $almacenAgricola->almacenid,
            ]
        );

        $pa = null;
        if (Schema::hasTable('produccionalmacenamiento')) {
            $pa = ProduccionAlmacenamiento::updateOrCreate(
                [
                    'produccionid' => $produccion->produccionid,
                    'almacenid' => $almacenAgricola->almacenid,
                ],
                [
                    'cantidad' => self::COSECHA_KG,
                    'unidadmedidaid' => $kgId,
                    'fechaentrada' => Carbon::now()->subDays(30),
                    'temperatura' => 8,
                    'humedad' => 75,
                    'observaciones' => 'Cosecha ingresada a almacén agrícola — '.self::MARK,
                ]
            );
        }

        if (Schema::hasTable('certificacion_lote')) {
            CertificacionLote::updateOrCreate(
                [
                    'loteid' => $lote->loteid,
                    'codigo_certificado' => 'CERT-FLUJO-2026-001',
                ],
                [
                    'usuarioid' => $agricultor->usuarioid,
                    'resultado' => CertificacionLote::RAZON_CERTIFICADO,
                    'observaciones' => 'Producto apto para procesamiento industrial. '.self::MARK,
                    'fecha_certificacion' => Carbon::now()->subDays(28),
                    'recomendaciones' => 'Mantener cadena de frío hasta planta.',
                ]
            );
        }

        return $pa;
    }

    private function seedPedidoEnvioPlanta(
        Lote $lote,
        ?ProduccionAlmacenamiento $pa,
        array $usuarios,
        Almacen $almacenAgricola
    ): Pedido {
        $pedido = Pedido::updateOrCreate(
            ['numero_solicitud' => self::PEDIDO_AGRICOLA],
            [
                'nombre_planta' => 'Planta Procesadora Santa Cruz',
                'origen_latitud' => -17.3380,
                'origen_longitud' => -66.2150,
                'origen_direccion' => $lote->ubicacion,
                'latitud' => -17.7833,
                'longitud' => -63.1821,
                'direccion_texto' => 'Parque Industrial El Trompillo, Santa Cruz',
                'estado' => 'confirmado',
                'fechapedido' => Carbon::now()->subDays(27),
                'fechaEntregaDeseada' => Carbon::now()->subDays(26)->toDateString(),
                'observaciones' => 'Pedido de materia prima zanahoria — lote '.self::LOTE_CODIGO.' '.self::MARK,
                'fecha_aceptacion_agricola' => Carbon::now()->subDays(27),
                'aceptado_por_usuarioid' => $usuarios['agricultor']->usuarioid,
            ]
        );

        DetallePedido::query()->where('pedidoid', $pedido->pedidoid)->delete();
        $detalle = [
            'pedidoid' => $pedido->pedidoid,
            'produccionalmacenamientoid' => $pa?->produccionalmacenamientoid,
            'cultivo_personalizado' => 'Zanahoria Imperator (cosecha fresca)',
            'cantidad' => self::COSECHA_KG,
            'observaciones' => 'Materia prima del lote '.self::LOTE_CODIGO,
        ];
        if (Schema::hasColumn('detallepedido', 'producto_ref')) {
            $detalle['producto_ref'] = $pa ? 'cosecha:'.$pa->produccionid : null;
        }
        if (Schema::hasColumn('detallepedido', 'nombre_planta')) {
            $detalle['nombre_planta'] = 'Planta Procesadora Santa Cruz';
        }
        DetallePedido::create($detalle);

        $transportistaId = $usuarios['transportista']?->usuarioid ?? $usuarios['admin']->usuarioid;

        EnvioAsignacionMultiple::updateOrCreate(
            ['externo_envio_id' => self::ENVIO_AGRICOLA],
            [
                'pedidoid' => $pedido->pedidoid,
                'transportista_usuarioid' => $transportistaId,
                'asignadopor_usuarioid' => $usuarios['admin']->usuarioid,
                'vehiculo_ref' => 'Camión refrigerado SCR-4521',
                'estado' => 'recibido_planta',
                'fecha_asignacion' => Carbon::now()->subDays(27)->setTime(7, 0),
                'simulacion_inicio_at' => Carbon::now()->subDays(27)->setTime(8, 30),
                'llegada_confirmada_at' => Carbon::now()->subDays(26)->setTime(16, 40),
                'fecha_recepcion_planta' => Carbon::now()->subDays(26)->setTime(17, 0),
                'recepcion_usuarioid' => $usuarios['planta']->usuarioid,
                'almacenid' => $almacenAgricola->almacenid,
            ]
        );

        return $pedido;
    }

    private function seedProcesamientoPlanta(
        Lote $lote,
        Pedido $pedido,
        Usuario $plantaUser,
        Almacen $almacenPlanta,
        ?int $kgId
    ): LoteProduccionPedido {
        $pasosLinea = [
            ['proceso' => 'Preparación de Materias Primas', 'maq' => 'L-100', 'orden' => 1, 'nombre' => 'Lavado y selección', 'horas_offset' => 0],
            ['proceso' => 'Secado', 'maq' => 'SC-500', 'orden' => 2, 'nombre' => 'Secado superficial', 'horas_offset' => 3],
            ['proceso' => 'Envasado', 'maq' => 'EV-700', 'orden' => 3, 'nombre' => 'Envasado al vacío', 'horas_offset' => 6],
            ['proceso' => 'Etiquetado', 'maq' => 'ET-800', 'orden' => 4, 'nombre' => 'Etiquetado con lote', 'horas_offset' => 8],
            ['proceso' => 'Empaquetado', 'maq' => 'SE-10', 'orden' => 5, 'nombre' => 'Empaque final Bolsa 1 kg', 'horas_offset' => 10],
            ['proceso' => 'Control de Calidad', 'maq' => 'BD-500', 'orden' => 6, 'nombre' => 'Control de peso y sellado', 'horas_offset' => 12],
        ];

        $inicio = Carbon::now()->subDays(25)->setTime(8, 0);

        $lotePlanta = LoteProduccionPedido::updateOrCreate(
            ['codigo_lote' => self::LOTE_PLANTA_CODIGO],
            [
                'pedidoid' => $pedido->pedidoid,
                'nombre' => self::PRODUCTO,
                'producto' => self::PRODUCTO,
                'fecha_creacion' => $inicio->toDateString(),
                'hora_inicio' => $inicio,
                'hora_fin' => $inicio->copy()->addHours(14),
                'cantidad_objetivo' => 420,
                'cantidad_producida' => 400,
                'unidadmedidaid' => $kgId,
                'empaque_nombre_personalizado' => 'Bolsa 1 kg',
                'empaque_peso_neto_kg' => 1.0,
                'empaque_tipo_envase' => 'bolsa',
                'observaciones' => 'Procesamiento de cosecha '.self::LOTE_CODIGO.' '.self::MARK,
            ]
        );

        RegistroProcesoMaquinaPlanta::query()
            ->where('loteproduccionpedidoid', $lotePlanta->loteproduccionpedidoid)
            ->delete();

        foreach ($pasosLinea as $paso) {
            $proceso = ProcesoPlanta::query()->where('nombre', $paso['proceso'])->first();
            $maquina = MaquinaPlanta::query()->where('codigo', $paso['maq'])->first();
            if (! $proceso || ! $maquina) {
                continue;
            }

            $pm = ProcesoMaquinaPlanta::updateOrCreate(
                [
                    'procesoplantaid' => $proceso->procesoplantaid,
                    'maquinaplantaid' => $maquina->maquinaplantaid,
                    'orden_paso' => $paso['orden'],
                ],
                [
                    'nombre' => $paso['nombre'],
                    'descripcion' => self::MARK.' Paso '.$paso['orden'],
                    'tiempo_estimado' => 90,
                ]
            );

            $hInicio = $inicio->copy()->addHours($paso['horas_offset']);
            RegistroProcesoMaquinaPlanta::create([
                'procesomaquinaplantaid' => $pm->procesomaquinaplantaid,
                'loteid' => $lote->loteid,
                'loteproduccionpedidoid' => $lotePlanta->loteproduccionpedidoid,
                'usuarioid' => $plantaUser->usuarioid,
                'variables_ingresadas' => json_encode(['estado' => 'ok', 'paso' => $paso['nombre']]),
                'cumple_estandar' => true,
                'observaciones' => $paso['nombre'].' OK — '.$maquina->nombre.' '.self::MARK,
                'hora_inicio' => $hInicio,
                'hora_fin' => $hInicio->copy()->addHours(2),
                'fecha_registro' => $hInicio,
            ]);
        }

        EvaluacionFinalLoteProduccion::updateOrCreate(
            [
                'loteproduccionpedidoid' => $lotePlanta->loteproduccionpedidoid,
                'razon' => EvaluacionFinalLoteProduccion::RAZON_CERTIFICADO,
            ],
            [
                'inspector_usuarioid' => $plantaUser->usuarioid,
                'observaciones' => 'Producto conforme: sellado correcto, peso promedio 1.00 kg, sin defectos visibles.',
                'recomendaciones' => 'Liberar a almacén de producto terminado.',
                'fecha_evaluacion' => $inicio->copy()->addHours(14),
            ]
        );

        AlmacenajeLoteProduccion::updateOrCreate(
            [
                'loteproduccionpedidoid' => $lotePlanta->loteproduccionpedidoid,
                'almacenid' => $almacenPlanta->almacenid,
            ],
            [
                'ubicacion' => 'Cámara fría A-3',
                'condicion' => 'Óptima',
                'cantidad' => 400,
                'observaciones' => 'Producto terminado listo para mayorista — '.self::MARK,
                'fecha_almacenaje' => $inicio->copy()->addHours(15),
            ]
        );

        return $lotePlanta->fresh();
    }

    /** @return array{0: Insumo, 1: ?InsumoPresentacion} */
    private function seedProductoTerminadoPlanta(
        LoteProduccionPedido $lotePlanta,
        Almacen $almacenPlanta,
        Usuario $plantaUser,
        ?int $kgId
    ): array {
        $tipoProd = InsumoCatalogo::tipoProductoTerminadoId()
            ?? TipoInsumo::query()->value('tipoinsumoid');

        $insumo = Insumo::updateOrCreate(
            [
                'nombre' => self::PRODUCTO,
                'almacenid' => $almacenPlanta->almacenid,
            ],
            [
                'tipoinsumoid' => $tipoProd,
                'unidadmedidaid' => $kgId,
                'stock' => 400,
                'stockminimo' => 40,
                'descripcion' => 'Producto procesado — origen lote agrícola '.self::LOTE_CODIGO
                    .' / lote planta '.self::LOTE_PLANTA_CODIGO.' '.self::MARK,
            ]
        );

        $presentacion = $this->asegurarPresentacionBolsa1Kg($insumo);

        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->first();

        if ($tipoIngreso) {
            AlmacenMovimiento::updateOrCreate(
                [
                    'referencia' => self::LOTE_PLANTA_CODIGO,
                    'insumoid' => $insumo->insumoid,
                ],
                [
                    'almacenid' => $almacenPlanta->almacenid,
                    'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                    'usuarioid' => $plantaUser->usuarioid,
                    'fecha' => Carbon::now()->subDays(24)->toDateString(),
                    'cantidad' => 400,
                    'observaciones' => '[Ingreso producto terminado] Lote '.$lotePlanta->codigo_lote.' '.self::MARK,
                ]
            );

            AlmacenMovimiento::updateOrCreate(
                [
                    'referencia' => self::ENVIO_AGRICOLA,
                    'insumoid' => $insumo->insumoid,
                    'observaciones' => '[Recepción planta] Materia prima '.self::LOTE_CODIGO.' '.self::MARK,
                ],
                [
                    'almacenid' => $almacenPlanta->almacenid,
                    'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                    'usuarioid' => $plantaUser->usuarioid,
                    'fecha' => Carbon::now()->subDays(26)->toDateString(),
                    'cantidad' => self::COSECHA_KG,
                ]
            );
        }

        return [$insumo, $presentacion];
    }

    private function seedTrasladoMayorista(
        Insumo $insumoPlanta,
        ?InsumoPresentacion $presentacion,
        LoteProduccionPedido $lotePlanta,
        array $usuarios,
        array $almacenes
    ): void {
        $transportistaId = $usuarios['transportista']?->usuarioid ?? $usuarios['admin']->usuarioid;

        $ruta = RutaDistribucion::updateOrCreate(
            ['codigo' => self::RUTA_MAYORISTA],
            [
                'nombre' => 'Traslado planta → mayorista (flujo QR)',
                'tipo_ruta' => RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA,
                'almacen_planta_origenid' => $almacenes['planta']->almacenid,
                'almacen_mayorista_destinoid' => $almacenes['mayorista']->almacenid,
                'transportista_usuarioid' => $transportistaId,
                'creado_por_usuarioid' => $usuarios['planta']->usuarioid,
                'estado' => RutaDistribucionCatalogo::ESTADO_COMPLETADA,
                'fecha_salida' => Carbon::now()->subDays(20)->setTime(9, 0),
                'simulacion_inicio_at' => Carbon::now()->subDays(20)->setTime(9, 0),
                'llegada_confirmada_at' => Carbon::now()->subDays(20)->setTime(14, 30),
                'fecha_aprobacion_mayorista' => Carbon::now()->subDays(20)->setTime(15, 0),
                'aprobado_por_usuarioid' => ($usuarios['mayorista'] ?? $usuarios['admin'])->usuarioid,
                'costo_bs' => 350,
            ]
        );

        DetalleTrasladoPlantaMayorista::query()
            ->where('rutadistribucionid', $ruta->rutadistribucionid)
            ->delete();

        DetalleTrasladoPlantaMayorista::create([
            'rutadistribucionid' => $ruta->rutadistribucionid,
            'insumoid' => $insumoPlanta->insumoid,
            'insumo_presentacionid' => $presentacion?->insumo_presentacionid,
            'loteproduccionpedidoid' => $lotePlanta->loteproduccionpedidoid,
            'presentacion_nombre' => $presentacion?->nombre ?? 'Bolsa 1 kg',
            'producto_nombre' => self::PRODUCTO,
            'cantidad' => 200,
            'cantidad_unidades' => 200,
            'observaciones' => 'Traslado de producto terminado — '.self::MARK,
        ]);

        $tipoEgreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'egreso')
            ->where('activo', true)
            ->first();
        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->first();

        if ($tipoEgreso) {
            AlmacenMovimiento::updateOrCreate(
                [
                    'referencia' => self::RUTA_MAYORISTA,
                    'insumoid' => $insumoPlanta->insumoid,
                    'almacenid' => $almacenes['planta']->almacenid,
                ],
                [
                    'tipo_movimiento_almacenid' => $tipoEgreso->tipo_movimiento_almacenid,
                    'usuarioid' => $usuarios['planta']->usuarioid,
                    'fecha' => Carbon::now()->subDays(20)->toDateString(),
                    'cantidad' => 200,
                    'observaciones' => '[Traslado planta → mayorista] '.self::RUTA_MAYORISTA.' '.self::MARK,
                ]
            );
        }

        if ($tipoIngreso) {
            // movimiento en mayorista se crea al seedear stock
        }
    }

    private function seedStockMayorista(Almacen $almacenMayorista, ?int $kgId, Usuario $user): Insumo
    {
        $tipoProd = InsumoCatalogo::tipoProductoTerminadoId()
            ?? TipoInsumo::query()->value('tipoinsumoid');

        $insumo = Insumo::updateOrCreate(
            [
                'nombre' => self::PRODUCTO,
                'almacenid' => $almacenMayorista->almacenid,
            ],
            [
                'tipoinsumoid' => $tipoProd,
                'unidadmedidaid' => $kgId,
                'stock' => 200,
                'stockminimo' => 20,
                'descripcion' => 'Producto procesado y envasado — origen lote '.self::LOTE_CODIGO.'. '.self::MARK,
            ]
        );

        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->first();

        if ($tipoIngreso) {
            AlmacenMovimiento::updateOrCreate(
                [
                    'referencia' => self::RUTA_MAYORISTA,
                    'insumoid' => $insumo->insumoid,
                    'almacenid' => $almacenMayorista->almacenid,
                ],
                [
                    'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                    'usuarioid' => $user->usuarioid,
                    'fecha' => Carbon::now()->subDays(20)->toDateString(),
                    'cantidad' => 200,
                    'observaciones' => '[Recepción mayorista] Traslado '.self::RUTA_MAYORISTA.' '.self::MARK,
                ]
            );
        }

        return $insumo;
    }

    private function resolverPuntoVenta(?Usuario $minorista): PuntoVenta
    {
        $punto = PuntoVenta::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%alvaro%'])
            ->orWhere('nombre', 'Mercado Flujo Demo')
            ->first();

        if ($punto) {
            if ($minorista && ! $punto->usuarioid) {
                $punto->usuarioid = $minorista->usuarioid;
                $punto->save();
            }

            return $punto;
        }

        return PuntoVenta::create([
            'usuarioid' => $minorista?->usuarioid,
            'nombre' => 'Mercado Alvaro',
            'direccion' => 'Av. Grigotá esq. Calle Ñuflo de Chávez, Santa Cruz',
            'latitud' => -17.7892,
            'longitud' => -63.1811,
            'activo' => true,
            'fechacreacion' => now(),
        ]);
    }

    private function seedPedidoDistribucionPdv(
        PuntoVenta $punto,
        array $almacenes,
        Insumo $insumoMayorista,
        ?InsumoPresentacion $presentacion,
        Usuario $admin
    ): void {
        $productoNombre = $presentacion
            ? self::PRODUCTO.' · '.$presentacion->nombre
            : self::PRODUCTO;

        $pedido = PedidoDistribucion::updateOrCreate(
            ['numero_solicitud' => self::PEDIDO_PDV],
            [
                'puntoventaid' => $punto->puntoventaid,
                'almacen_mayorista_origenid' => $almacenes['mayorista']->almacenid,
                'almacen_planta_origenid' => $almacenes['planta']->almacenid,
                'estado' => PedidoDistribucionCatalogo::ESTADO_RECIBIDO,
                'fechapedido' => Carbon::now()->subDays(5),
                'fecha_aceptacion' => Carbon::now()->subDays(4),
                'fecha_envio' => Carbon::now()->subDays(3),
                'fecha_recepcion' => Carbon::now()->subDays(2),
                'creado_por_usuarioid' => $admin->usuarioid,
                'aceptado_por_usuarioid' => $admin->usuarioid,
                'observaciones' => 'Distribución mayorista → minorista — trazabilidad '
                    .self::LOTE_CODIGO.' '.self::MARK,
            ]
        );

        DetallePedidoDistribucion::query()
            ->where('pedidodistribucionid', $pedido->pedidodistribucionid)
            ->delete();

        DetallePedidoDistribucion::create([
            'pedidodistribucionid' => $pedido->pedidodistribucionid,
            'producto_nombre' => $productoNombre,
            'insumoid' => $insumoMayorista->insumoid,
            'insumo_presentacionid' => $presentacion?->insumo_presentacionid,
            'tipo_envase' => $presentacion?->tipo_envase ?? 'bolsa',
            'cantidad' => self::UNIDADES_PDV,
            'observaciones' => 'Producto envasado — lote agrícola '.self::LOTE_NOMBRE,
        ]);
    }

    private function seedMovimientoRecepcionPdv(PuntoVenta $punto, Insumo $insumoPdv, Usuario $admin): void
    {
        if (! Schema::hasTable('almacen_movimiento') || ! $punto->almacenid) {
            return;
        }

        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->first();

        if (! $tipoIngreso) {
            return;
        }

        AlmacenMovimiento::updateOrCreate(
            ['referencia' => self::PEDIDO_PDV, 'insumoid' => $insumoPdv->insumoid],
            [
                'almacenid' => $punto->almacenid,
                'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                'usuarioid' => $admin->usuarioid,
                'fecha' => Carbon::now()->subDays(2)->toDateString(),
                'cantidad' => self::STOCK_PDV_KG,
                'observaciones' => '[Recepción PDV] '.self::PEDIDO_PDV.' · '.self::UNIDADES_PDV
                    .' bolsas ('.number_format(self::STOCK_PDV_KG, 2).' kg) '.self::MARK,
            ]
        );
    }

    private function asegurarPresentacionBolsa1Kg(Insumo $insumo): ?InsumoPresentacion
    {
        if (! Schema::hasTable('insumo_presentacion')) {
            return null;
        }

        $existente = InsumoPresentacion::query()
            ->where('insumoid', $insumo->insumoid)
            ->where('activo', true)
            ->whereRaw('LOWER(nombre) LIKE ?', ['%1 kg%'])
            ->first();

        if ($existente) {
            return $existente;
        }

        $tipoEmpaqueId = Schema::hasTable('tipo_empaque')
            ? TipoEmpaque::query()->whereRaw('LOWER(nombre) LIKE ?', ['%bolsa%'])->value('tipoempaqueid')
            : null;

        return InsumoPresentacion::create([
            'insumoid' => $insumo->insumoid,
            'tipoempaqueid' => $tipoEmpaqueId,
            'nombre' => 'Bolsa 1 kg',
            'tipo_envase' => 'bolsa',
            'peso_neto_kg' => 1.0,
            'orden' => 1,
            'activo' => true,
        ]);
    }

    private function unidadKgId(): ?int
    {
        return UnidadMedida::query()
            ->whereRaw('LOWER(TRIM(COALESCE(abreviatura, nombre))) IN (?, ?, ?)', ['kg', 'kilogramo', 'kilo'])
            ->value('unidadmedidaid')
            ?? UnidadMedida::query()->value('unidadmedidaid');
    }
}
