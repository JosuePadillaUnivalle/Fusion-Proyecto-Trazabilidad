<?php

namespace Database\Seeders;

use App\Models\Cultivo;
use App\Models\EstadoLoteInsumo;
use App\Models\EstadoLoteTipo;
use App\Models\Prioridad;
use App\Models\TipoActividad;
use App\Models\TipoAlmacen;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Datos de demostración — Administración (catálogos maestros y gestión de usuarios).
 * Ejecutar: php artisan db:seed --class=AdministracionModuloSeeder --force
 */
class AdministracionModuloSeeder extends Seeder
{
    private const MARK = '[MOD-ADMIN]';

    public function run(): void
    {
        if (Usuario::where('email', 'admin@agronexus.com')->doesntExist()) {
            $this->call(DatosPruebaSeeder::class);
        }

        $this->call(RolePermissionSeeder::class);
        $this->call(DemoCatalogosBaseSeeder::class);
        $this->call(DemoUsuariosAlmacenesActoresSeeder::class);

        $this->seedCatalogosAdministracion();
        $this->seedUsuariosGestion();

        $counts = [
            'cultivos' => Cultivo::count(),
            'tipos_actividad' => TipoActividad::count(),
            'usuarios' => Usuario::count(),
            'roles' => Role::count(),
        ];

        $this->command?->info(sprintf(
            '%s Listo: %d cultivos, %d tipos actividad, %d usuarios, %d roles Spatie.',
            self::MARK,
            $counts['cultivos'],
            $counts['tipos_actividad'],
            $counts['usuarios'],
            $counts['roles']
        ));
        $this->command?->info('  Rutas: /catalogos · /gestion-usuarios');
        $this->command?->info('  Admin: admin@agronexus.com / 123456');
        $this->command?->info('  Usuarios demo extra: supervisor@agronexus.com · auditor@agronexus.com (password)');
    }

    private function seedCatalogosAdministracion(): void
    {
        if (Schema::hasTable('cultivo')) {
            Cultivo::updateOrCreate(['nombre' => 'Zanahoria'], ['nombre' => 'Zanahoria']);
            Cultivo::updateOrCreate(['nombre' => 'Pimentón'], ['nombre' => 'Pimentón']);
        }

        if (Schema::hasTable('tipoactividad')) {
            $hasDesc = Schema::hasColumn('tipoactividad', 'descripcion');
            foreach (['Monitoreo IoT', 'Auditoría de calidad', 'Capacitación de personal'] as $nombre) {
                $data = ['nombre' => $nombre];
                if ($hasDesc) {
                    $data['descripcion'] = self::MARK.' '.$nombre;
                }
                TipoActividad::updateOrCreate(['nombre' => $nombre], $data);
            }
        }

        if (Schema::hasTable('tipoinsumo')) {
            foreach (['Empaque', 'EPP', 'Insumo químico controlado'] as $nombre) {
                TipoInsumo::updateOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
            }
        }

        if (Schema::hasTable('tipoalmacen')) {
            $hasDesc = Schema::hasColumn('tipoalmacen', 'descripcion');
            $tipos = [
                ['nombre' => 'Tránsito', 'descripcion' => 'Mercadería en movimiento entre nodos.'],
                ['nombre' => 'Cuarentena', 'descripcion' => 'Producto retenido por control de calidad.'],
            ];
            foreach ($tipos as $t) {
                $data = ['nombre' => $t['nombre']];
                if ($hasDesc) {
                    $data['descripcion'] = self::MARK.' '.$t['descripcion'];
                }
                TipoAlmacen::updateOrCreate(['nombre' => $t['nombre']], $data);
            }
        }

        if (Schema::hasTable('unidadmedida')) {
            $extra = [
                ['nombre' => 'Tonelada', 'abreviatura' => 't', 'categoria' => 'peso'],
                ['nombre' => 'Metro cúbico', 'abreviatura' => 'm³', 'categoria' => 'volumen'],
            ];
            foreach ($extra as $u) {
                $data = ['nombre' => $u['nombre']];
                if (Schema::hasColumn('unidadmedida', 'abreviatura')) {
                    $data['abreviatura'] = $u['abreviatura'];
                }
                if (Schema::hasColumn('unidadmedida', 'categoria')) {
                    $data['categoria'] = $u['categoria'];
                }
                UnidadMedida::updateOrCreate(['nombre' => $u['nombre']], $data);
            }
        }

        if (Schema::hasTable('estadolote_tipo')) {
            $hasDesc = Schema::hasColumn('estadolote_tipo', 'descripcion');
            $estados = ['En certificación', 'Suspendido', 'Archivado'];
            foreach ($estados as $nombre) {
                $data = ['nombre' => $nombre];
                if ($hasDesc) {
                    $data['descripcion'] = self::MARK.' Estado administrativo demo.';
                }
                EstadoLoteTipo::updateOrCreate(['nombre' => $nombre], $data);
            }
        }

        if (Schema::hasTable('estadoloteinsumo')) {
            foreach (['En tránsito', 'Rechazado', 'Vencido'] as $nombre) {
                EstadoLoteInsumo::updateOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
            }
        }

        if (Schema::hasTable('prioridad')) {
            Prioridad::updateOrCreate(['nombre' => 'Crítica'], ['nombre' => 'Crítica']);
        }
    }

    private function seedUsuariosGestion(): void
    {
        if (! Schema::hasTable('usuario')) {
            return;
        }

        $extras = [
            [
                'nombre' => 'María',
                'apellido' => 'Supervisora',
                'nombreusuario' => 'supervisor',
                'email' => 'supervisor@agronexus.com',
                'role' => 'operador',
                'telefono' => '700000201',
                'activo' => true,
                'info' => self::MARK.' Supervisor de campo — gestión usuarios demo.',
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'Auditor',
                'nombreusuario' => 'auditor',
                'email' => 'auditor@agronexus.com',
                'role' => 'operador',
                'telefono' => '700000202',
                'activo' => true,
                'info' => self::MARK.' Auditor interno de procesos.',
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'Inactiva',
                'nombreusuario' => 'ana_inactiva',
                'email' => 'inactiva@agronexus.com',
                'role' => 'agricultor',
                'telefono' => '700000203',
                'activo' => false,
                'info' => self::MARK.' Usuario desactivado para pruebas de listado.',
            ],
        ];

        foreach ($extras as $item) {
            $role = Role::firstOrCreate(['name' => $item['role'], 'guard_name' => 'web']);

            $usuario = Usuario::updateOrCreate(
                ['email' => $item['email']],
                [
                    'nombre' => $item['nombre'],
                    'apellido' => $item['apellido'],
                    'nombreusuario' => $item['nombreusuario'],
                    'telefono' => $item['telefono'],
                    'passwordhash' => Hash::make('password'),
                    'role' => $item['role'],
                    'informacionadicional' => $item['info'],
                    'fecharegistro' => now(),
                    'fechamodificacion' => now(),
                    'activo' => $item['activo'],
                ]
            );

            $usuario->syncRoles([$role->name]);
        }
    }
}
