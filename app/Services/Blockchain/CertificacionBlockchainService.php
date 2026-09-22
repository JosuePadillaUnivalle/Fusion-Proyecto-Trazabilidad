<?php

namespace App\Services\Blockchain;

use App\Jobs\RegistrarCertificacionBlockchainJob;
use App\Models\CertificacionLote;
use Illuminate\Support\Facades\Log;

class CertificacionBlockchainService
{
    public const ESTADO_PENDIENTE = CertificacionLote::BLOCKCHAIN_PENDIENTE;

    public const ESTADO_CERTIFICADA = CertificacionLote::BLOCKCHAIN_CERTIFICADA;

    public const ESTADO_CONFIRMADO = CertificacionLote::BLOCKCHAIN_CERTIFICADA;

    public const ESTADO_RECHAZADA = CertificacionLote::BLOCKCHAIN_RECHAZADA;

    public const ESTADO_ERROR = CertificacionLote::BLOCKCHAIN_ERROR;

    public const ESTADO_OMITIDA = CertificacionLote::BLOCKCHAIN_OMITIDA;

    public const ESTADO_OMITIDO = CertificacionLote::BLOCKCHAIN_OMITIDA;

    private const TIPO_DATO = 'certificacion_lote';

    public function __construct(
        private readonly BlockchainClient $client,
        private readonly CertificacionLotePayloadBuilder $payloadBuilder,
    ) {}

    /**
     * Encola la certificacion de lote tras una evaluacion conforme, sin bloquear la UI.
     */
    public function encolarSiCorresponde(CertificacionLote $certificacion): void
    {
        if (! $certificacion->esCertificado()) {
            $this->marcarOmitida($certificacion, 'Solo se anclan certificaciones conformes.');

            return;
        }

        if (! $this->client->enabled()) {
            $this->marcarOmitida($certificacion, 'Integración blockchain deshabilitada.');

            return;
        }

        $construido = $this->payloadBuilder->construir($certificacion);
        $hash = $construido['hash'];
        $datoId = $this->datoIdPara($certificacion);
        $operacionId = $this->operacionIdPara($certificacion, $hash);

        $certificacion->forceFill([
            'blockchain_estado' => self::ESTADO_PENDIENTE,
            'blockchain_error' => null,
            'blockchain_dato_id' => $datoId,
            'blockchain_solicitud_id' => null,
            'blockchain_operacion_id' => $operacionId,
            'blockchain_hash' => $hash,
            'blockchain_payload_version' => CertificacionLotePayloadBuilder::VERSION,
            'blockchain_txid' => null,
            'blockchain_certificado_en' => null,
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
            $this->marcarOmitida($cert, 'Solo se anclan certificaciones conformes.');

            return false;
        }

        if (! $this->client->enabled()) {
            $this->marcarOmitida($cert, 'Integración blockchain deshabilitada.');

            return false;
        }

        if ($cert->blockchainConfirmada()) {
            return true;
        }

        if (filled($cert->blockchain_operacion_id)) {
            $sincronizada = $this->sincronizarOperacion($cert);
            if ($sincronizada !== null) {
                return $sincronizada;
            }
        }

        return $this->crearSolicitud($cert);
    }

    /**
     * Revisa pendientes/errores con intentos bajo el maximo.
     *
     * @return array{procesados:int, certificadas:int, confirmados:int, pendientes:int, rechazadas:int, fallidos:int}
     */
    public function sincronizarPendientes(?int $limite = 50): array
    {
        $max = (int) config('blockchain.max_intentos', 8);
        $limite = max(1, $limite ?? 50);

        $ids = CertificacionLote::query()
            ->where('resultado', CertificacionLote::RAZON_CERTIFICADO)
            ->whereIn('blockchain_estado', [self::ESTADO_PENDIENTE, self::ESTADO_ERROR])
            ->where('blockchain_intentos', '<', $max)
            ->orderBy('certificacionid')
            ->limit($limite)
            ->pluck('certificacionid');

        $certificadas = 0;
        $pendientes = 0;
        $rechazadas = 0;
        $fallidos = 0;

        foreach ($ids as $id) {
            $this->enviar((int) $id);

            $estado = CertificacionLote::query()
                ->whereKey($id)
                ->value('blockchain_estado');

            match ($estado) {
                self::ESTADO_CERTIFICADA => $certificadas++,
                self::ESTADO_PENDIENTE => $pendientes++,
                self::ESTADO_RECHAZADA => $rechazadas++,
                default => $fallidos++,
            };
        }

        return [
            'procesados' => $ids->count(),
            'certificadas' => $certificadas,
            'confirmados' => $certificadas,
            'pendientes' => $pendientes,
            'rechazadas' => $rechazadas,
            'fallidos' => $fallidos,
        ];
    }

    public function datoIdPara(CertificacionLote $cert): string
    {
        $codigo = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $cert->codigo_certificado) ?: ('CERT-'.$cert->certificacionid);

        return 'AF-'.$codigo;
    }

