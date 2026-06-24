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

        if (! app()->environment('local')) {
            return false;
        }

        return config('database.default') === 'sqlite';
    }

    /** Restaura snapshot si la base local no tiene usuarios. */
    public static function asegurar(bool $silencioso = true): bool
    {
        if (! self::debeProteger()) {
            return false;
        }

        if (! Schema::hasTable('usuario')) {
            return false;
        }

        try {
            $usuarios = (int) DB::table('usuario')->count();
        } catch (\Throwable) {
            $usuarios = 0;
        }

        if ($usuarios > 0) {
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

        $hash = Hash::make(self::DEMO_PASSWORD);

        DB::table('usuario')
            ->whereIn('email', self::DEMO_EMAILS)
            ->update([
                'passwordhash' => $hash,
                'activo' => 1,
            ]);
    }
}
