<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\PedidoDistribucion;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Support\LicenciaConduccionCatalogo;
use App\Support\TiposLicenciaBolivia;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TransporteCapacidadService
{
    /** Densidad estimada agrícola (kg/m³) cuando no hay dimensiones de empaque. */
    public const DENSIDAD_ESTIMADA_KG_M3 = 200.0;

    /**
     * @return array{kg: float, m3: float, licencia_requerida: ?string, tamano: ?string, usa_override: bool}
     */
    public function capacidadEfectiva(Vehiculo $vehiculo): array
    {
        $vehiculo->loadMissing('tipoVehiculo');

        $kgTipo = (float) ($vehiculo->tipoVehiculo?->capacidad_kg ?? 0);
        $m3Tipo = (float) ($vehiculo->tipoVehiculo?->capacidad_m3 ?? 0);

        $kg = $vehiculo->capacidad_kg_override !== null
            ? (float) $vehiculo->capacidad_kg_override
            : $kgTipo;

        $m3 = $vehiculo->capacidad_m3_override !== null
            ? (float) $vehiculo->capacidad_m3_override
            : $m3Tipo;

        return [
            'kg' => max(0, $kg),
            'm3' => max(0, $m3),
            'licencia_requerida' => $vehiculo->tipoVehiculo?->licencia_requerida,
            'tamano' => $vehiculo->tipoVehiculo?->tamano,
            'usa_override' => $vehiculo->capacidad_kg_override !== null || $vehiculo->capacidad_m3_override !== null,
        ];
    }

    public function licenciaTransportista(Usuario $transportista): ?string
    {
        $licencias = $this->licenciasTransportista($transportista);

        return $licencias !== [] ? TiposLicenciaBolivia::licenciaPrincipal($licencias) : null;
    }

    /**
     * @return list<string>
     */
    public function licenciasTransportista(Usuario $transportista): array
    {
        $transportista->loadMissing('perfilTransportista');

        $json = $transportista->licencias_json ?? $transportista->perfilTransportista?->licencias_json;
        if (is_string($json)) {
            $decoded = json_decode($json, true);
            $json = is_array($decoded) ? $decoded : null;
        }

        if (is_array($json) && $json !== []) {
            return TiposLicenciaBolivia::normalizarLista($json);
        }

        $unica = $transportista->tipo_licencia
            ?? $transportista->perfilTransportista?->tipo_licencia;

        return TiposLicenciaBolivia::normalizarLista($unica !== null ? [$unica] : []);
    }

    public function validarAsignacion(Usuario $transportista, Vehiculo $vehiculo): void
    {
        if ($transportista->role !== 'transportista' || ! $transportista->activo) {
            throw new InvalidArgumentException('El usuario seleccionado no es un transportista activo.');
        }

        if (! $vehiculo->activo) {
            throw new InvalidArgumentException('El vehículo seleccionado no está activo.');
        }

        if (! \App\Support\EstadoVehiculoCatalogo::disponibleParaUso($vehiculo)) {
            $mensaje = \App\Support\EstadoVehiculoCatalogo::enMantenimiento($vehiculo)
                ? 'El vehículo '.$vehiculo->placa.' está en mantenimiento y no puede usarse.'
                : 'El vehículo '.$vehiculo->placa.' no está disponible para asignación.';

            throw new InvalidArgumentException($mensaje);
        }

        if (app(\App\Services\VehiculoFlotaEstadoService::class)->estaEnRuta($vehiculo)) {
            throw new InvalidArgumentException(
                'El vehículo '.$vehiculo->placa.' está en ruta en este momento y no puede asignarse a otro encargo.'
            );
        }

        $cap = $this->capacidadEfectiva($vehiculo);
        $licencias = $this->licenciasTransportista($transportista);

        if (! LicenciaConduccionCatalogo::puedeConducirConLicencias($licencias, $cap['licencia_requerida'])) {
            throw new InvalidArgumentException(
                LicenciaConduccionCatalogo::mensajeBloqueoMultiples($licencias, $cap['licencia_requerida'])
            );
        }
    }

    public function validarCarga(Vehiculo $vehiculo, float $pesoKg, ?float $volumenM3 = null): void
    {
        $cap = $this->capacidadEfectiva($vehiculo);
        $pesoKg = max(0, $pesoKg);
        $volumenM3 = $volumenM3 ?? $this->volumenDesdePeso($pesoKg);

        if ($cap['kg'] > 0 && $pesoKg > $cap['kg'] + 0.0001) {
            throw new InvalidArgumentException(
                'La carga ('.number_format($pesoKg, 2).' kg) supera la capacidad del vehículo '
                .$vehiculo->placa.' ('.number_format($cap['kg'], 2).' kg).'
            );
        }

        if ($cap['m3'] > 0 && $volumenM3 > $cap['m3'] + 0.0001) {
            throw new InvalidArgumentException(
                'El volumen estimado ('.number_format($volumenM3, 2).' m³) supera la capacidad del vehículo '
                .$vehiculo->placa.' ('.number_format($cap['m3'], 2).' m³).'
            );
        }
    }

    public function validarAsignacionYCarga(Usuario $transportista, Vehiculo $vehiculo, float $pesoKg, ?float $volumenM3 = null): void
    {
        $this->validarAsignacion($transportista, $vehiculo);
        $this->validarCarga($vehiculo, $pesoKg, $volumenM3);
    }

    public function volumenDesdePeso(float $pesoKg): float
    {
        if ($pesoKg <= 0) {
            return 0.0;
        }

        return $pesoKg / self::DENSIDAD_ESTIMADA_KG_M3;
    }

    public function pesoPedido(Pedido $pedido): float
    {
        $pedido->loadMissing('detalles');

        return (float) $pedido->detalles->sum(fn ($d) => (float) ($d->cantidad ?? 0));
    }

    /**
     * @param  Collection<int, PedidoDistribucion>|array<int, PedidoDistribucion>  $pedidos
     */
    public function pesoPedidosDistribucion(Collection|array $pedidos): float
    {
        $coleccion = $pedidos instanceof Collection ? $pedidos : collect($pedidos);

        return (float) $coleccion->sum(function (PedidoDistribucion $pedido) {
            $pedido->loadMissing('detalles');

            return (float) $pedido->detalles->sum(fn ($d) => (float) ($d->cantidad ?? 0));
        });
    }

    public function resumenCarga(float $pesoKg, ?float $volumenM3 = null): array
    {
        $volumenM3 = $volumenM3 ?? $this->volumenDesdePeso($pesoKg);

        return [
            'peso_kg' => round($pesoKg, 2),
            'volumen_m3' => round($volumenM3, 2),
        ];
    }

    public function etiquetaCapacidad(Vehiculo $vehiculo): string
    {
        $cap = $this->capacidadEfectiva($vehiculo);
        $partes = [];

        if ($cap['kg'] > 0) {
            $partes[] = number_format($cap['kg'], 0).' kg';
        }
        if ($cap['m3'] > 0) {
            $partes[] = number_format($cap['m3'], 1).' m³';
        }

        $texto = $partes !== [] ? implode(' / ', $partes) : 'Sin capacidad';
        $lic = $cap['licencia_requerida'];

        if ($lic) {
            $texto .= ' · Lic. '.$lic;
        }

        return $texto;
    }
}
