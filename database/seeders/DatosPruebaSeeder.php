<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCatalogosBase();
        $this->seedDatosPrueba();
    }

    private function seedCatalogosBase(): void
    {
        DB::table('unidadmedida')->updateOrInsert(
            ['nombre' => 'Kilogramo'],
            ['abreviatura' => 'kg', 'categoria' => 'peso']
        );
        DB::table('unidadmedida')->updateOrInsert(
            ['nombre' => 'Hectárea'],
            ['abreviatura' => 'ha', 'categoria' => 'superficie']
        );

        DB::table('tipoinsumo')->updateOrInsert(['nombre' => 'Fertilizante'], []);
        DB::table('tipoinsumo')->updateOrInsert(['nombre' => 'Semilla'], []);
        DB::table('tipoinsumo')->updateOrInsert(['nombre' => 'Bioinsumo'], []);

        DB::table('cultivo')->updateOrInsert(['nombre' => 'Tomate'], []);
        DB::table('cultivo')->updateOrInsert(['nombre' => 'Papa'], []);
        DB::table('cultivo')->updateOrInsert(['nombre' => 'Lechuga'], []);

        DB::table('estadolote_tipo')->updateOrInsert(['nombre' => 'planificado'], ['nombre' => 'Planificado']);
        DB::table('estadolote_tipo')->updateOrInsert(['nombre' => 'sembrado'], ['nombre' => 'Sembrado']);
        DB::table('estadolote_tipo')->updateOrInsert(['nombre' => 'en crecimiento'], ['nombre' => 'En crecimiento']);
        DB::table('estadolote_tipo')->updateOrInsert(['nombre' => 'cosechado'], ['nombre' => 'Cosechado']);

        DB::table('destinoproduccion')->updateOrInsert(['nombre' => 'almacenamiento'], []);
        DB::table('destinoproduccion')->updateOrInsert(['nombre' => 'venta'], []);
    }

    private function seedDatosPrueba(): void
    {
        // Misma contraseña que AdminUserSeeder (123456); updateOrCreate para no dejar admin con otro hash si ya existía.
        $adminUsuario = Usuario::updateOrCreate(
            ['email' => 'admin@agrofusion.com'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'nombreusuario' => 'admin',
                'telefono' => '123456789',
                'passwordhash' => Hash::make('123456'),
                'role' => 'admin',
                'activo' => true,
                'fecharegistro' => now(),
                'fechamodificacion' => now(),
            ]
        );
        $adminUsuario->syncRoles(['admin']);

        $agricultor = Usuario::firstOrCreate(
            ['email' => 'agricultor@agrofusion.com'],
            [
                'nombre' => 'Usuario',
                'apellido' => 'Agricultor',
                'nombreusuario' => 'agricultor',
                'telefono' => '700000001',
                'passwordhash' => Hash::make('password'),
                'activo' => true,
                'fecharegistro' => now(),
                'fechamodificacion' => now(),
            ]
        );

        $kgId = DB::table('unidadmedida')->where('nombre', 'Kilogramo')->value('unidadmedidaid');
        $haId = DB::table('unidadmedida')->where('nombre', 'Hectárea')->value('unidadmedidaid');
        $estadoProdId = DB::table('estadolote_tipo')->whereRaw('LOWER(TRIM(nombre)) = ?', ['en crecimiento'])->value('estadolotetipoid');
        $estadoCosechadoId = DB::table('estadolote_tipo')->where('nombre', 'cosechado')->value('estadolotetipoid');
        $destinoAlmacenId = DB::table('destinoproduccion')->where('nombre', 'almacenamiento')->value('destinoproduccionid');

        $tipoFertId = DB::table('tipoinsumo')->where('nombre', 'Fertilizante')->value('tipoinsumoid');
        $tipoSemillaId = DB::table('tipoinsumo')->where('nombre', 'Semilla')->value('tipoinsumoid');
        $tipoBioId = DB::table('tipoinsumo')->where('nombre', 'Bioinsumo')->value('tipoinsumoid');

        $cultivoTomateId = DB::table('cultivo')->where('nombre', 'Tomate')->value('cultivoid');
        $cultivoPapaId = DB::table('cultivo')->where('nombre', 'Papa')->value('cultivoid');
        $cultivoLechugaId = DB::table('cultivo')->where('nombre', 'Lechuga')->value('cultivoid');

        // 1) Actores de abastecimiento (3)
        DB::table('actor_abastecimiento')->updateOrInsert(
            ['nombre' => 'Cooperativa Verde Sur'],
            ['tipo_actor' => 'productor', 'email' => 'contacto@verdesur.test', 'telefono' => '700100001', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('actor_abastecimiento')->updateOrInsert(
            ['nombre' => 'Proveedor AgroNorte'],
            ['tipo_actor' => 'proveedor', 'email' => 'ventas@agronorte.test', 'telefono' => '700100002', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('actor_abastecimiento')->updateOrInsert(
            ['nombre' => 'Alianza Campo Vivo'],
            ['tipo_actor' => 'mixto', 'email' => 'info@campovivo.test', 'telefono' => '700100003', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]
        );

        $actor1 = DB::table('actor_abastecimiento')->where('nombre', 'Cooperativa Verde Sur')->value('actorid');
        $actor2 = DB::table('actor_abastecimiento')->where('nombre', 'Proveedor AgroNorte')->value('actorid');
        $actor3 = DB::table('actor_abastecimiento')->where('nombre', 'Alianza Campo Vivo')->value('actorid');

        // 2) Insumos (3)
        DB::table('insumo')->updateOrInsert(
            ['nombre' => 'Fertilizante NPK 15-15-15'],
            ['tipoinsumoid' => $tipoFertId, 'unidadmedidaid' => $kgId, 'stock' => 180, 'stockminimo' => 30, 'proveedor' => 'Proveedor AgroNorte', 'actorid' => $actor2, 'preciounitario' => 38.5, 'descripcion' => 'Insumo granular para etapa vegetativa.']
        );
        DB::table('insumo')->updateOrInsert(
            ['nombre' => 'Semilla Certificada Tomate'],
            ['tipoinsumoid' => $tipoSemillaId, 'unidadmedidaid' => $kgId, 'stock' => 60, 'stockminimo' => 15, 'proveedor' => 'Cooperativa Verde Sur', 'actorid' => $actor1, 'preciounitario' => 52.9, 'descripcion' => 'Semilla para producción controlada.']
        );
        DB::table('insumo')->updateOrInsert(
            ['nombre' => 'Bioestimulante Foliar'],
            ['tipoinsumoid' => $tipoBioId, 'unidadmedidaid' => $kgId, 'stock' => 95, 'stockminimo' => 20, 'proveedor' => 'Alianza Campo Vivo', 'actorid' => $actor3, 'preciounitario' => 44.0, 'descripcion' => 'Aplicación foliar para fortalecimiento de cultivo.']
        );

        // 3) Lotes (3)
        DB::table('lote')->updateOrInsert(
            ['nombre' => 'Lote Norte A1'],
            ['usuarioid' => $agricultor->usuarioid, 'ubicacion' => 'Zona Norte - Parcela A1', 'superficie' => 12.5, 'unidadsuperficieid' => $haId, 'cultivoid' => $cultivoTomateId, 'actorid' => $actor1, 'codigo_trazabilidad' => 'LT-2026-0001', 'fechasiembra' => now()->subDays(90)->toDateString(), 'estadolotetipoid' => $estadoCosechadoId, 'latitud' => -17.7401, 'longitud' => -63.1302, 'fechamodificacion' => now()]
        );
        DB::table('lote')->updateOrInsert(
            ['nombre' => 'Lote Este B2'],
            ['usuarioid' => $agricultor->usuarioid, 'ubicacion' => 'Zona Este - Parcela B2', 'superficie' => 9.0, 'unidadsuperficieid' => $haId, 'cultivoid' => $cultivoPapaId, 'actorid' => $actor2, 'codigo_trazabilidad' => 'LT-2026-0002', 'fechasiembra' => now()->subDays(70)->toDateString(), 'estadolotetipoid' => $estadoProdId, 'latitud' => -17.7551, 'longitud' => -63.1155, 'fechamodificacion' => now()]
        );
        DB::table('lote')->updateOrInsert(
            ['nombre' => 'Lote Sur C3'],
            ['usuarioid' => $agricultor->usuarioid, 'ubicacion' => 'Zona Sur - Parcela C3', 'superficie' => 7.4, 'unidadsuperficieid' => $haId, 'cultivoid' => $cultivoLechugaId, 'actorid' => $actor3, 'codigo_trazabilidad' => 'LT-2026-0003', 'fechasiembra' => now()->subDays(45)->toDateString(), 'estadolotetipoid' => $estadoCosechadoId, 'latitud' => -17.7692, 'longitud' => -63.1440, 'fechamodificacion' => now()]
        );

        $lote1 = DB::table('lote')->where('nombre', 'Lote Norte A1')->value('loteid');
        $lote2 = DB::table('lote')->where('nombre', 'Lote Este B2')->value('loteid');
        $lote3 = DB::table('lote')->where('nombre', 'Lote Sur C3')->value('loteid');

        // 4) Producciones (3)
        DB::table('produccion')->updateOrInsert(
            ['loteid' => $lote1, 'fechacosecha' => now()->subDays(10)->toDateString()],
            ['cantidad' => 2450, 'unidadmedidaid' => $kgId, 'cantidad_base' => 2450, 'destinoproduccionid' => $destinoAlmacenId, 'observaciones' => 'Cosecha principal de tomate para almacenamiento.']
        );
        DB::table('produccion')->updateOrInsert(
            ['loteid' => $lote2, 'fechacosecha' => now()->subDays(7)->toDateString()],
            ['cantidad' => 1980, 'unidadmedidaid' => $kgId, 'cantidad_base' => 1980, 'destinoproduccionid' => $destinoAlmacenId, 'observaciones' => 'Producción parcial de papa.']
        );
        DB::table('produccion')->updateOrInsert(
            ['loteid' => $lote3, 'fechacosecha' => now()->subDays(5)->toDateString()],
            ['cantidad' => 1325, 'unidadmedidaid' => $kgId, 'cantidad_base' => 1325, 'destinoproduccionid' => $destinoAlmacenId, 'observaciones' => 'Cosecha de lechuga para pedidos locales.']
        );

        // 5) Certificación (1)
        DB::table('certificacion_lote')->updateOrInsert(
            ['codigo_certificado' => 'CERT-2026-0001'],
            ['loteid' => $lote1, 'usuarioid' => $adminUsuario->usuarioid, 'observaciones' => 'Certificación de lote con control de trazabilidad completo.', 'fecha_certificacion' => now()]
        );

        // 6) Envío (1) en cola local de envíos pendientes
        $yaExisteEnvio = DB::table('envios_pendientes')
            ->where('datos_envio', 'like', '%ENV-2026-0001%')
            ->exists();

        if (! $yaExisteEnvio) {
            DB::table('envios_pendientes')->insert([
                'datos_envio' => json_encode([
                    'codigo_envio' => 'ENV-2026-0001',
                    'origen' => 'Planta Central',
                    'destino' => 'Centro de Distribución Norte',
                    'producto' => 'Tomate',
                    'cantidad' => 500,
                    'unidad' => 'kg',
                ]),
                'estado' => 'pendiente',
                'intentos' => 0,
                'usuarioid' => $adminUsuario->usuarioid,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }
}

