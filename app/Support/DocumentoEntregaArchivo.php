<?php

namespace App\Support;

use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;
use App\Support\RutaDistribucionCatalogo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class DocumentoEntregaArchivo
{
    private const PDF_VERSION = 10;

    /** @var array<string, string> */
    private const TIPOS_ETIQUETA = [
        'guia_entrega' => 'Guía de entrega',
        'guia_transporte' => 'Guía de transporte',
        'confirmacion_entrega' => 'Confirmación de entrega',
        'nota_entrega' => 'Nota de entrega',
        'pod' => 'POD / comprobante de entrega',
    ];

    public static function asegurarPdfOperativo(DocumentoEntrega $documento): bool
    {
        $path = trim((string) $documento->archivo_path);
        if ($path === '') {
            return false;
        }

        if (
            ! Storage::disk('public')->exists($path)
            || self::esArchivoPlaceholder($path)
            || ($documento->metadata['pdf_version'] ?? null) !== self::PDF_VERSION
        ) {
            return self::generarPdfOperativo($documento);
        }

        return true;
    }

    public static function materializarSiFalta(DocumentoEntrega $documento): bool
    {
        return self::asegurarPdfOperativo($documento);
    }

    public static function materializarTodosFaltantes(bool $forzarRegeneracion = false): int
    {
        if (! Schema::hasTable('documento_entrega')) {
            return 0;
        }

        $procesados = 0;

        DocumentoEntrega::query()
            ->whereNotNull('archivo_path')
            ->orderBy('documentoentregaid')
            ->chunkById(50, function ($documentos) use (&$procesados, $forzarRegeneracion) {
                foreach ($documentos as $documento) {
                    $ok = $forzarRegeneracion
                        ? self::generarPdfOperativo($documento)
                        : self::asegurarPdfOperativo($documento);

                    if ($ok) {
                        $procesados++;
                    }
                }
            }, 'documentoentregaid');

        return $procesados;
    }

    public static function generarPdfOperativo(DocumentoEntrega $documento): bool
    {
        $path = trim((string) $documento->archivo_path);
        if ($path === '') {
            return false;
        }

        $disk = Storage::disk('public');
        $dir = dirname(str_replace('\\', '/', $path));
        if ($dir !== '.' && $dir !== '') {
            $disk->makeDirectory($dir);
        }

        $contexto = self::contextoPdf($documento);
        $pdf = Pdf::loadView('logistica.documentos.pdf.comprobante', $contexto)
            ->setPaper('a4', 'portrait');

        $guardado = (bool) $disk->put($path, $pdf->output());

        if ($guardado) {
            $metadata = array_merge($documento->metadata ?? [], [
                'pdf_version' => self::PDF_VERSION,
                'pdf_generado_en' => now()->toIso8601String(),
            ]);
            $documento->update(['metadata' => $metadata]);
        }

        return $guardado;
    }

    /** @return array<string, mixed> */
    public static function contextoPdf(DocumentoEntrega $documento): array
    {
        $documento->loadMissing(['usuario', 'pedido.detalles', 'almacen']);

        $envio = null;
        if ($documento->externo_envio_id) {
            $envio = EnvioAsignacionMultiple::query()
                ->with([
                    'transportista',
                    'pedido.detalles',
                    'almacen',
                    'ruta.paradas',
                    'checklistCondicionVehiculo.detalles.condicion',
                    'checklistIncidente.detalles.tipoIncidente',
                    'firmaTransportista',
                    'firmaRecepcion',
                    'llegadaConfirmadaPor',
                ])
                ->where('externo_envio_id', $documento->externo_envio_id)
                ->first();
        }

        $rutaOperacion = null;
        $esRutaPdv = false;
        $rutaId = (int) ($documento->metadata['rutadistribucionid'] ?? 0);
        if ($envio === null && $rutaId > 0) {
            $esTrasladoPlanta = (bool) ($documento->metadata['envio_cierre_planta_mayorista'] ?? false);
            $esRutaPdv = (bool) ($documento->metadata['envio_cierre_mayorista_pdv'] ?? false);
            if ($esTrasladoPlanta || $esRutaPdv) {
                $with = [
                    'transportista',
                    'vehiculo.tipoVehiculo',
                    'paradas',
                    'checklistCondicionVehiculo.detalles.condicion',
                    'checklistIncidente.detalles.tipoIncidente',
                    'firmaTransportista',
                    'firmaRecepcion',
                    'llegadaConfirmadaPor',
                ];
                if ($esRutaPdv) {
                    $with = array_merge($with, [
                        'pedidos.detalles.insumo.unidadMedida',
                        'pedidos.detalles.presentacion',
                        'pedidos.puntoVenta',
                        'almacenOrigen',
                    ]);
                } else {
                    $with = array_merge($with, [
                        'almacenPlantaOrigen',
                        'almacenMayoristaDestino',
                        'detallesTraslado.insumo.unidadMedida',
                    ]);
                }
                $rutaOperacion = RutaDistribucion::query()->with($with)->find($rutaId);
            }
        }

        $pedido = $documento->pedido ?? $envio?->pedido;
        if ($pedido === null && $rutaOperacion !== null) {
            $pedido = $rutaOperacion->pedidos->first();
        }
        if ($esRutaPdv && $rutaOperacion !== null) {
            $pedido = $rutaOperacion->pedidos->first() ?? $pedido;
            $pedido?->loadMissing(['detalles.insumo.unidadMedida', 'detalles.presentacion.tipoEmpaque']);
        }
        $pedidoReferencia = $pedido?->numero_solicitud
            ?? ($documento->pedidoid ? '#'.$documento->pedidoid : null)
            ?? $rutaOperacion?->codigo
            ?? $documento->externo_envio_id;
        $vehiculoRef = $envio?->vehiculo_ref
            ?? $rutaOperacion?->vehiculo?->placa
            ?? null;
        $estadoVehiculo = EnvioCierreAgricolaCatalogo::etiquetaEstadoVehiculo(
            $envio?->checklistCondicionVehiculo?->estado_general
                ?? $rutaOperacion?->checklistCondicionVehiculo?->estado_general
        );
        $transportista = $envio?->transportista ?? $rutaOperacion?->transportista ?? $documento->usuario;

        $destinoCliente = '—';
        $direccionEntrega = '—';
        $firmaRecepcionEtiqueta = 'Firma recepción en destino';
        if ($pedido !== null && ! $esRutaPdv) {
            $destinoCliente = EnvioPedidoService::etiquetaPlantaDestinoLista($pedido)
                ?? EnvioPedidoService::etiquetaPlantaDestinoPedido($pedido)
                ?? '—';
            $direccionEntrega = trim((string) ($pedido->direccion_texto ?? ''));
            if ($direccionEntrega === '' && $destinoCliente !== '—') {
                $direccionEntrega = $destinoCliente;
            }
            if ($direccionEntrega === '') {
                $direccionEntrega = '—';
            }
            $firmaRecepcionEtiqueta = 'Firma recepción en planta';
        } elseif ($esRutaPdv && $rutaOperacion !== null) {
            $pdv = $pedido?->puntoVenta ?? $rutaOperacion->pedidos->first()?->puntoVenta;
            $paradaPdv = $rutaOperacion->paradas?->firstWhere('tipo', RutaDistribucionCatalogo::PARADA_ENTREGA_PDV);
            $destinoCliente = $pdv?->nombre
                ?? ($paradaPdv ? str_replace('Entrega: ', '', (string) $paradaPdv->destino) : null)
                ?? '—';
            $direccionEntrega = trim((string) ($pdv?->direccion ?? ''));
            if ($direccionEntrega === '' && $destinoCliente !== '—') {
                $direccionEntrega = $destinoCliente;
            }
            if ($direccionEntrega === '') {
                $direccionEntrega = '—';
            }
            $firmaRecepcionEtiqueta = 'Firma recepción en punto de venta';
        } elseif ($rutaOperacion !== null && ! $esRutaPdv) {
            $destinoCliente = \App\Support\TrasladoPlantaMayoristaPresentacion::nombreDestinoMayorista($rutaOperacion) ?? '—';
            $direccionEntrega = trim((string) ($rutaOperacion->almacenMayoristaDestino?->ubicacion ?? ''));
            if ($direccionEntrega === '' && $destinoCliente !== '—') {
                $direccionEntrega = $destinoCliente;
            }
            if ($direccionEntrega === '') {
                $direccionEntrega = '—';
            }
            $firmaRecepcionEtiqueta = 'Firma recepción en almacén mayorista';
        }

        $cargadoPor = DocumentoEntregaCatalogo::etiquetaUsuario($documento->usuario);

        $almacenOrigenNombre = $documento->almacen?->nombre
            ?? $envio?->almacen?->nombre
            ?? $rutaOperacion?->almacenOrigen?->nombre
            ?? $rutaOperacion?->almacenPlantaOrigen?->nombre
            ?? '—';

        $lineasProducto = [];
        if ($esRutaPdv && $rutaOperacion !== null) {
            $todosDetalles = collect();
            foreach ($rutaOperacion->pedidos as $pedidoRuta) {
                $todosDetalles = $todosDetalles->concat($pedidoRuta->detalles);
            }
            foreach (\App\Support\PedidoDistribucionConsolidacion::consolidar($todosDetalles) as $grupo) {
                $titulo = $grupo['producto'];
                if ($grupo['lote'] !== '') {
                    $titulo .= ' - '.$grupo['lote'];
                }
                $lineasProducto[] = [
                    'producto' => $titulo,
                    'cantidad' => number_format($grupo['cantidad'], 0, '.', ',').' '.$grupo['unidad']
                        .($grupo['cantidad_kg'] > 0 ? ' ('.number_format($grupo['cantidad_kg'], 2, '.', '').' kg)' : ''),
                    'empaquetaje' => $grupo['empaque'] ?: '—',
                    'observaciones' => '—',
                ];
            }
        } else {
            foreach ($pedido?->detalles ?? [] as $detalle) {
                $empaque = PedidoCatalogo::descripcionEmpaqueDetalle($detalle->observaciones);
                $obsUsuario = PedidoCatalogo::observacionesUsuarioDetalle($detalle->observaciones);
                $nombreProducto = $detalle->cultivo_personalizado
                    ?? $detalle->producto_nombre
                    ?? $detalle->insumo?->nombre
                    ?? 'Producto';
                $unidad = $detalle->insumo?->unidadMedida?->abreviatura ?? 'kg';

                $lineasProducto[] = [
                    'producto' => $nombreProducto,
                    'cantidad' => number_format((float) $detalle->cantidad, 2, '.', '').' '.$unidad,
                    'empaquetaje' => $empaque ?? ($detalle->presentacion?->nombre ?? '—'),
                    'observaciones' => $obsUsuario ?: '—',
                ];
            }
        }

        if ($lineasProducto === [] && $rutaOperacion !== null && ! $esRutaPdv) {
            $snap = $documento->metadata['lineas_producto'] ?? null;
            if (is_array($snap) && $snap !== []) {
                foreach ($snap as $linea) {
                    $lineasProducto[] = [
                        'producto' => $linea['producto'] ?? 'Producto',
                        'cantidad' => self::formatearCantidadLineaTraslado($linea),
                        'empaquetaje' => $linea['empaquetaje'] ?? $linea['presentacion'] ?? '—',
                        'observaciones' => $linea['observaciones'] ?? '—',
                    ];
                }
            }
        }

        if ($lineasProducto === [] && $rutaOperacion !== null && ! $esRutaPdv) {
            foreach ($rutaOperacion->detallesTraslado as $detalle) {
                $lineasProducto[] = [
                    'producto' => $detalle->producto_nombre ?? $detalle->insumo?->nombre ?? 'Producto',
                    'cantidad' => self::formatearCantidadLineaTraslado([
                        'cantidad_unidades' => $detalle->cantidad_unidades,
                        'cantidad' => $detalle->cantidad,
                        'unidad' => $detalle->insumo?->unidadMedida?->abreviatura ?? 'kg',
                    ]),
                    'empaquetaje' => $detalle->presentacion_nombre ?: '—',
                    'observaciones' => $detalle->observaciones ?: '—',
                ];
            }
        }

        $tipo = (string) $documento->tipo_documento;
        $tipoEtiqueta = self::TIPOS_ETIQUETA[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));

        $textoObservaciones = match ($tipo) {
            'guia_entrega', 'guia_transporte' => 'Documento de salida y trazabilidad del envío hacia el punto de entrega indicado.',
            'confirmacion_entrega', 'pod' => 'Comprobante de recepción conforme en destino.',
            'nota_entrega' => 'Registro de recepción en almacén o punto de despacho.',
            default => 'Comprobante logístico asociado al envío.',
        };

        if ($envio?->estado === 'entregado') {
            $textoObservaciones .= ' Envío marcado como entregado en el sistema.';
        } elseif ($rutaOperacion?->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA) {
            $textoObservaciones .= $esRutaPdv
                ? ' Entrega completada en el punto de venta.'
                : ' Traslado completado en el sistema.';
        }

        $operacionChecklistCondicion = $envio?->checklistCondicionVehiculo ?? $rutaOperacion?->checklistCondicionVehiculo;
        $operacionChecklistIncidente = $envio?->checklistIncidente ?? $rutaOperacion?->checklistIncidente;
        $operacionFirmaTransportista = $envio?->firmaTransportista ?? $rutaOperacion?->firmaTransportista;
        $operacionFirmaRecepcion = $envio?->firmaRecepcion ?? $rutaOperacion?->firmaRecepcion;
        $operacionLlegadaAt = $envio?->llegada_confirmada_at ?? $rutaOperacion?->llegada_confirmada_at;

        $transportistaNombre = trim((string) ($operacionFirmaTransportista?->nombrefirmante ?? ''));
        if ($transportistaNombre === '') {
            $transportistaNombre = DocumentoEntregaCatalogo::etiquetaUsuario($transportista);
        }

        $recepcionNombre = trim((string) ($operacionFirmaRecepcion?->nombrefirmante ?? ''));

        $condicionesLineas = [];
        foreach ($operacionChecklistCondicion?->detalles ?? [] as $det) {
            $condicionesLineas[] = [
                'titulo' => $det->condicion?->titulo ?? 'Condición',
                'valor' => $det->valor ? 'Sí' : 'No',
            ];
        }

        $observacionCondiciones = self::resolverObservacionCondiciones(
            $condicionesLineas,
            $operacionChecklistCondicion?->observaciones
        );

        $incidentesLineas = [];
        foreach ($operacionChecklistIncidente?->detalles ?? [] as $det) {
            $incidentesLineas[] = [
                'titulo' => $det->tipoIncidente?->titulo ?? 'Incidente',
                'ocurrio' => $det->ocurrio ? 'Sí' : 'No',
            ];
        }

        $observacionIncidentes = IncidenteTransporteCatalogo::resolverObservacion(
            $incidentesLineas,
            $operacionChecklistIncidente?->observaciones
        );

        $rutaLogistica = '—';
        if ($envio !== null) {
            $nombreRuta = trim((string) ($envio->ruta?->nombre ?? ''));
            $trayecto = EnvioPedidoService::trayectoTexto($envio);
            $rutaLogistica = $nombreRuta !== '' ? $nombreRuta : ($trayecto ?? '—');
            if ($rutaLogistica === '' || $rutaLogistica === null) {
                $rutaLogistica = '—';
            }
        } elseif ($rutaOperacion !== null) {
            if ($esRutaPdv) {
                $origen = $rutaOperacion->almacenOrigen?->nombre ?? 'Centro mayorista';
                $destino = $destinoCliente !== '—' ? $destinoCliente : 'Punto de venta';
                $rutaLogistica = $origen.' → '.$destino;
            } else {
                $origen = $rutaOperacion->almacenPlantaOrigen?->nombre ?? 'Planta';
                $destino = \App\Support\TrasladoPlantaMayoristaPresentacion::nombreDestinoMayorista($rutaOperacion) ?? 'Mayorista';
                $rutaLogistica = $origen.' → '.$destino;
            }
        }

        $estadoEnvioRaw = $envio?->estado ?? $rutaOperacion?->estado ?? 'pendiente';
        $estadoEnvio = $estadoEnvioRaw === RutaDistribucionCatalogo::ESTADO_COMPLETADA
            ? 'Completada'
            : ucfirst(str_replace('_', ' ', (string) $estadoEnvioRaw));

        return [
            'documento' => $documento,
            'envio' => $envio,
            'pedido' => $pedido,
            'pedidoReferencia' => $pedidoReferencia,
            'vehiculoRef' => $vehiculoRef,
            'estadoVehiculo' => $estadoVehiculo,
            'tipoEtiqueta' => $tipoEtiqueta,
            'transportistaNombre' => $transportistaNombre,
            'recepcionNombre' => $recepcionNombre !== '' ? $recepcionNombre : ($destinoCliente !== '—' ? $destinoCliente : null),
            'destinoCliente' => $destinoCliente,
            'direccionEntrega' => $direccionEntrega,
            'cargadoPor' => $cargadoPor,
            'almacenOrigenNombre' => $almacenOrigenNombre,
            'rutaLogistica' => $rutaLogistica,
            'estadoEnvio' => $estadoEnvio,
            'lineasProducto' => $lineasProducto,
            'textoObservaciones' => $textoObservaciones,
            'condicionesLineas' => $condicionesLineas,
            'observacionCondiciones' => $observacionCondiciones,
            'incidentesLineas' => $incidentesLineas,
            'observacionIncidentes' => $observacionIncidentes,
            'observacionesIncidentes' => $operacionChecklistIncidente?->observaciones,
            'observacionPersonalCondiciones' => self::extraerObservacionManual($operacionChecklistCondicion?->observaciones),
            'observacionPersonalIncidentes' => self::extraerObservacionManual($operacionChecklistIncidente?->observaciones),
            'firmaTransportistaImg' => $operacionFirmaTransportista?->imagenfirma,
            'firmaRecepcionImg' => $operacionFirmaRecepcion?->imagenfirma,
            'firmaRecepcionEtiqueta' => $firmaRecepcionEtiqueta,
            'llegadaConfirmadaAt' => $operacionLlegadaAt,
        ];
    }

    /**
     * @param  array<int, array{titulo: string, valor: string}>  $condicionesLineas
     * @return array{texto: ?string, alerta: bool}
     */
    private static function resolverObservacionCondiciones(array $condicionesLineas, ?string $observacionesGuardadas): array
    {
        $deficiencias = array_values(array_filter(
            $condicionesLineas,
            static fn (array $fila): bool => ($fila['valor'] ?? '') === 'No'
        ));

        if ($deficiencias !== []) {
            $titulos = array_map(static fn (array $fila): string => $fila['titulo'], $deficiencias);

            return [
                'texto' => 'Deficiencias detectadas: '.implode(', ', $titulos).'.',
                'alerta' => true,
            ];
        }

        $texto = trim((string) ($observacionesGuardadas ?? ''));
        if ($texto !== '') {
            $texto = preg_replace('/\s*\(registro\s+r[aá]pido\)\.?/iu', '', $texto) ?? $texto;
            $texto = trim($texto);
            if ($texto !== '' && ! str_ends_with($texto, '.')) {
                $texto .= '.';
            }

            return ['texto' => $texto !== '' ? $texto : null, 'alerta' => false];
        }

        if ($condicionesLineas !== []) {
            return ['texto' => 'Vehículo en perfectas condiciones.', 'alerta' => false];
        }

        return ['texto' => null, 'alerta' => false];
    }

    /** @return list<string> */
    private static function textosObservacionSistema(): array
    {
        return [
            'vehículo en perfectas condiciones.',
            'vehiculo en perfectas condiciones.',
            'vehículo en condiciones óptimas.',
            'vehiculo en condiciones optimas.',
            'transporte sin incidentes reportados.',
        ];
    }

    private static function esObservacionManual(?string $texto): bool
    {
        $limpio = trim((string) $texto);
        if ($limpio === '') {
            return false;
        }

        $limpio = preg_replace('/\s*\(registro\s+r[aá]pido\)\.?/iu', '', $limpio) ?? $limpio;
        $limpio = trim($limpio);
        if ($limpio === '') {
            return false;
        }

        $normalizado = mb_strtolower($limpio);

        foreach (self::textosObservacionSistema() as $defecto) {
            if ($normalizado === $defecto || str_starts_with($normalizado, $defecto)) {
                return false;
            }
        }

        if (str_starts_with($normalizado, 'deficiencias detectadas:')) {
            return false;
        }

        if (str_starts_with($normalizado, 'incidentes reportados:')) {
            return false;
        }

        return true;
    }

    private static function extraerObservacionManual(?string $texto): ?string
    {
        if (! self::esObservacionManual($texto)) {
            return null;
        }

        $limpio = trim((string) $texto);
        $limpio = preg_replace('/\s*\(registro\s+r[aá]pido\)\.?/iu', '', $limpio) ?? $limpio;
        $limpio = trim($limpio);

        return $limpio !== '' ? $limpio : null;
    }

    private static function esArchivoPlaceholder(string $path): bool
    {
        if (! Storage::disk('public')->exists($path)) {
            return false;
        }

        $contenido = Storage::disk('public')->get($path);

        return strlen($contenido) < 2000
            && str_contains($contenido, 'Documento generado por AgroFusion');
    }

    /** @return array{producto: string, cantidad: string, empaquetaje: string, observaciones: string} */
    private static function lineaProductoDesdeDetallePdv(\App\Models\DetallePedidoDistribucion $detalle): array
    {
        $empaque = PedidoCatalogo::descripcionEmpaqueDetalle($detalle->observaciones);
        if ($empaque === null && $detalle->presentacion) {
            $pres = $detalle->presentacion;
            $partes = array_filter([
                trim((string) ($pres->nombre ?? '')),
                trim((string) ($pres->tipoEmpaque?->nombre ?? '')),
            ]);
            $empaque = $partes !== [] ? implode(' · ', $partes) : null;
        }

        $nombreProducto = $detalle->insumo?->nombre
            ?? $detalle->producto_nombre
            ?? 'Producto';
        if (str_contains($nombreProducto, ' · ')) {
            [$nombreProducto] = explode(' · ', $nombreProducto, 2);
        }

        $unidad = $detalle->presentacion?->etiquetaUnidad()
            ?? $detalle->insumo?->unidadMedida?->abreviatura
            ?? 'un';

        if ($empaque === null && str_contains((string) ($detalle->producto_nombre ?? ''), ' · ')) {
            [, $empaque] = explode(' · ', (string) $detalle->producto_nombre, 2);
        }

        $obsUsuario = PedidoCatalogo::observacionesUsuarioDetalle($detalle->observaciones);

        return [
            'producto' => trim($nombreProducto),
            'cantidad' => self::formatearCantidadLineaTraslado([
                'cantidad_unidades' => $detalle->cantidad_unidades ?? null,
                'cantidad' => $detalle->cantidad,
                'unidad' => $unidad,
            ]),
            'empaquetaje' => $empaque ?? '—',
            'observaciones' => $obsUsuario ?: '—',
        ];
    }

    /** @param  array<string, mixed>  $linea */
    private static function formatearCantidadLineaTraslado(array $linea): string
    {
        $unidades = isset($linea['cantidad_unidades']) ? (float) $linea['cantidad_unidades'] : 0.0;
        $kg = isset($linea['cantidad']) ? (float) $linea['cantidad'] : 0.0;
        $unidad = (string) ($linea['unidad'] ?? 'kg');

        if ($unidades > 0) {
            $texto = number_format($unidades, 0, '.', ',').' u.';
            if ($kg > 0) {
                $texto .= ' ('.number_format($kg, 2, '.', '').' kg)';
            }

            return $texto;
        }

        if ($kg > 0) {
            return number_format($kg, 2, '.', '').' '.$unidad;
        }

        return '—';
    }
}
