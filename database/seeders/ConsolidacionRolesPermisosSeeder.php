<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Permisos legacy de módulos internos (compatibilidad con código antiguo).
 * Los roles canónicos se sincronizan en RolePermissionSeeder desde permission_matrix.
 */
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

        $admin->givePermissionTo($permisos);
        $agricultor->givePermissionTo([
            'dashboard.ver',
            'lotes.gestionar',
            'produccion.gestionar',
            'pedidos.gestionar',
            'certificaciones.gestionar',
            'inventario.gestionar',
        ]);
    }
}
