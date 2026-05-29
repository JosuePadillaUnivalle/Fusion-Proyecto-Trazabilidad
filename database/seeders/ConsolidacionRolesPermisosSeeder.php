<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ConsolidacionRolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'dashboard.ver',
            'lotes.gestionar',
            'produccion.gestionar',
            'pedidos.gestionar',
            'certificaciones.gestionar',
            'inventario.gestionar',
            'usuarios.gestionar',
            'roles.gestionar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $agricultor = Role::firstOrCreate(['name' => 'agricultor', 'guard_name' => 'web']);
        $operador = Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);

        $admin->syncPermissions($permisos);
        $agricultor->syncPermissions([
            'dashboard.ver',
            'lotes.gestionar',
            'produccion.gestionar',
        ]);
        $operador->syncPermissions([
            'dashboard.ver',
            'lotes.gestionar',
            'produccion.gestionar',
            'pedidos.gestionar',
            'certificaciones.gestionar',
        ]);
    }
}

