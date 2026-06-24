<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\DatosPlanta;
use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\IncidenteEnvio;
use App\Models\Lote;
use App\Models\MaquinaPlanta;
use App\Models\OperadorPlanta;
use App\Models\Pedido;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Models\Produccion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\RegistroProcesoMaquinaPlanta;
use App\Models\RutaMultiEntrega;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Models\VariableEstandar;
use App\Models\VariableProcesoMaquinaPlanta;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de demostración — módulo Planta (panel logístico, procesos/máquinas, línea de proceso, almacenaje).
 * Ejecutar: php artisan db:seed --class=PlantaModuloSeeder
 */
class PlantaModuloSeeder extends Seeder
{
    private const MARK = '[MOD-PLANTA]';

    private const EMAIL_PLANTA = 'planta@agrofusion.com';

    public function run(): void
    {
        $this->call(ProduccionModuloSeeder::class);

        if (Almacen::count() < 1) {
            $this->call(DemoUsuariosAlmacenesActoresSeeder::class);
        }

        if (EnvioAsignacionMultiple::count() < 3) {
            $this->call(DemoEnviosAsignacionesRutasSeeder::class);
        }

        if (Pedido::count() < 3) {
            $this->call(DemoPedidosVentasCertificacionesSeeder::class);
        }

        DB::transaction(function () {
            $this->seedDatosPlanta();
            $this->seedOperadorPlanta();
            $pasos = $this->seedLineaProcesoMaquina();
            $this->seedRegistrosProceso($pasos);
            $this->seedProduccionAlmacenamientoPlanta();
            $this->seedLogisticaPlantaExtra();
        });

        $abiertos = IncidenteEnvio::where('estado', 'abierto')->count();
        $rutasActivas = RutaMultiEntrega::whereIn('estado', ['planificada', 'en_ruta'])->count();

        $this->command?->info(sprintf(
            '%s Listo: %d procesos-máquina, %d variables estándar, %d registros línea, %d prod. almacenadas, panel → ped:%d asig:%d rutas_act:%d inc_abiertos:%d docs:%d',
            self::MARK,
            ProcesoMaquinaPlanta::count(),
            VariableEstandar::where('codigo', 'like', 'VAR-%')->orWhere('descripcion', 'like', '%'.self::MARK.'%')->count(),
            RegistroProcesoMaquinaPlanta::where('observaciones', 'like', self::MARK.'%')->count(),
            ProduccionAlmacenamiento::where('observaciones', 'like', '%'.self::MARK.'%')->count(),
            Pedido::count(),
            EnvioAsignacionMultiple::count(),
            $rutasActivas,
            $abiertos,
            DocumentoEntrega::count()
        ));
    }

    private function seedDatosPlanta(): void
    {
        if (! Schema::hasTable('datos_planta')) {
            return;
        }

        DatosPlanta::updateOrCreate(
            ['nombre' => 'Planta Procesadora Santa Cruz'],
            [
                'direccion' => 'Parque Industrial El Trompillo, Km 12',
                'ciudad' => 'Santa Cruz de la Sierra',
                'departamento' => 'Santa Cruz',
                'pais' => 'Bolivia',
                'latitud' => -17.7833,
                'longitud' => -63.1821,
                'telefono' => '+591 3 3456789',
                'email' => 'planta@agrofusion.com',
            ]
        );
    }

    private function seedOperadorPlanta(): void
    {
        if (! Schema::hasTable('operador_planta')) {
            return;
        }

        $planta = Usuario::where('email', self::EMAIL_PLANTA)->first();
        if (! $planta) {
            return;
        }

        OperadorPlanta::updateOrCreate(
            ['email' => self::EMAIL_PLANTA],
            [
                'nombre' => $planta->nombre,
                'apellido' => $planta->apellido,
                'usuario' => $planta->nombreusuario,
                'password_hash' => Hash::make('password'),
                'usuarioid' => $planta->usuarioid,
                'activo' => true,
            ]
        );
    }

