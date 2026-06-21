<?php

namespace App\Services;

use App\Models\Vehiculo;

/**
 * Dimensiones físicas de la caja de carga (largo × ancho × alto en metros).
 */
class VehiculoDimensionesService
{
  /** Proporción típica caja de camión: largo : ancho : alto */
  private const RATIO_L = 2.5;

  private const RATIO_W = 1.6;

  private const RATIO_H = 1.0;

  /**
   * @return array{
   *   largo_m: float,
   *   ancho_m: float,
   *   alto_m: float,
   *   volumen_m3: float,
   *   factor_volumen_util: float,
   *   m3_util: float,
   *   usa_override: bool,
   *   derivado_de_m3: bool,
   * }
   */
  public function dimensionesEfectivas(Vehiculo $vehiculo): array
  {
    $vehiculo->loadMissing('tipoVehiculo');

    $tipo = $vehiculo->tipoVehiculo;
    $factor = max(0.1, min(1.0, (float) ($tipo?->factor_volumen_util ?? 0.85)));

    $largo = $vehiculo->largo_m_override ?? $tipo?->largo_m;
    $ancho = $vehiculo->ancho_m_override ?? $tipo?->ancho_m;
    $alto = $vehiculo->alto_m_override ?? $tipo?->alto_m;

    $usaOverride = $vehiculo->largo_m_override !== null
      || $vehiculo->ancho_m_override !== null
      || $vehiculo->alto_m_override !== null;

    $derivadoDeM3 = false;

    if (! $largo || ! $ancho || ! $alto) {
      $capSvc = app(TransporteCapacidadService::class);
      $m3 = $capSvc->capacidadEfectiva($vehiculo)['m3'];
      $estimadas = $this->estimarDesdeVolumen($m3);
      $largo = $largo ?: $estimadas['largo_m'];
      $ancho = $ancho ?: $estimadas['ancho_m'];
      $alto = $alto ?: $estimadas['alto_m'];
      $derivadoDeM3 = true;
    }

    $largo = max(0.1, (float) $largo);
    $ancho = max(0.1, (float) $ancho);
    $alto = max(0.1, (float) $alto);
    $volumen = round($largo * $ancho * $alto, 3);
    $m3Util = round($volumen * $factor, 3);

    return [
      'largo_m' => $largo,
      'ancho_m' => $ancho,
      'alto_m' => $alto,
      'volumen_m3' => $volumen,
      'factor_volumen_util' => $factor,
      'm3_util' => $m3Util,
      'usa_override' => $usaOverride,
      'derivado_de_m3' => $derivadoDeM3,
    ];
  }

  /**
   * @return array{largo_m: float, ancho_m: float, alto_m: float}
   */
  public function estimarDesdeVolumen(float $m3): array
  {
    if ($m3 <= 0) {
      return ['largo_m' => 2.0, 'ancho_m' => 1.6, 'alto_m' => 1.2];
    }

    $k = pow($m3 / (self::RATIO_L * self::RATIO_W * self::RATIO_H), 1 / 3);

    return [
      'largo_m' => round(self::RATIO_L * $k, 2),
      'ancho_m' => round(self::RATIO_W * $k, 2),
      'alto_m' => round(self::RATIO_H * $k, 2),
    ];
  }
}
