<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles estandar para Fusion + OrgTrack (idempotente)
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $operadorRole = Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'planta', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'transportista', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'almacen', 'guard_name' => 'web']);

        // Compatibilidad con roles historicos ya usados en el proyecto
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'agricultor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Agricultor', 'guard_name' => 'web']);

        // Asignacion estandar de roles a usuarios existentes sin duplicidad
        $adminUser = Usuario::where('email', 'admin@agronexus.com')->first();
        if ($adminUser) {
            $adminUser->syncRoles([$adminRole->name]);
            $adminUser->role = $adminRole->name;
            $adminUser->fechamodificacion = now();
            $adminUser->save();
        }

        $operadorUser = Usuario::where('email', 'operador@agronexus.com')->first();
        if ($operadorUser) {
            $operadorUser->syncRoles([$operadorRole->name]);
            $operadorUser->role = $operadorRole->name;
            $operadorUser->fechamodificacion = now();
            $operadorUser->save();
        }
    }
}