    /**
     * @return array<string, ProcesoMaquinaPlanta>
     */
    private function seedLineaProcesoMaquina(): array
    {
        $out = [];
        if (! Schema::hasTable('proceso_maquina_planta')) {
            return $out;
        }

        $procesoLavado = ProcesoPlanta::where('nombre', 'Lavado y selección')->first();
        $procesoClasif = ProcesoPlanta::where('nombre', 'Clasificación por calidad')->first();
        $procesoEmpaque = ProcesoPlanta::where('nombre', 'Empaque')->first();
        $procesoCalidad = ProcesoPlanta::where('nombre', 'Control de calidad')->first();

        $maqLavadora = MaquinaPlanta::where('codigo', 'L-100')->first();
        $maqBanda = MaquinaPlanta::where('codigo', 'BC-20')->first();
        $maqSelladora = MaquinaPlanta::where('codigo', 'SE-10')->first();
        $maqBalanza = MaquinaPlanta::where('codigo', 'BD-500')->first();

        $pasos = [
            ['key' => 'lavado', 'proc' => $procesoLavado, 'maq' => $maqLavadora, 'orden' => 1, 'nombre' => 'Lavado inicial', 'min' => 25],
            ['key' => 'clasif', 'proc' => $procesoClasif, 'maq' => $maqBanda, 'orden' => 2, 'nombre' => 'Clasificación visual', 'min' => 40],
            ['key' => 'empaque', 'proc' => $procesoEmpaque, 'maq' => $maqSelladora, 'orden' => 3, 'nombre' => 'Empaque primario', 'min' => 30],
            ['key' => 'calidad', 'proc' => $procesoCalidad, 'maq' => $maqBalanza, 'orden' => 4, 'nombre' => 'Control de peso', 'min' => 15],
        ];

        foreach ($pasos as $p) {
            if (! $p['proc'] || ! $p['maq']) {
                continue;
            }

            $pm = ProcesoMaquinaPlanta::updateOrCreate(
                [
                    'procesoplantaid' => $p['proc']->procesoplantaid,
                    'maquinaplantaid' => $p['maq']->maquinaplantaid,
                    'orden_paso' => $p['orden'],
                ],
                [
                    'nombre' => $p['nombre'],
                    'descripcion' => self::MARK.' Paso '.$p['orden'].' de línea de planta.',
                    'tiempo_estimado' => $p['min'],
                ]
            );
            $out[$p['key']] = $pm;
        }

        if (Schema::hasTable('variable_estandar') && Schema::hasTable('variable_proceso_maquina_planta')) {
            $vars = [
                ['codigo' => 'VAR-TEMP', 'nombre' => 'Temperatura', 'unidad' => '°C', 'desc' => 'Temperatura del producto o del equipo durante la operación.', 'min' => 8, 'max' => 18, 'obj' => 12],
                ['codigo' => 'VAR-PESO', 'nombre' => 'Peso', 'unidad' => 'kg', 'desc' => 'Peso de muestra, lote o porción verificada en báscula.', 'min' => 0.2, 'max' => 50, 'obj' => 1],
                ['codigo' => 'VAR-HUM', 'nombre' => 'Humedad relativa', 'unidad' => '%', 'desc' => 'Humedad del ambiente o del producto en la etapa.', 'min' => 40, 'max' => 95, 'obj' => 70],
            ];

            foreach ($vars as $v) {
                $est = VariableEstandar::updateOrCreate(
                    ['codigo' => $v['codigo']],
                    [
                        'nombre' => $v['nombre'],
                        'unidad' => $v['unidad'],
                        'descripcion' => $v['desc'],
                        'activo' => true,
                    ]
                );

                foreach ($out as $pm) {
                    VariableProcesoMaquinaPlanta::updateOrCreate(
                        [
                            'procesomaquinaplantaid' => $pm->procesomaquinaplantaid,
                            'variableestandarid' => $est->variableestandarid,
                        ],
                        [
                            'valor_minimo' => $v['min'],
                            'valor_maximo' => $v['max'],
                            'valor_objetivo' => $v['obj'],
                            'obligatorio' => true,
                        ]
                    );
                }
            }
        }

        return $out;
    }

    /**
     * @param  array<string, ProcesoMaquinaPlanta>  $pasos
     */
    private function seedRegistrosProceso(array $pasos): void
    {
        if (! Schema::hasTable('registro_proceso_maquina_planta') || $pasos === []) {
            return;
        }

        $operador = Usuario::where('email', self::EMAIL_PLANTA)->first()
            ?? Usuario::where('email', 'admin@agrofusion.com')->first();

        if (! $operador) {
            return;
        }

        $registros = [
            ['paso' => 'lavado', 'lote' => 'Lote Norte A1', 'dias' => 3, 'cumple' => true, 'vars' => ['VAR-TEMP' => 12.5, 'VAR-PESO' => 2.1]],
            ['paso' => 'clasif', 'lote' => 'Lote Norte A1', 'dias' => 2, 'cumple' => true, 'vars' => ['VAR-PESO' => 2.0, 'VAR-HUM' => 68]],
            ['paso' => 'lavado', 'lote' => 'Lote Este B2', 'dias' => 5, 'cumple' => true, 'vars' => ['VAR-TEMP' => 11.0, 'VAR-PESO' => 1.8]],
            ['paso' => 'empaque', 'lote' => 'Lote Sur C3', 'dias' => 1, 'cumple' => false, 'vars' => ['VAR-PESO' => 0.35, 'VAR-HUM' => 82]],
            ['paso' => 'calidad', 'lote' => 'Lote Central D4', 'dias' => 0, 'cumple' => true, 'vars' => ['VAR-PESO' => 1.2, 'VAR-HUM' => 55]],
        ];

        foreach ($registros as $i => $r) {
            $pm = $pasos[$r['paso']] ?? null;
            $lote = Lote::where('nombre', $r['lote'])->first();
            if (! $pm || ! $lote) {
                continue;
            }

            $inicio = now()->subDays($r['dias'])->setTime(8, 0, 0);
            $fin = $inicio->copy()->addMinutes(45);

            RegistroProcesoMaquinaPlanta::updateOrCreate(
                ['observaciones' => self::MARK.' registro|'.$i],
                [
                    'procesomaquinaplantaid' => $pm->procesomaquinaplantaid,
                    'loteid' => $lote->loteid,
                    'usuarioid' => $operador->usuarioid,
                    'variables_ingresadas' => json_encode($r['vars'], JSON_UNESCAPED_UNICODE),
                    'cumple_estandar' => $r['cumple'],
                    'hora_inicio' => $inicio,
                    'hora_fin' => $fin,
                    'fecha_registro' => $inicio,
                ]
            );
        }
    }

