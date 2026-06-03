<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Insumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\Usuario;
use App\Support\AlmacenAmbito;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\InsumoCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecepcionPlantaEnvioService
{
    public function confirmar(
        EnvioAsignacionMultiple $asignacion,
        Usuario $usuario,
        int $almacenid,
        int $insumoid,
        float $cantidad,
        ?string $observaciones = null
    ): void {
        if (! in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true)) {
            throw new \InvalidArgumentException('El envío debe estar en transporte hacia planta para confirmar la recepción.');
        }

        if ($asignacion->fecha_recepcion_planta) {
            throw new \InvalidArgumentException('Este envío ya fue confirmado en planta.');
        }

        $almacen = Almacen::query()
            ->whereKey($almacenid)
            ->where('activo', true)
            ->firstOrFail();

        if (($almacen->ambito ?? AlmacenAmbito::PLANTA) !== AlmacenAmbito::PLANTA) {
            throw new \InvalidArgumentException('Debe seleccionar un almacén de planta.');
        }

        $insumo = Insumo::query()->findOrFail($insumoid);
        if ((int) $insumo->almacenid !== (int) $almacenid) {
            $insumo->almacenid = $almacenid;
            $insumo->save();
        }

        $tipoIngreso = $this->tipoMovimientoIngresoRecepcion();

        DB::transaction(function () use ($asignacion, $usuario, $almacen, $insumo, $cantidad, $observaciones, $tipoIngreso) {
            $ahora = now();

            AlmacenMovimiento::create([
                'almacenid' => $almacen->almacenid,
                'insumoid' => $insumo->insumoid,
                'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                'usuarioid' => $usuario->usuarioid,
                'fecha' => $ahora->toDateString(),
                'cantidad' => $cantidad,
                'referencia' => $asignacion->externo_envio_id,
                'destino_motivo' => $almacen->nombre,
                'observaciones' => '[Recepción planta — '.$asignacion->externo_envio_id.'] '.($observaciones ?? ''),
            ]);

            $insumo->incrementarStock($cantidad);

            $asignacion->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
                'estado' => 'recibido_planta',
                'almacenid' => $almacen->almacenid,
                'fecha_recepcion_planta' => $ahora,
                'recepcion_usuarioid' => $usuario->usuarioid,
            ]));
        });
    }

    /**
     * @return array{cantidad: float, producto: ?string}
     */
    public function sugerenciaDesdeEnvio(EnvioAsignacionMultiple $asignacion): array
    {
        $detalles = is_array($asignacion->detalles_productos) ? $asignacion->detalles_productos : [];
        $primero = $detalles[0] ?? [];

        $cantidad = (float) ($primero['cantidad'] ?? $primero['peso_kg'] ?? $primero['peso'] ?? 0);
        $producto = (string) ($primero['producto'] ?? $primero['nombre'] ?? $primero['cultivo'] ?? '');

        return [
            'cantidad' => $cantidad > 0 ? $cantidad : 0,
            'producto' => $producto !== '' ? $producto : null,
        ];
    }

    public function resolverInsumoEnAlmacen(Almacen $almacen, ?string $nombreProducto): ?Insumo
    {
        if ($nombreProducto === null || trim($nombreProducto) === '') {
            return null;
        }

        InsumoCatalogo::asegurarCatalogosBase();
        $needle = Str::lower(trim($nombreProducto));

        return Insumo::query()
            ->where('almacenid', $almacen->almacenid)
            ->whereIn('tipoinsumoid', InsumoCatalogo::tiposValidosIds())
            ->get()
            ->first(function (Insumo $insumo) use ($needle) {
                $nombre = Str::lower($insumo->nombre);

                return Str::contains($nombre, $needle) || Str::contains($needle, $nombre);
            });
    }

    private function tipoMovimientoIngresoRecepcion(): TipoMovimientoAlmacen
    {
        $tipo = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->get()
            ->first(fn (TipoMovimientoAlmacen $t) => in_array(
                TipoMovimientoAlmacen::normalizeNombre($t->nombre),
                ['produccion recibida', 'producción recibida', 'compra', 'ajuste positivo'],
                true
            ));

        return $tipo ?? TipoMovimientoAlmacen::activosPorNaturaleza('ingreso')->firstOrFail();
    }
}
