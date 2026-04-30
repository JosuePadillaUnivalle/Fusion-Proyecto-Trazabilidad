<?php

namespace App\Support;

use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use Illuminate\Database\Eloquent\Builder;

final class DocumentoEntregaTransportista
{
    /**
     * POD/comprobante: debe existir una asignación del envío al transportista.
     * Si viene ID de envío externo, se usa ese criterio; si no, pedido.
     */
    public static function puedeSubirParaSusAsignaciones(int $usuarioid, ?string $externoEnvioId, ?int $pedidoId): bool
    {
        $tieneExterno = $externoEnvioId !== null && $externoEnvioId !== '';
        if (!$tieneExterno && $pedidoId === null) {
            return false;
        }

        $q = EnvioAsignacionMultiple::query()->where('transportista_usuarioid', $usuarioid);

        if ($externoEnvioId !== null && $externoEnvioId !== '') {
            return $q->where('externo_envio_id', $externoEnvioId)->exists();
        }

        return $q->where('pedidoid', $pedidoId)->exists();
    }

    /**
     * Listados: envíos asociados por asignación o archivos subidos por el propio usuario.
     */
    public static function restringirConsultaTransportista(Builder $query, int $usuarioid): Builder
    {
        $externoIds = EnvioAsignacionMultiple::query()
            ->where('transportista_usuarioid', $usuarioid)
            ->whereNotNull('externo_envio_id')
            ->pluck('externo_envio_id');

        $pedidoIds = EnvioAsignacionMultiple::query()
            ->where('transportista_usuarioid', $usuarioid)
            ->whereNotNull('pedidoid')
            ->pluck('pedidoid');

        return $query->where(function (Builder $w) use ($usuarioid, $externoIds, $pedidoIds) {
            $w->where('usuarioid', $usuarioid);
            if ($externoIds->isNotEmpty()) {
                $w->orWhereIn('externo_envio_id', $externoIds);
            }
            if ($pedidoIds->isNotEmpty()) {
                $w->orWhereIn('pedidoid', $pedidoIds);
            }
        });
    }

    public static function puedeVerDocumento(DocumentoEntrega $documento, int $usuarioid): bool
    {
        if ((int) $documento->usuarioid === $usuarioid) {
            return true;
        }

        return self::puedeSubirParaSusAsignaciones(
            $usuarioid,
            $documento->externo_envio_id,
            $documento->pedidoid ? (int) $documento->pedidoid : null
        );
    }
}