    private function seedProduccionAlmacenamientoPlanta(): void
    {
        if (! Schema::hasTable('produccionalmacenamiento')) {
            return;
        }

        $kgId = UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['kilogramo'])->value('unidadmedidaid');
        $undId = UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['unidad'])->value('unidadmedidaid');
        if (! $kgId) {
            return;
        }

        $almCentral = Almacen::where('nombre', 'Almacén Central Santa Cruz')->first();
        $almNorte = Almacen::where('nombre', 'Almacén Norte')->first();
        $almPlantaProc = Almacen::where('nombre', 'Almacén Planta Procesadora')->first();

        $items = [
            ['cultivo' => 'Tomate', 'alm' => $almCentral, 'cant' => 1500, 'um' => $kgId, 'dias' => 2],
            ['cultivo' => 'Papa', 'alm' => $almCentral, 'cant' => 2300, 'um' => $kgId, 'dias' => 3],
            ['cultivo' => 'Lechuga', 'alm' => $almNorte, 'cant' => 800, 'um' => $undId ?? $kgId, 'dias' => 1],
            ['cultivo' => 'Cebolla', 'alm' => $almPlantaProc, 'cant' => 1200, 'um' => $kgId, 'dias' => 0],
        ];

        foreach ($items as $d) {
            if (! $d['alm']) {
                continue;
            }

            $prod = Produccion::query()
                ->join('lote', 'produccion.loteid', '=', 'lote.loteid')
                ->join('cultivo', 'lote.cultivoid', '=', 'cultivo.cultivoid')
                ->whereRaw('LOWER(TRIM(cultivo.nombre)) = ?', [mb_strtolower($d['cultivo'])])
                ->orderByDesc('produccion.produccionid')
                ->select('produccion.*')
                ->first();

            if (! $prod) {
                continue;
            }

            $obs = self::MARK.' Almacenaje '.$d['cultivo'].' · Zona disponible';

            ProduccionAlmacenamiento::updateOrCreate(
                [
                    'produccionid' => $prod->produccionid,
                    'almacenid' => $d['alm']->almacenid,
                    'observaciones' => $obs,
                ],
                [
                    'cantidad' => $d['cant'],
                    'unidadmedidaid' => $d['um'],
                    'fechaentrada' => Carbon::now()->subDays($d['dias']),
                    'fechasalida' => null,
                ]
            );
        }
    }

    private function seedLogisticaPlantaExtra(): void
    {
        $planta = Usuario::where('email', self::EMAIL_PLANTA)->first();
        $admin = Usuario::where('email', 'admin@agrofusion.com')->first();
        $almPlanta = Almacen::where('nombre', 'Almacén Planta Procesadora')->first();

        if (Schema::hasTable('incidente_envio') && $planta) {
            IncidenteEnvio::firstOrCreate(
                ['descripcion' => self::MARK.' Demora en carga por revisión de calidad en planta.'],
                [
                    'externo_envio_id' => 'ENV-PLANTA-001',
                    'reportadopor_usuarioid' => $planta->usuarioid,
                    'tipo' => 'Operación planta',
                    'estado' => 'abierto',
                    'almacenid' => $almPlanta?->almacenid,
                ]
            );
        }

        if (Schema::hasTable('documento_entrega') && $admin) {
            DocumentoEntrega::updateOrCreate(
                ['titulo' => self::MARK.' Acta de salida planta ENV-PLANTA-001'],
                [
                    'externo_envio_id' => 'ENV-PLANTA-001',
                    'usuarioid' => $admin->usuarioid,
                    'tipo_documento' => 'acta_salida',
                    'archivo_path' => 'demo/planta/sin-archivo-env-planta-001.pdf',
                    'metadata' => ['mod_planta' => true],
                    'almacenid' => $almPlanta?->almacenid,
                ]
            );
        }

        if (Schema::hasTable('pedido')) {
            Pedido::updateOrCreate(
                ['numero_solicitud' => 'ENV-PLANTA-001'],
                [
                    'nombre_planta' => 'Cliente Exportación Sur',
                    'latitud' => -17.75,
                    'longitud' => -63.18,
                    'direccion_texto' => 'Zona Franca El Alto, Santa Cruz',
                    'estado' => 'en produccion',
                    'fechapedido' => now()->subDay(),
                    'observaciones' => self::MARK.' Pedido generado desde planta procesadora.',
                ]
            );
        }
    }
}