    private function crearSolicitud(CertificacionLote $cert): bool
    {
        $construido = $this->payloadBuilder->construir($cert);
        $hash = $construido['hash'];
        $payload = $construido['payload'];
        $datoId = $cert->blockchain_dato_id ?: $this->datoIdPara($cert);
        $operacionId = $cert->blockchain_operacion_id ?: $this->operacionIdPara($cert, $hash);

        $cert->forceFill([
            'blockchain_dato_id' => $datoId,
            'blockchain_operacion_id' => $operacionId,
            'blockchain_hash' => $hash,
            'blockchain_payload_version' => CertificacionLotePayloadBuilder::VERSION,
            'blockchain_intentos' => ((int) $cert->blockchain_intentos) + 1,
            'blockchain_estado' => self::ESTADO_PENDIENTE,
            'blockchain_error' => null,
            'blockchain_enviado_en' => now(),
        ])->save();

        $result = $this->client->crearDato($datoId, self::TIPO_DATO, $payload, $this->headersPara($operacionId));

        if (! $result['ok'] && $this->pareceYaExiste($result)) {
            $result = $this->client->actualizarDato($datoId, self::TIPO_DATO, $payload, $this->headersPara($operacionId));
        }

        return $this->aplicarResultado($cert->fresh(), $result, $datoId, $operacionId, $hash);
    }

    /**
     * @return bool|null true si ya quedo certificada, false si sigue pendiente/fallo, null si no se pudo consultar.
     */
    private function sincronizarOperacion(CertificacionLote $cert): ?bool
    {
        $result = $this->client->consultarOperacion((string) $cert->blockchain_operacion_id);

        if ($result['status'] === 404) {
            return null;
        }

        if (! $result['ok']) {
            $cert->forceFill([
                'blockchain_error' => mb_substr($result['message'] ?: 'No se pudo consultar la operación blockchain.', 0, 2000),
            ])->save();

            return false;
        }

        return $this->aplicarResultado(
            $cert,
            $result,
            $cert->blockchain_dato_id ?: $this->datoIdPara($cert),
            (string) $cert->blockchain_operacion_id,
            (string) $cert->blockchain_hash,
            false
        );
    }

