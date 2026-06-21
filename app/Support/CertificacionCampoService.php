<?php

namespace App\Support;

use App\Models\CertificacionLote;
use App\Models\Lote;

final class CertificacionCampoService
{
    public function ultima(Lote $lote): ?CertificacionLote
    {
        $lote->loadMissing('certificaciones');

        return $lote->certificaciones
            ->sortByDesc(fn (CertificacionLote $c) => $c->fecha_certificacion?->timestamp ?? 0)
            ->first();
    }

    public function estaCertificado(Lote $lote): bool
    {
        $ultima = $this->ultima($lote);

        return $ultima !== null && $ultima->esCertificado();
    }

    public function esNoConforme(Lote $lote): bool
    {
        $ultima = $this->ultima($lote);

        return $ultima !== null && $ultima->esNoConforme();
    }

    public function fueEvaluado(Lote $lote): bool
    {
        return $this->ultima($lote) !== null;
    }

    public function puedeEnviarAlmacen(Lote $lote): bool
    {
        return $this->estaCertificado($lote);
    }

    public function mensajeBloqueoAlmacen(Lote $lote): ?string
    {
        if ($this->esNoConforme($lote)) {
            $motivo = trim((string) ($this->ultima($lote)?->observaciones ?? ''));

            return $motivo !== ''
                ? 'El lote está marcado como No conforme y no puede ingresar al almacén. Motivo: '.$motivo
                : 'El lote está marcado como No conforme y no puede ingresar al almacén.';
        }

        if (! $this->estaCertificado($lote)) {
            return 'El lote aún no está certificado. Certifíquelo en Certificaciones antes de enviar la cosecha al almacén.';
        }

        return null;
    }
}
