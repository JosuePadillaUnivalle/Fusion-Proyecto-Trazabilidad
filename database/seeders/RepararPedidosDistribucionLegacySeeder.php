<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\PedidoDistribucion;
use App\Models\RutaDistribucion;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Services\PedidoDistribucionMayoristaService;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\RutaDistribucionCatalogo;
use App\Support\SimulacionRutaCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Corrige pedidos PDV del flujo anterior (en_transito sin simulación / rutas rotas).
 * php artisan db:seed --class=RepararPedidosDistribucionLegacySeeder
 */
class RepararPedidosDistribucionLegacySeeder extends Seeder
{
    /** @var list<string> */
    private const NUMEROS_LEGACY = [
        'PDV-20260605-0001',
        'PDV-20260611-0001',
        'PDV-20260611-0002',
        'PDV-20260613-0001',
    ];

    public function run(): void
    {
        $pirai = Almacen::query()
            ->where('nombre', 'like', '%Pirai%')
            ->where('ambito', 'mayorista')
            ->first();

        if ($pirai === null) {
            $this->command?->warn('No se encontró Almacén Pirai (mayorista).');

            return;
        }

        $admin = Usuario::query()->where('email', 'admin@agrofusion.com')->first();
        $creadoPor = (int) ($admin?->usuarioid ?? 1);

        $choferes = Usuario::query()
            ->whereIn('email', ['Pedro@gmail.com', 'Lucia@gmail.com', 'carlos.mayorista@gmail.com'])
            ->orderBy('usuarioid')
            ->get();

        if ($choferes->isEmpty()) {
            $this->command?->warn('Ejecute TransportistasMayoristaSeeder primero.');

            return;
        }

        $vehiculos = Vehiculo::query()
            ->whereIn('placa', ['SCZ-MAY-01', 'SCZ-MAY-02', 'SCZ-MAY-03', 'SCZ-MAY-04'])
            ->where('activo', true)
            ->orderBy('vehiculoid')
            ->get();

        $service = app(PedidoDistribucionMayoristaService::class);
        $reparados = 0;

        foreach (self::NUMEROS_LEGACY as $i => $numero) {
            $pedido = PedidoDistribucion::query()
                ->with(['rutaDistribucion', 'puntoVenta'])
                ->where('numero_solicitud', $numero)
                ->first();

            if ($pedido === null) {
                $this->command?->warn("Pedido {$numero} no encontrado.");

                continue;
            }

            if ($pedido->estado === PedidoDistribucionCatalogo::ESTADO_RECIBIDO) {
                $this->command?->line("{$numero}: ya recibido, se omite.");

                continue;
            }

            DB::transaction(function () use ($pedido, $pirai, $creadoPor) {
                $this->limpiarRutaRota($pedido);

                $pedido->update([
                    'estado' => PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
                    'almacen_mayorista_origenid' => $pirai->almacenid,
                    'rutadistribucionid' => null,
                    'transportista_usuarioid' => null,
                    'vehiculoid' => null,
                    'fecha_envio' => null,
                    'fecha_aceptacion' => $pedido->fecha_aceptacion ?? now(),
                    'aceptado_por_usuarioid' => $pedido->aceptado_por_usuarioid ?? $creadoPor,
                ]);
            });

            $chofer = $choferes[$i % $choferes->count()];
            $vehiculo = $vehiculos[$i % max(1, $vehiculos->count())] ?? $vehiculos->first();

            if ($vehiculo === null) {
                $this->command?->warn("{$numero}: sin vehículo mayorista.");

                continue;
            }

            try {
                $service->designarTransportista(
                    $pedido->fresh(),
                    (int) $chofer->usuarioid,
                    (int) $vehiculo->vehiculoid,
                    $creadoPor
                );
                $reparados++;
                $pdv = $pedido->puntoVenta?->nombre ?? 'PDV';
                $this->command?->info("{$numero} → {$pdv} desde {$pirai->nombre} ({$chofer->nombre}, {$vehiculo->placa})");
            } catch (\Throwable $e) {
                $this->command?->error("{$numero}: {$e->getMessage()}");
            }
        }

        $this->command?->info("Reparados: {$reparados} pedido(s). Marque «En ruta» desde el detalle como admin.");
    }

    private function limpiarRutaRota(PedidoDistribucion $pedido): void
    {
        if ($pedido->rutadistribucionid === null) {
            return;
        }

        $ruta = RutaDistribucion::query()
            ->with('pedidos')
            ->find($pedido->rutadistribucionid);

        if ($ruta === null) {
            return;
        }

        $simActiva = SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta);

        if ($simActiva) {
            throw new \RuntimeException("La ruta {$ruta->codigo} tiene simulación activa; no se puede reparar automáticamente.");
        }

        foreach ($ruta->pedidos as $p) {
            $p->update(['rutadistribucionid' => null]);
        }

        $ruta->paradas()->delete();
        $ruta->delete();
    }
}
