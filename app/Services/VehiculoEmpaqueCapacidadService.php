<?php

namespace App\Services;

use App\Models\CatalogoTamanoConteo;
use App\Models\TipoEmpaque;
use App\Models\Vehiculo;
use App\Support\CadenaFrioTransporteCatalogo;

/**
 * Matriz vehículo ↔ empaque: cuántas cajas caben según peso y volumen.
 */
class VehiculoEmpaqueCapacidadService
{
  public function __construct(
    private readonly TransporteCapacidadService $capacidadSvc,
    private readonly VehiculoDimensionesService $dimensionesSvc,
    private readonly CargaCalculoService $cargaSvc,
  ) {}

  /**
   * @return array{
   *   capacidad: array<string, mixed>,
   *   dimensiones: array<string, mixed>,
   *   tipos_transporte: list<array{id: int, nombre: string, codigo: ?string}>,
   *   codigos_transporte: list<string>,
   *   empaques_genericos: list<array<string, mixed>>,
   *   empaques_por_calibre: list<array<string, mixed>>,
   * }
   */
  public function resumenParaVehiculo(Vehiculo $vehiculo): array
  {
    $vehiculo->loadMissing(['tipoVehiculo.tiposTransporte', 'tiposTransporte']);

    $cap = $this->capacidadSvc->capacidadEfectiva($vehiculo);
    $dims = $this->dimensionesSvc->dimensionesEfectivas($vehiculo);
    $m3Util = $dims['m3_util'] > 0 ? $dims['m3_util'] : $cap['m3'];

    $tiposTransporte = $vehiculo->tiposTransporteEfectivos();
    $codigosTransporte = $tiposTransporte
      ->pluck('codigo')
      ->filter()
      ->values()
      ->all();

    return [
      'capacidad' => $cap,
      'dimensiones' => $dims,
      'tipos_transporte' => $tiposTransporte->map(fn ($t) => [
        'id' => $t->tipotransporteid,
        'nombre' => $t->nombre,
        'codigo' => $t->codigo,
      ])->values()->all(),
      'codigos_transporte' => $codigosTransporte,
      'empaques_genericos' => $this->filasEmpaquesGenericos($cap['kg'], $m3Util),
      'empaques_por_calibre' => $this->filasPorCalibre($cap['kg'], $m3Util, $codigosTransporte),
    ];
  }

  /**
   * @return list<array<string, mixed>>
   */
  private function filasEmpaquesGenericos(float $capKg, float $m3Util): array
  {
    $empaques = TipoEmpaque::query()
      ->where('activo', true)
      ->orderBy('nombre')
      ->get();

    $filas = [];

    foreach ($empaques as $empaque) {
      $volumen = $this->volumenEmpaqueM3($empaque);
      $porVolumen = ($volumen && $m3Util > 0)
        ? (int) floor($m3Util / $volumen)
        : null;

      $tara = (float) ($empaque->tara_kg ?? 0);
      $porPeso = ($tara > 0 && $capKg > 0)
        ? (int) floor($capKg / $tara)
        : null;

      $filas[] = [
        'tipo' => 'generico',
        'empaque_id' => $empaque->tipoempaqueid,
        'empaque_nombre' => $empaque->nombre,
        'producto_nombre' => null,
        'calibre_nombre' => null,
        'peso_bruto_kg' => $tara > 0 ? $tara : null,
        'volumen_m3' => $volumen,
        'max_por_peso' => $porPeso,
        'max_por_volumen' => $porVolumen,
        'max_efectivo' => $this->maxEfectivo($porPeso, $porVolumen),
        'limite_por' => $this->limitePor($porPeso, $porVolumen),
        'nota' => $tara > 0
          ? 'Solo tara del empaque; el peso con producto se calcula por calibre.'
          : 'Sin tara registrada; límite por volumen.',
        'advertencia_termica' => null,
      ];
    }

    return $filas;
  }

  /**
   * @param  list<string>  $codigosTransporte
   * @return list<array<string, mixed>>
   */
  private function filasPorCalibre(float $capKg, float $m3Util, array $codigosTransporte): array
  {
    $calibres = CatalogoTamanoConteo::query()
      ->with(['insumo', 'tipoEmpaque'])
      ->where('activo', true)
      ->orderBy('insumoid')
      ->orderBy('nombre')
      ->get();

    $filas = [];

    foreach ($calibres as $calibre) {
      $empaque = $calibre->tipoEmpaque;
      if (! $empaque) {
        continue;
      }

      $calc = $this->cargaSvc->calcular([
        'conteo_por_empaque' => $calibre->conteo_por_empaque,
        'peso_promedio_kg' => $calibre->peso_promedio_kg,
        'largo_cm' => $empaque->largo_cm,
        'ancho_cm' => $empaque->ancho_cm,
        'alto_cm' => $empaque->alto_cm,
        'tara_kg' => $empaque->tara_kg,
        'forma_pedido' => 'empaques',
        'cantidad_pedido' => 1,
      ]);

      $pesoBruto = (float) $calc['peso_bruto_kg'];
      $volumen = $calc['volumen_empaque_m3'];

      $max = $this->cargaSvc->empaquesMaximosEnVehiculo($capKg, $m3Util, $pesoBruto, $volumen);

      $porPeso = ($pesoBruto > 0 && $capKg > 0) ? (int) floor($capKg / $pesoBruto) : null;
      $porVolumen = ($volumen && $volumen > 0 && $m3Util > 0) ? (int) floor($m3Util / $volumen) : null;

      $productoNombre = $calibre->insumo?->nombre;

      $filas[] = [
        'tipo' => 'calibre',
        'empaque_id' => $empaque->tipoempaqueid,
        'empaque_nombre' => $empaque->nombre,
        'producto_nombre' => $productoNombre,
        'calibre_nombre' => $calibre->nombre,
        'calibre_id' => $calibre->catalogotamanoconteoid,
        'peso_bruto_kg' => $pesoBruto,
        'volumen_m3' => $volumen,
        'max_por_peso' => $porPeso,
        'max_por_volumen' => $porVolumen,
        'max_efectivo' => $max,
        'limite_por' => $this->limitePor($porPeso, $porVolumen),
        'nota' => null,
        'advertencia_termica' => CadenaFrioTransporteCatalogo::advertenciaTermica(
          $productoNombre,
          $codigosTransporte
        ),
      ];
    }

    return $filas;
  }

  private function volumenEmpaqueM3(TipoEmpaque $empaque): ?float
  {
    $l = (float) ($empaque->largo_cm ?? 0);
    $a = (float) ($empaque->ancho_cm ?? 0);
    $h = (float) ($empaque->alto_cm ?? 0);

    if ($l <= 0 || $a <= 0 || $h <= 0) {
      return null;
    }

    return round(($l / 100) * ($a / 100) * ($h / 100), 4);
  }

  private function maxEfectivo(?int $porPeso, ?int $porVolumen): ?int
  {
    if ($porPeso === null && $porVolumen === null) {
      return null;
    }
    if ($porPeso === null) {
      return $porVolumen;
    }
    if ($porVolumen === null) {
      return $porPeso;
    }

    return min($porPeso, $porVolumen);
  }

  private function limitePor(?int $porPeso, ?int $porVolumen): ?string
  {
    if ($porPeso === null && $porVolumen === null) {
      return null;
    }
    if ($porPeso === null) {
      return 'volumen';
    }
    if ($porVolumen === null) {
      return 'peso';
    }
    if ($porPeso <= $porVolumen) {
      return 'peso';
    }

    return 'volumen';
  }
}
