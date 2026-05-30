<?php

namespace Database\Seeders;

use App\Models\ActorAbastecimiento;
use App\Models\Almacen;
use App\Models\TipoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class DemoUsuariosAlmacenesActoresSeeder extends Seeder
{
    public function run(): void
    {
        $almacenCentral = $this->seedAlmacenes();
        $this->seedUsuarios($almacenCentral?->almacenid);
        $this->seedActoresAbastecimiento();
    }

    private function seedAlmacenes(): ?Almacen
    {
        if (!Schema::hasTable('almacen') || !Schema::hasTable('tipoalmacen')) {
            return null;
        }

        $unidadBase = UnidadMedida::where('nombre', 'Kilogramo')->first()
            ?? UnidadMedida::query()->orderBy('unidadmedidaid')->first();

        if (! $unidadBase) {
            return null;
        }

        $tipos = [
            'Central' => TipoAlmacen::updateOrCreate(['nombre' => 'Central'], ['descripcion' => 'Almacén principal']),
            'Secundario' => TipoAlmacen::updateOrCreate(['nombre' => 'Secundario'], ['descripcion' => 'Almacén secundario']),
            'Planta' => TipoAlmacen::updateOrCreate(['nombre' => 'Planta'], ['descripcion' => 'Almacén de planta']),
        ];

        $items = [
            [
                'nombre' => 'Almacén Central Santa Cruz',
                'ubicacion' => 'Av. Cristo Redentor, Santa Cruz',
                'tipo' => 'Central',
            ],
            [
                'nombre' => 'Almacén Norte',
                'ubicacion' => 'Zona Norte, Santa Cruz',
                'tipo' => 'Secundario',
            ],
            [
                'nombre' => 'Almacén Planta Procesadora',
                'ubicacion' => 'Parque Industrial, Santa Cruz',
                'tipo' => 'Planta',
            ],
        ];

        $central = null;
        foreach ($items as $item) {
            $almacen = Almacen::updateOrCreate(
                ['nombre' => $item['nombre']],
                [
                    'descripcion' => $item['nombre'],
                    'ubicacion' => $item['ubicacion'],
                    'capacidad' => 50000,
                    'unidadmedidaid' => $unidadBase->unidadmedidaid,
                    'tipoalmacenid' => $tipos[$item['tipo']]->tipoalmacenid,
                    'activo' => true,
                ]
            );

            if ($item['nombre'] === 'Almacén Central Santa Cruz') {
                $central = $almacen;
            }
        }

        return $central;
    }

    private function seedUsuarios(?int $almacenCentralId): void
    {
        if (!Schema::hasTable('usuario')) {
            return;
        }

        $usuarios = [
            ['nombre' => 'Administrador', 'apellido' => 'Sistema', 'nombreusuario' => 'admin', 'email' => 'admin@agrofusion.com', 'role' => 'admin', 'telefono' => '700000100'],
            ['nombre' => 'Agricultor', 'apellido' => 'Demo', 'nombreusuario' => 'agricultor', 'email' => 'agricultor@agrofusion.com', 'role' => 'agricultor', 'telefono' => '700000101'],
            ['nombre' => 'Operador', 'apellido' => 'Logístico', 'nombreusuario' => 'operador', 'email' => 'operador@agrofusion.com', 'role' => 'operador', 'telefono' => '700000102'],
            ['nombre' => 'Planta', 'apellido' => 'Principal', 'nombreusuario' => 'planta', 'email' => 'planta@agrofusion.com', 'role' => 'planta', 'telefono' => '700000103'],
            ['nombre' => 'Carlos', 'apellido' => 'Mamani', 'nombreusuario' => 'transportista', 'email' => 'transportista@agrofusion.com', 'role' => 'transportista', 'telefono' => '700000104'],
            ['nombre' => 'Jorge', 'apellido' => 'Almacenero', 'nombreusuario' => 'almacen', 'email' => 'almacen@agrofusion.com', 'role' => 'almacen', 'telefono' => '700000105'],
        ];

        foreach ($usuarios as $item) {
            $role = Role::firstOrCreate(['name' => $item['role'], 'guard_name' => 'web']);

            $plainPassword = $item['role'] === 'admin' ? '123456' : 'password';

            $payload = [
                'nombre' => $item['nombre'],
                'apellido' => $item['apellido'],
                'nombreusuario' => $item['nombreusuario'],
                'telefono' => $item['telefono'],
                'passwordhash' => Hash::make($plainPassword),
                'role' => $item['role'],
                'activo' => true,
                'fecharegistro' => now(),
                'fechamodificacion' => now(),
            ];

            if ($item['role'] === 'almacen' && $almacenCentralId) {
                $payload['almacenid'] = $almacenCentralId;
            }

            $usuario = Usuario::updateOrCreate(
                ['email' => $item['email']],
                $payload
            );

            $usuario->syncRoles([$role->name]);
        }
    }

    private function seedActoresAbastecimiento(): void
    {
        if (!Schema::hasTable('actor_abastecimiento')) {
            return;
        }

        $items = [
            ['nombre' => 'Productor Valle Verde', 'tipo_actor' => 'productor', 'telefono' => '+59170000001'],
            ['nombre' => 'Proveedor AgroInsumos SRL', 'tipo_actor' => 'proveedor', 'telefono' => '+59170000002'],
            ['nombre' => 'Cooperativa Campo Fértil', 'tipo_actor' => 'mixto', 'telefono' => '+59170000003'],
        ];

        foreach ($items as $item) {
            ActorAbastecimiento::updateOrCreate(
                ['nombre' => $item['nombre']],
                [
                    'tipo_actor' => $item['tipo_actor'],
                    'telefono' => $item['telefono'],
                    'activo' => true,
                ]
            );
        }
    }
}