    /**
     * @param  array{ok: bool, status: int, estado: ?string, solicitud_id: ?string, operacion_id: ?string, dato_id: ?string, tx_id: ?string, body: mixed, message: string}  $result
     */
    private function aplicarResultado(
        ?CertificacionLote $cert,
        array $result,
        string $datoId,
        string $operacionId,
        string $hash,
        bool $registrarErrorComoFallo = true,
    ): bool {
        if (! $cert) {
            return false;
        }

        $estado = mb_strtolower((string) $result['estado']);

        if ($result['ok'] && filled($result['tx_id'])) {
            $cert->forceFill([
                'blockchain_estado' => self::ESTADO_CERTIFICADA,
                'blockchain_dato_id' => $result['dato_id'] ?: $datoId,
                'blockchain_solicitud_id' => $result['solicitud_id'] ?: $cert->blockchain_solicitud_id,
                'blockchain_operacion_id' => $result['operacion_id'] ?: $operacionId,
                'blockchain_hash' => $hash ?: $cert->blockchain_hash,
                'blockchain_payload_version' => CertificacionLotePayloadBuilder::VERSION,
                'blockchain_txid' => $result['tx_id'],
                'blockchain_error' => null,
                'blockchain_enviado_en' => $cert->blockchain_enviado_en ?: now(),
                'blockchain_certificado_en' => now(),
            ])->save();

            Log::info('Certificación de lote certificada en blockchain', [
                'certificacionid' => $cert->certificacionid,
                'datoId' => $datoId,
                'operacionId' => $operacionId,
                'txId' => $result['tx_id'],
            ]);

            return true;
        }

        if ($result['ok'] && in_array($estado, ['pendiente', 'en_proceso', 'procesando', 'aprobada'], true)) {
            $cert->forceFill([
                'blockchain_estado' => self::ESTADO_PENDIENTE,
                'blockchain_dato_id' => $result['dato_id'] ?: $datoId,
                'blockchain_solicitud_id' => $result['solicitud_id'] ?: $cert->blockchain_solicitud_id,
                'blockchain_operacion_id' => $result['operacion_id'] ?: $operacionId,
                'blockchain_hash' => $hash ?: $cert->blockchain_hash,
                'blockchain_payload_version' => CertificacionLotePayloadBuilder::VERSION,
                'blockchain_error' => null,
                'blockchain_enviado_en' => $cert->blockchain_enviado_en ?: now(),
            ])->save();

            return false;
        }

        if ($result['ok'] && in_array($estado, ['rechazada', 'rechazado'], true)) {
            $cert->forceFill([
                'blockchain_estado' => self::ESTADO_RECHAZADA,
                'blockchain_solicitud_id' => $result['solicitud_id'] ?: $cert->blockchain_solicitud_id,
                'blockchain_operacion_id' => $result['operacion_id'] ?: $operacionId,
                'blockchain_error' => mb_substr($result['message'] ?: 'Solicitud blockchain rechazada.', 0, 2000),
            ])->save();

            return false;
        }

        if ($registrarErrorComoFallo) {
            $cert->forceFill([
                'blockchain_estado' => self::ESTADO_ERROR,
                'blockchain_error' => mb_substr($result['message'] ?: 'Error desconocido al solicitar certificación blockchain.', 0, 2000),
            ])->save();

            Log::warning('Fallo al solicitar certificación de lote en blockchain', [
                'certificacionid' => $cert->certificacionid,
                'status' => $result['status'],
                'message' => $result['message'],
            ]);
        }

        return false;
    }

    private function operacionIdPara(CertificacionLote $cert, string $hash): string
    {
        $base = sprintf(
            'agrofusion.certificacion_lote.%s.v%s.%s',
            $cert->certificacionid,
            CertificacionLotePayloadBuilder::VERSION,
            substr($hash, 0, 16)
        );

        return preg_replace('/[^A-Za-z0-9_.-]/', '-', $base) ?: $base;
    }

    /**
     * @return array<string, string>
     */
    private function headersPara(string $operacionId): array
    {
        return [
            'X-Operacion-Id' => $operacionId,
            'X-Actor-Id' => 'agrofusion-certificacion-lote',
            'X-Actor-Name' => 'AgroFusion',
            'X-Actor-Rol' => 'sistema_integrador',
        ];
    }

    private function marcarOmitida(CertificacionLote $certificacion, string $motivo): void
    {
        $certificacion->forceFill([
            'blockchain_estado' => self::ESTADO_OMITIDA,
            'blockchain_error' => $motivo,
            'blockchain_solicitud_id' => null,
            'blockchain_operacion_id' => null,
            'blockchain_hash' => null,
            'blockchain_txid' => null,
            'blockchain_certificado_en' => null,
        ])->save();
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
