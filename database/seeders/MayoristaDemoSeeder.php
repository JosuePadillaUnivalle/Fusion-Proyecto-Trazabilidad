<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Usuario;
use App\Support\AlmacenAmbito;
use App\Support\PermissionMatrixSync;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class MayoristaDemoSeeder extends Seeder
{
    public function run(): void
    {
        PermissionMatrixSync::syncRole('mayorista');

        Role::firstOrCreate(['name' => 'mayorista', 'guard_name' => 'web']);

        $usuario = Usuario::updateOrCreate(
            ['email' => 'Mayorista@gmail.com'],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Mayorista',
                'nombreusuario' => 'mayorista_demo',
                'telefono' => '700000300',
                'passwordhash' => Hash::make('password'),
                'role' => 'mayorista',
                'activo' => true,
                'estado_cuenta' => 'aprobado',
                'fecharegistro' => now(),
                'fechamodificacion' => now(),
            ]
        );

        $usuario->syncRoles(['mayorista']);

        if (! Schema::hasColumn('almacen', 'responsable_usuarioid')) {
            $this->command?->warn('Columna responsable_usuarioid ausente; ejecute migraciones primero.');

            return;
        }

        $vinculados = AlmacenAmbito::scope(Almacen::query(), AlmacenAmbito::MAYORISTA)
            ->where(function ($q) {
                $q->whereNull('responsable_usuarioid')
                    ->orWhere('responsable_usuarioid', 0);
            })
            ->update(['responsable_usuarioid' => $usuario->usuarioid]);

        $this->command?->info("Mayorista demo: Mayorista@gmail.com / password · Almacenes vinculados: {$vinculados}");
    }
}
