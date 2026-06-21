<?php

namespace App\Support;

use App\Exceptions\EliminacionBloqueadaException;
use Illuminate\Database\QueryException;

final class EliminacionSegura
{
  /**
   * @throws EliminacionBloqueadaException
   */
  public static function ejecutar(callable $eliminar, ?string $mensajeFallback = null): void
  {
    try {
      $eliminar();
    } catch (EliminacionBloqueadaException $e) {
      throw $e;
    } catch (QueryException $e) {
      if (self::esViolacionFk($e)) {
        throw new EliminacionBloqueadaException(
          $mensajeFallback ?? self::mensajeGenerico()
        );
      }

      throw $e;
    }
  }

  public static function esViolacionFk(QueryException $e): bool
  {
    $sqlState = (string) ($e->errorInfo[0] ?? '');
    if ($sqlState === '23000') {
      return true;
    }

    $mensaje = strtolower($e->getMessage());

    return str_contains($mensaje, 'foreign key constraint failed')
      || str_contains($mensaje, 'integrity constraint violation')
      || str_contains($mensaje, '1451');
  }

  public static function mensajeGenerico(): string
  {
    return 'No se puede eliminar este registro porque otros datos del sistema dependen de él. '
      . 'Desvincule o archive las referencias primero.';
  }
}
