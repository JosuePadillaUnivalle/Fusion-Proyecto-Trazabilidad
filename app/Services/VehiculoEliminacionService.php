<?php

namespace App\Services;

use App\Exceptions\EliminacionBloqueadaException;
use App\Models\Vehiculo;
use App\Support\EliminacionSegura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VehiculoEliminacionService
{
  public function __construct(
    private readonly VehiculoFlotaEstadoService $flotaEstado,
  ) {}

  /**
   * @return list<string>
   */
  public function bloqueos(Vehiculo $vehiculo): array
  {
    $id = (int) $vehiculo->vehiculoid;
    $bloqueos = [];

    if ($this->flotaEstado->estaEnRuta($vehiculo)) {
      $bloqueos[] = 'está en ruta en tiempo real (finalice la simulación primero)';
    }

    if (Schema::hasTable('distribucion_salida')) {
      $n = DB::table('distribucion_salida')->where('vehiculoid', $id)->count();
      if ($n > 0) {
        $bloqueos[] = "tiene {$n} salida(s) de distribución registrada(s)";
      }
    }

    return $bloqueos;
  }

  /**
   * @throws EliminacionBloqueadaException
   */
  public function eliminar(Vehiculo $vehiculo): void
  {
    $bloqueos = $this->bloqueos($vehiculo);
    if ($bloqueos !== []) {
      throw new EliminacionBloqueadaException(
        'No se puede eliminar el vehículo '.$vehiculo->placa.' porque '
        .implode('; ', $bloqueos).'.'
      );
    }

    EliminacionSegura::ejecutar(function () use ($vehiculo): void {
      DB::transaction(function () use ($vehiculo): void {
        $id = (int) $vehiculo->vehiculoid;

        if (Schema::hasTable('ruta_distribucion') && Schema::hasColumn('ruta_distribucion', 'vehiculoid')) {
          DB::table('ruta_distribucion')->where('vehiculoid', $id)->update(['vehiculoid' => null]);
        }

        if (Schema::hasTable('pedido_distribucion') && Schema::hasColumn('pedido_distribucion', 'vehiculoid')) {
          DB::table('pedido_distribucion')->where('vehiculoid', $id)->update(['vehiculoid' => null]);
        }

        if (Schema::hasTable('perfil_transportista') && Schema::hasColumn('perfil_transportista', 'vehiculoid')) {
          DB::table('perfil_transportista')->where('vehiculoid', $id)->update(['vehiculoid' => null]);
        }

        $vehiculo->tiposTransporte()->detach();
        $vehiculo->delete();
      });
    }, 'No se puede eliminar el vehículo '.$vehiculo->placa.' porque tiene registros vinculados en el sistema.');
  }
}
