<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Evita quedarse sin usuarios en desarrollo local cuando Git sobrescribe database.sqlite vacío.
 */
class LocalDatabaseGuard
{
    private const DEMO_PASSWORD = '12345';

    /** Incrementar al cambiar la política de contraseñas locales. */
    private const CREDENCIALES_LOCAL_VERSION = 2;

    /** @var list<string> */
    private const DEMO_EMAILS = [
        'admin@agrofusion.com',
        'agricultor@agrofusion.com',
        'planta@agrofusion.com',
        'transportista@agrofusion.com',
        'operador@agrofusion.com',
        'almacen@agrofusion.com',
        'LuisGuerrero123@gmail.com',
    ];

    public static function debeProteger(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        if (env('CI') || env('GITHUB_ACTIONS')) {
            return false;
        }

        if (! app()->environment('local')) {
            return false;
        }

        if (config('database.default') !== 'sqlite') {
            return false;
        }

        $dbPath = (string) (config('database.connections.sqlite.database') ?? '');
        if ($dbPath !== '' && $dbPath !== ':memory:' && ! is_file($dbPath)) {
            return false;
        }

        return true;
    }

    /** Restaura snapshot si la base local no tiene usuarios. */
    public static function asegurar(bool $silencioso = true): bool
    {
        if (! self::debeProteger()) {
            return false;
        }

        try {
            if (! Schema::hasTable('usuario')) {
                return false;
            }
        } catch (\Throwable) {
            return false;
        }

        try {
            $usuarios = (int) DB::table('usuario')->count();
        } catch (\Throwable) {
            $usuarios = 0;
        }

        if ($usuarios > 0) {
            self::normalizarCredencialesDemoSiHaceFalta();

            return false;
        }

        $origen = database_path('database.snapshot.sqlite');
        $destino = database_path('database.sqlite');

        if (! is_file($origen)) {
            if (! $silencioso) {
                Log::warning('LocalDatabaseGuard: database.snapshot.sqlite no existe; no se puede restaurar.');
            }

            return false;
        }

        try {
            $usuariosSnapshot = (int) (new \PDO('sqlite:'.$origen))
                ->query('SELECT COUNT(*) FROM usuario')
                ->fetchColumn();
        } catch (\Throwable) {
            $usuariosSnapshot = 0;
        }

        if ($usuariosSnapshot <= 0) {
            return false;
        }

        if (! copy($origen, $destino)) {
            Log::error('LocalDatabaseGuard: no se pudo copiar database.snapshot.sqlite → database.sqlite');

            return false;
        }

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        self::normalizarCredencialesDemo();

        Log::info('LocalDatabaseGuard: base restaurada desde database.snapshot.sqlite (usuarios demo recuperados).');

        return true;
    }

    public static function normalizarCredencialesDemo(): void
    {
        if (! Schema::hasTable('usuario')) {
            return;
        }

        if (self::debeProteger()) {
            self::normalizarTodasCredencialesLocales();

            return;
        }

        $hash = Hash::make(self::DEMO_PASSWORD);

        DB::table('usuario')
            ->whereIn('email', self::DEMO_EMAILS)
            ->update([
                'passwordhash' => $hash,
                'activo' => 1,
            ]);
    }

    /** En local todas las cuentas usan la misma clave documentada (12345). */
    public static function normalizarTodasCredencialesLocales(): void
    {
        if (! Schema::hasTable('usuario')) {
            return;
        }

        $hash = Hash::make(self::DEMO_PASSWORD);

        DB::table('usuario')->update([
            'passwordhash' => $hash,
            'activo' => 1,
        ]);

        if (Schema::hasColumn('usuario', 'estado_cuenta')) {
            DB::table('usuario')
                ->where('estado_cuenta', CuentaEstado::PENDIENTE)
                ->update(['estado_cuenta' => CuentaEstado::APROBADO]);
        }

        cache()->forever('agrofusion_demo_cred_version', self::CREDENCIALES_LOCAL_VERSION);
    }

    /** Repara contraseñas demo tras pulls o migraciones que dejan hashes inconsistentes. */
    public static function normalizarCredencialesDemoSiHaceFalta(): void
    {
        if (! self::debeProteger()) {
            return;
        }

        $version = (int) cache()->get('agrofusion_demo_cred_version', 0);
        if ($version >= self::CREDENCIALES_LOCAL_VERSION) {
            return;
        }

        self::normalizarTodasCredencialesLocales();
    }
}
