<?php

namespace App\Services\Blockchain;

use App\Jobs\RegistrarCertificacionBlockchainJob;
use App\Models\CertificacionLote;
use Illuminate\Support\Facades\Log;

class CertificacionBlockchainService
{
    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_CONFIRMADO = 'confirmado';

    public const ESTADO_ERROR = 'error';

    public const ESTADO_OMITIDO = 'omitido';

    public function __construct(
        private readonly BlockchainClient $client,
    ) {}

    /**
     * Encola el ancla on-chain tras una certificación conforme (no bloquea la UI).
     */
    public function encolarSiCorresponde(CertificacionLote $certificacion): void
    {
        if (! $certificacion->esCertificado()) {
            $certificacion->forceFill([
                'blockchain_estado' => self::ESTADO_OMITIDO,
                'blockchain_error' => 'Solo se anclan certificaciones conformes.',
            ])->save();

            return;
        }

        if (! $this->client->enabled()) {
            $certificacion->forceFill([
                'blockchain_estado' => self::ESTADO_OMITIDO,
                'blockchain_error' => 'Integración blockchain deshabilitada.',
            ])->save();

            return;
        }

        $certificacion->forceFill([
            'blockchain_estado' => self::ESTADO_PENDIENTE,
            'blockchain_error' => null,
            'blockchain_dato_id' => $this->datoIdPara($certificacion),
        ])->save();

        RegistrarCertificacionBlockchainJob::dispatch($certificacion->certificacionid)
            ->afterResponse();
    }

    public function enviar(int $certificacionId): bool
    {
        $cert = CertificacionLote::with(['lote.cultivo', 'usuario'])->find($certificacionId);
        if (! $cert) {
            return false;
        }

        if (! $cert->esCertificado()) {
            $cert->forceFill([
                'blockchain_estado' => self::ESTADO_OMITIDO,
                'blockchain_error' => 'Solo se anclan certificaciones conformes.',
            ])->save();

            return false;
        }

        if (! $this->client->enabled()) {
            $cert->forceFill([
                'blockchain_estado' => self::ESTADO_OMITIDO,
                'blockchain_error' => 'Integración blockchain deshabilitada.',
            ])->save();

            return false;
        }

        if ($cert->blockchain_estado === self::ESTADO_CONFIRMADO && filled($cert->blockchain_txid)) {
            return true;
        }

        $datoId = $cert->blockchain_dato_id ?: $this->datoIdPara($cert);
        $tipo = 'certificacion_lote';
        $payload = $this->payloadPara($cert);

        $cert->forceFill([
            'blockchain_dato_id' => $datoId,
            'blockchain_intentos' => ((int) $cert->blockchain_intentos) + 1,
            'blockchain_estado' => self::ESTADO_PENDIENTE,
        ])->save();

        $result = $this->client->crearDato($datoId, $tipo, $payload);

        // Si ya existía en el ledger (reintento / re-certificación), actualizar.
        if (! $result['ok'] && $this->pareceYaExiste($result)) {
            $result = $this->client->actualizarDato($datoId, $tipo, $payload);
        }

        if ($result['ok'] && filled($result['tx_id'])) {
            $cert->forceFill([
                'blockchain_estado' => self::ESTADO_CONFIRMADO,
                'blockchain_txid' => $result['tx_id'],
                'blockchain_error' => null,
                'blockchain_enviado_en' => now(),
            ])->save();

            Log::info('Certificación anclada en blockchain', [
                'certificacionid' => $cert->certificacionid,
                'datoId' => $datoId,
                'txId' => $result['tx_id'],
            ]);

            return true;
        }

        // 201/200 sin txId raro pero posible: marcar confirmado si status OK.
        if ($result['ok']) {
            $cert->forceFill([
                'blockchain_estado' => self::ESTADO_CONFIRMADO,
                'blockchain_txid' => $result['tx_id'],
                'blockchain_error' => null,
                'blockchain_enviado_en' => now(),
            ])->save();

            return true;
        }

        $cert->forceFill([
            'blockchain_estado' => self::ESTADO_ERROR,
            'blockchain_error' => mb_substr($result['message'] ?: 'Error desconocido al anclar', 0, 2000),
        ])->save();

        Log::warning('Fallo al anclar certificación en blockchain', [
            'certificacionid' => $cert->certificacionid,
            'status' => $result['status'],
            'message' => $result['message'],
        ]);

        return false;
    }

    /**
     * Reintenta pendientes/errores con intentos bajo el máximo.
     *
     * @return array{procesados:int, confirmados:int, fallidos:int}
     */
    public function sincronizarPendientes(?int $limite = 50): array
    {
        $max = (int) config('blockchain.max_intentos', 8);
        $limite = max(1, $limite ?? 50);

        $ids = CertificacionLote::query()
            ->where('resultado', CertificacionLote::RAZON_CERTIFICADO)
            ->where(function ($q) {
                $q->whereNull('blockchain_estado')
                    ->orWhereIn('blockchain_estado', [self::ESTADO_PENDIENTE, self::ESTADO_ERROR]);
            })
            ->where('blockchain_intentos', '<', $max)
            ->orderBy('certificacionid')
            ->limit($limite)
            ->pluck('certificacionid');

        $confirmados = 0;
        $fallidos = 0;

        foreach ($ids as $id) {
            if ($this->enviar((int) $id)) {
                $confirmados++;
            } else {
                $fallidos++;
            }
        }

        return [
            'procesados' => $ids->count(),
            'confirmados' => $confirmados,
            'fallidos' => $fallidos,
        ];
    }

    public function datoIdPara(CertificacionLote $cert): string
    {
        $codigo = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $cert->codigo_certificado) ?: ('CERT-'.$cert->certificacionid);

        return 'AF-'.$codigo;
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadPara(CertificacionLote $cert): array
    {
        $lote = $cert->lote;
        $usuario = $cert->usuario;

        return [
            'origen' => 'agrofusion',
            'certificacionid' => $cert->certificacionid,
            'loteid' => $cert->loteid,
            'codigo_certificado' => $cert->codigo_certificado,
            'codigo_trazabilidad' => $lote?->codigo_trazabilidad,
            'resultado' => $cert->resultado,
            'observaciones' => $cert->observaciones,
            'fecha_certificacion' => optional($cert->fecha_certificacion)->toIso8601String(),
            'lote_nombre' => $lote?->nombre,
            'cultivo' => $lote?->cultivo?->nombre ?? $lote?->cultivo_etiqueta,
            'ubicacion' => $lote?->ubicacion,
            'certificado_por' => $usuario
                ? trim(($usuario->nombre ?? '').' '.($usuario->apellido ?? ''))
                : null,
            'usuarioid' => $cert->usuarioid,
        ];
    }

    /**
     * @param  array{ok: bool, status: int, message: string, body: mixed}  $result
     */
    private function pareceYaExiste(array $result): bool
    {
        $msg = mb_strtolower($result['message'] ?? '');
        if (str_contains($msg, 'ya existe') || str_contains($msg, 'already exists')) {
            return true;
        }

        if (is_array($result['body'] ?? null)) {
            $inner = mb_strtolower((string) ($result['body']['mensaje'] ?? ''));
            if (str_contains($inner, 'ya existe')) {
                return true;
            }
        }

        return false;
    }
}
