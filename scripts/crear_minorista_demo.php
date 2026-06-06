<?php

use App\Models\PuntoVenta;
use App\Models\Usuario;
use App\Support\CuentaEstado;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = 'minorista@agrofusion.com';
$password = 'Minorista2026';

Role::firstOrCreate(['name' => 'minorista', 'guard_name' => 'web']);

$permisosMinorista = config('permission_matrix.role_permissions.minorista', []);
foreach ($permisosMinorista as $permName) {
    Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
}

$usuario = Usuario::updateOrCreate(
    ['email' => $email],
    [
        'nombre' => 'María',
        'apellido' => 'Minorista Demo',
        'nombreusuario' => 'minorista_demo',
        'telefono' => '700000200',
        'passwordhash' => Hash::make($password),
        'role' => 'minorista',
        'activo' => true,
        'estado_cuenta' => CuentaEstado::APROBADO,
        'fecharegistro' => now(),
        'fechamodificacion' => now(),
    ]
);

$usuario->syncRoles(['minorista']);
$role = Role::findByName('minorista', 'web');
$role->syncPermissions($permisosMinorista);
app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

$punto = PuntoVenta::query()
    ->where('nombre', 'Mercado Prueba')
    ->orWhere('usuarioid', $usuario->usuarioid)
    ->first();

if ($punto === null) {
    $punto = PuntoVenta::create([
        'usuarioid' => $usuario->usuarioid,
        'nombre' => 'Tienda Demo Minorista',
        'direccion' => 'Av. Demo 123, Santa Cruz',
        'latitud' => -17.7833,
        'longitud' => -63.1821,
        'activo' => true,
        'observaciones' => 'Punto de venta de prueba para minorista demo',
        'fechacreacion' => now(),
    ]);
} else {
    $punto->update([
        'usuarioid' => $usuario->usuarioid,
        'activo' => true,
    ]);
}

echo json_encode([
    'ok' => true,
    'email' => $email,
    'password' => $password,
    'usuarioid' => $usuario->usuarioid,
    'punto_venta' => $punto->nombre,
    'puntoventaid' => $punto->puntoventaid,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
