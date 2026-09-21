<?php

namespace Database\Seeders;

use App\Models\PerfilTransportista;
use App\Models\Usuario;
use App\Support\CuentaEstado;
use App\Support\TransportistaFlotaCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Inserta una cuenta operativa por rol (nombres reales, password 12345).
 * Solo crea si el email no existe — no modifica cuentas ya presentes.
 *
 * php artisan db:seed --class=CuentasOperativasPorRolSeeder --force
 */
class CuentasOperativasPorRolSeeder extends Seeder
{
    private const PASSWORD = '12345';

    public function run(): void
    {
        foreach ([
            'admin', 'agricultor', 'jefe_agricultor', 'planta', 'jefe_planta',
            'transportista', 'minorista', 'mayorista', 'jefe_mayorista',
        ] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $defs = [
            [
                'email' => 'RicardoMendoza@gmail.com',
                'nombre' => 'Ricardo',
                'apellido' => 'Mendoza',
                'nombreusuario' => 'rmendoza',
                'telefono' => '+591 70011001',
                'role' => 'agricultor',
                'roles' => ['jefe_agricultor'],
                'etiqueta' => 'Jefe Agricultor',
            ],
            [
                'email' => 'AndresQuispe@gmail.com',
                'nombre' => 'Andrés',
                'apellido' => 'Quispe',
                'nombreusuario' => 'aquispe',
                'telefono' => '+591 70011002',
                'role' => 'agricultor',
                'roles' => ['agricultor'],
                'supervisor_email' => 'RicardoMendoza@gmail.com',
                'etiqueta' => 'Operador Agricultor',
            ],
            [
                'email' => 'PatriciaLuna@gmail.com',
                'nombre' => 'Patricia',
                'apellido' => 'Luna',
                'nombreusuario' => 'pluna',
                'telefono' => '+591 70012001',
                'role' => 'jefe_planta',
                'roles' => ['jefe_planta'],
                'etiqueta' => 'Jefe Planta',
            ],
            [
                'email' => 'DiegoVargas@gmail.com',
                'nombre' => 'Diego',
                'apellido' => 'Vargas',
                'nombreusuario' => 'dvargas',
                'telefono' => '+591 70012002',
                'role' => 'planta',
                'roles' => ['planta'],
                'supervisor_email' => 'PatriciaLuna@gmail.com',
                'etiqueta' => 'Operador Planta',
            ],
            [
                'email' => 'HectorRamirez@gmail.com',
                'nombre' => 'Héctor',
                'apellido' => 'Ramírez',
                'nombreusuario' => 'hramirez',
                'telefono' => '+591 70013001',
                'role' => 'transportista',
                'roles' => ['transportista'],
                'tipo_licencia' => 'C',
                'perfil' => [
                    'ambito_flota' => TransportistaFlotaCatalogo::AGRICOLA,
                    'licencia' => 'C-7701001',
                    'tipo_licencia' => 'C',
                ],
                'etiqueta' => 'Transportista agrícola',
            ],
            [
                'email' => 'SofiaCastro@gmail.com',
                'nombre' => 'Sofía',
                'apellido' => 'Castro',
                'nombreusuario' => 'scastro',
                'telefono' => '+591 70013002',
                'role' => 'transportista',
                'roles' => ['transportista'],
                'tipo_licencia' => 'C',
                'perfil' => [
                    'ambito_flota' => TransportistaFlotaCatalogo::PLANTA,
                    'licencia' => 'C-7701002',
                    'tipo_licencia' => 'C',
                ],
                'etiqueta' => 'Transportista planta',
            ],
            [
                'email' => 'GabrielTorrez@gmail.com',
                'nombre' => 'Gabriel',
                'apellido' => 'Torrez',
                'nombreusuario' => 'gtorrez',
                'telefono' => '+591 70013003',
                'role' => 'transportista',
                'roles' => ['transportista'],
                'tipo_licencia' => 'C',
                'perfil' => [
                    'ambito_flota' => TransportistaFlotaCatalogo::MAYORISTA,
                    'licencia' => 'C-7701003',
                    'tipo_licencia' => 'C',
                ],
                'etiqueta' => 'Transportista mayorista',
            ],
            [
                'email' => 'CamilaRojas@gmail.com',
                'nombre' => 'Camila',
                'apellido' => 'Rojas',
                'nombreusuario' => 'crojas',
                'telefono' => '+591 70014001',
                'role' => 'mayorista',
                'roles' => ['mayorista', 'jefe_mayorista'],
                'etiqueta' => 'Mayorista',
            ],
            [
                'email' => 'NicolasParedes@gmail.com',
                'nombre' => 'Nicolás',
                'apellido' => 'Paredes',
                'nombreusuario' => 'nparedes',
                'telefono' => '+591 70015001',
                'role' => 'minorista',
                'roles' => ['minorista'],
                'etiqueta' => 'Minorista',
            ],
        ];

        /** @var array<string, Usuario> $creados */
        $creados = [];

        foreach ($defs as $def) {
            $email = $def['email'];
            $existente = Usuario::query()
                ->whereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower($email)])
                ->first();

            if ($existente) {
                $creados[mb_strtolower($email)] = $existente;
                $this->command?->line('Ya existe (sin cambios): '.$email.' — '.$def['etiqueta']);

                continue;
            }

            $payload = [
                'nombre' => $def['nombre'],
                'apellido' => $def['apellido'],
                'nombreusuario' => $def['nombreusuario'],
                'email' => $email,
                'telefono' => $def['telefono'] ?? null,
                'role' => $def['role'],
                'passwordhash' => Hash::make(self::PASSWORD),
                'activo' => true,
            ];

            if (Schema::hasColumn('usuario', 'estado_cuenta')) {
                $payload['estado_cuenta'] = CuentaEstado::APROBADO;
            }
            if (Schema::hasColumn('usuario', 'fecharegistro')) {
                $payload['fecharegistro'] = now();
            }
            if (Schema::hasColumn('usuario', 'tipo_licencia') && ! empty($def['tipo_licencia'])) {
                $payload['tipo_licencia'] = $def['tipo_licencia'];
            }

            $usuario = Usuario::create($payload);
            $usuario->syncRoles($def['roles']);

            if (! empty($def['perfil']) && Schema::hasTable('perfil_transportista')) {
                $p = $def['perfil'];
                PerfilTransportista::query()->firstOrCreate(
                    ['usuarioid' => $usuario->usuarioid],
                    [
                        'ambito_flota' => $p['ambito_flota'],
                        'licencia' => $p['licencia'] ?? null,
                        'tipo_licencia' => $p['tipo_licencia'] ?? ($def['tipo_licencia'] ?? null),
                        'disponible' => true,
                    ]
                );
            }

            $creados[mb_strtolower($email)] = $usuario;
            $this->command?->info('Creado: '.$email.' / 12345 — '.$def['etiqueta']);
        }

        foreach ($defs as $def) {
            if (empty($def['supervisor_email'])) {
                continue;
            }
            $empleado = $creados[mb_strtolower($def['email'])] ?? null;
            $jefe = $creados[mb_strtolower($def['supervisor_email'])] ?? Usuario::query()
                ->whereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower($def['supervisor_email'])])
                ->first();

            if ($empleado && $jefe && Schema::hasColumn('usuario', 'supervisor_usuarioid')) {
                if (! $empleado->supervisor_usuarioid) {
                    $empleado->update(['supervisor_usuarioid' => $jefe->usuarioid]);
                }
            }
        }
    }
}
