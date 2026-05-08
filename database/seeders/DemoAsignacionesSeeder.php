<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Usuario;

class DemoAsignacionesSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure at least 3 transportistas exist
        $transportistas = Usuario::where('role', 'transportista')->take(3)->get();
        if ($transportistas->count() < 3) {
            for ($i = $transportistas->count(); $i < 3; $i++) {
                $t = Usuario::create([
                    'nombre' => 'Transp' . rand(100,999),
                    'apellido' => 'Demo',
                    'email' => 'transp'.uniqid().'@example.test',
                    'nombreusuario' => 'transp'.uniqid(),
                    'telefono' => '000000000',
                    'role' => 'transportista',
                    'activo' => true,
                    'fecharegistro' => now(),
                ]);
                $transportistas->push($t);
            }
        }

        // Create 6 demo asignaciones
        for ($i = 1; $i <= 6; $i++) {
            EnvioAsignacionMultiple::create([
                'externo_envio_id' => 'DEMO-' . date('Ymd') . '-' . $i,
                'pedidoid' => null,
                'transportista_usuarioid' => $transportistas->random()->usuarioid,
                'asignadopor_usuarioid' => 1,
                'rutamultientregaid' => null,
                'vehiculo_ref' => 'PLACA-' . rand(1000,9999),
                'estado' => 'pendiente',
                'fecha_asignacion' => now(),
                'almacenid' => null,
                'detalles_productos' => [
                    ['sku' => 'SKU-'.rand(100,999), 'cantidad' => rand(1,50)],
                    ['sku' => 'SKU-'.rand(100,999), 'cantidad' => rand(1,50)],
                ],
            ]);
        }
    }
}
