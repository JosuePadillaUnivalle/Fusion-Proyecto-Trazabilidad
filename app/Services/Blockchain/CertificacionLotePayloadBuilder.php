<?php

namespace App\Services\Blockchain;

use App\Models\CertificacionLote;
use App\Models\Produccion;
use App\Support\LoteTrazabilidadService;
use Illuminate\Support\Collection;

class CertificacionLotePayloadBuilder
{
    public const VERSION = 1;

    public function __construct(
        private readonly LoteTrazabilidadService $trazabilidad,
    ) {}

    /**
     * @return array{payload: array<string, mixed>, hash: string}
     */
    public function construir(CertificacionLote $certificacion): array
    {
        $payload = $this->payloadSinHash($certificacion);
        $hash = $this->hash($payload);
        $payload['hashRegistro'] = $hash;

        return [
            'payload' => $payload,
            'hash' => $hash,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadSinHash(CertificacionLote $certificacion): array
    {
        $certificacion->loadMissing([
            'lote.actividades.tipoActividad',
            'lote.cultivo',
            'lote.estadoTipo',
            'lote.producciones.unidadMedida',
            'lote.unidadSuperficie',
        ]);

        $lote = $certificacion->lote;
        $pendientes = $lote ? $this->trazabilidad->actividadesCrecimientoPendientes($lote) : [];

        return $this->canonicalizar([
            'version' => self::VERSION,
            'sistemaOrigen' => 'AgroFusion',
            'evento' => 'certificacion_lote',
            'certificacion' => [
                'certificacionId' => (int) $certificacion->certificacionid,
                'codigoCertificado' => $certificacion->codigo_certificado,
                'resultado' => $certificacion->resultado,
                'fechaCertificacion' => $this->fechaIso($certificacion->fecha_certificacion),
            ],
            'lote' => [
                'loteId' => $lote ? (int) $lote->loteid : null,
                'nombre' => $lote?->nombre,
                'codigoTrazabilidad' => $lote?->codigo_trazabilidad,
                'cultivo' => $lote?->cultivo?->nombre ?? $lote?->cultivo_etiqueta,
                'superficie' => $this->numero($lote?->superficie),
                'unidadSuperficie' => $lote?->unidadSuperficie?->abreviatura
                    ?: $lote?->unidadSuperficie?->nombre,
                'ubicacion' => $lote?->ubicacion,
                'fechaSiembra' => $this->fecha($lote?->fechasiembra),
            ],
            'cumplimientoCampo' => [
                'siembraCompletada' => $lote ? $this->trazabilidad->siembraCompletada($lote) : false,
                'riegoCompletado' => ! in_array('riego', $pendientes, true),
                'controlPlagasCompletado' => ! in_array('control de plagas', $pendientes, true),
                'fertilizacionCompletada' => ! in_array('fertilización', $pendientes, true),
                'cosechaRegistrada' => $lote ? $lote->producciones->isNotEmpty() : false,
            ],
            'resumenCosecha' => $this->resumenCosecha($lote?->producciones ?? collect()),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function hash(array $payload): string
    {
        return hash('sha256', $this->jsonCanonico($payload));
    }

    /**
     * @param  mixed  $valor
     * @return mixed
     */
    public function canonicalizar(mixed $valor): mixed
    {
        if (! is_array($valor)) {
            return $valor;
        }

        foreach ($valor as $key => $item) {
            $valor[$key] = $this->canonicalizar($item);
        }

        if (! array_is_list($valor)) {
            ksort($valor);
        }

        return $valor;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonCanonico(array $payload): string
    {
        return json_encode(
            $this->canonicalizar($payload),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
        ) ?: '{}';
    }

    /**
     * @param  Collection<int, Produccion>  $producciones
     * @return array<string, mixed>
     */
    private function resumenCosecha(Collection $producciones): array
    {
        $unidades = $producciones
            ->map(fn (Produccion $produccion) => $produccion->unidadMedida?->abreviatura ?: $produccion->unidadMedida?->nombre)
            ->filter()
            ->unique()
            ->values();

        $ultimaFecha = $producciones
            ->map(fn (Produccion $produccion) => $produccion->fechacosecha)
            ->filter()
            ->sortDesc()
            ->first();

        return [
            'produccionesRegistradas' => $producciones->count(),
            'cantidadTotal' => $this->numero($producciones->sum(fn (Produccion $produccion) => (float) $produccion->cantidad)),
            'unidad' => $unidades->count() === 1 ? $unidades->first() : ($unidades->isEmpty() ? null : 'mixta'),
            'ultimaFechaCosecha' => $this->fecha($ultimaFecha),
        ];
    }

    private function fechaIso(mixed $fecha): ?string
    {
        if ($fecha === null) {
            return null;
        }

        return $fecha instanceof \DateTimeInterface
            ? $fecha->format(\DateTimeInterface::ATOM)
            : (string) $fecha;
    }

    private function fecha(mixed $fecha): ?string
    {
        if ($fecha === null) {
            return null;
        }

        return $fecha instanceof \DateTimeInterface
            ? $fecha->format('Y-m-d')
            : substr((string) $fecha, 0, 10);
    }

    private function numero(mixed $valor): float|int|null
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $numero = (float) $valor;

        return floor($numero) === $numero ? (int) $numero : $numero;
    }
}
