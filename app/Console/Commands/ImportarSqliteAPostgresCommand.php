<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportarSqliteAPostgresCommand extends Command
{
    protected $signature = 'agrofusion:importar-sqlite-a-postgres
                            {--sqlite=database/database.sqlite : Ruta al SQLite de origen}
                            {--fresh : migrate:fresh en PostgreSQL antes de importar}
                            {--force : Sin confirmación interactiva}';

    protected $description = 'Importa TODA la data de SQLite local a la conexión actual (PostgreSQL / Railway)';

    public function handle(): int
    {
        $sqlitePath = (string) $this->option('sqlite');
        if (! is_file($sqlitePath)) {
            $this->error("No existe el archivo SQLite: {$sqlitePath}");

            return self::FAILURE;
        }

        $destino = (string) config('database.default');
        $driver = (string) config("database.connections.{$destino}.driver");

        if ($driver !== 'pgsql') {
            $this->error('La conexión activa debe ser PostgreSQL (DB_CONNECTION=pgsql + DATABASE_URL de Railway).');
            $this->line('Ejemplo en PowerShell:');
            $this->line('  $env:DB_CONNECTION="pgsql"');
            $this->line('  $env:DATABASE_URL="postgresql://..."');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Esto REEMPLAZA los datos en PostgreSQL. ¿Continuar?', false)) {
            $this->warn('Cancelado.');

            return self::SUCCESS;
        }

        config([
            'database.connections.sqlite_import' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        if ($this->option('fresh')) {
            $this->info('Ejecutando migrate:fresh en PostgreSQL…');
            $this->call('migrate:fresh', ['--force' => true]);
        }

        $tablasSqlite = collect(DB::connection('sqlite_import')->select(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
        ))->pluck('name')->map(fn ($n) => (string) $n);

        $omitir = ['migrations'];
        $tablas = $tablasSqlite->reject(fn (string $t) => in_array($t, $omitir, true))->values();

        $this->info('Importando '.$tablas->count().' tablas desde SQLite…');

        DB::connection($destino)->statement("SET session_replication_role = 'replica'");

        $importadas = 0;
        $filasTotal = 0;

        foreach ($tablas as $tabla) {
            if (! Schema::connection($destino)->hasTable($tabla)) {
                $this->warn("  Omitida {$tabla}: no existe en PostgreSQL (¿faltó migración?)");

                continue;
            }

            $columnasDestino = Schema::connection($destino)->getColumnListing($tabla);
            if ($columnasDestino === []) {
                continue;
            }

            DB::connection($destino)->table($tabla)->delete();

            $filas = 0;
            $rows = DB::connection('sqlite_import')->table($tabla)->get();

            foreach ($rows->chunk(150) as $chunk) {
                $payload = [];
                foreach ($chunk as $fila) {
                    $row = (array) $fila;
                    $payload[] = array_intersect_key($row, array_flip($columnasDestino));
                }
                if ($payload !== []) {
                    DB::connection($destino)->table($tabla)->insert($payload);
                    $filas += count($payload);
                }
            }

            $this->reajustarSecuencia($destino, $tabla, $columnasDestino[0]);

            $importadas++;
            $filasTotal += $filas;
            $this->line("  ✓ {$tabla}: {$filas} filas");
        }

        DB::connection($destino)->statement("SET session_replication_role = 'origin'");

        $this->newLine();
        $this->info("Listo: {$importadas} tablas, {$filasTotal} filas importadas a PostgreSQL.");

        return self::SUCCESS;
    }

    /** @param  list<string>  $columnas */
    private function reajustarSecuencia(string $conexion, string $tabla, string $columnaPk): void
    {
        try {
            $max = DB::connection($conexion)->table($tabla)->max($columnaPk);
            if ($max === null) {
                return;
            }

            $seq = DB::connection($conexion)->selectOne(
                'SELECT pg_get_serial_sequence(?, ?) AS seq',
                [$tabla, $columnaPk]
            );

            if ($seq?->seq) {
                DB::connection($conexion)->statement(
                    'SELECT setval(?, ?, true)',
                    [$seq->seq, (int) $max]
                );
            }
        } catch (\Throwable) {
            // Tabla sin secuencia serial (pivots, etc.)
        }
    }
}
