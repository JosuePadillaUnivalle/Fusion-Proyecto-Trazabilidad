<?php

namespace App\Services;

use App\Models\ChecklistCondicionLogistica;
use App\Models\ChecklistCondicionLogisticaDetalle;
use App\Models\ChecklistIncidenteEnvio;
use App\Models\ChecklistIncidenteEnvioDetalle;
use App\Models\CondicionTransporte;
use App\Models\DocumentoEntrega;
use App\Models\FirmaRecepcionEnvio;
use App\Models\FirmaTransportistaEnvio;
use App\Models\PedidoDistribucion;
use App\Models\RutaDistribucion;
use App\Models\TipoIncidenteTransporte;
use App\Models\Usuario;
use App\Support\DocumentoEntregaArchivo;
use App\Support\EnvioCierreAgricolaCatalogo;
use App\Support\MayoristaAccess;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\PuntoVentaAccess;
use App\Support\RutaDistribucionCatalogo;
use App\Support\SimulacionRutaCatalogo;
use App\Support\UsuarioRol;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CierreEnvioDistribucionPdvService
{
    public function __construct(
        private readonly SimulacionRutaService $simulacion,
        private readonly RecepcionPuntoVentaService $recepcionPdv,
    ) {}

    public function tieneCondicionesVehiculo(RutaDistribucion $ruta): bool
    {
        return ChecklistCondicionLogistica::query()
            ->where('rutadistribucionid', $ruta->rutadistribucionid)
            ->whereIn('estado_general', [
                EnvioCierreAgricolaCatalogo::ESTADO_VEHICULO_PERFECTO,
                EnvioCierreAgricolaCatalogo::ESTADO_VEHICULO_REVISADO,
            ])
            ->exists();
    }

    public function documentoEntrega(RutaDistribucion $ruta): ?DocumentoEntrega
    {
        return DocumentoEntrega::query()
            ->where('metadata->rutadistribucionid', $ruta->rutadistribucionid)
            ->where('tipo_documento', 'guia_transporte')
            ->where('metadata->envio_cierre_mayorista_pdv', true)
            ->orderByDesc('documentoentregaid')
            ->first();
    }

    /** @return array<string, mixed> */
    public function resumenPasos(RutaDistribucion $ruta): array
    {
        $this->validarRutaPdv($ruta);

        $ruta->loadMissing([
            'checklistCondicionVehiculo.detalles.condicion',
            'checklistIncidente.detalles.tipoIncidente',
            'firmaTransportista',
            'firmaRecepcion',
        ]);

        $estadoSim = $this->simulacion->estadoDistribucion($ruta, false);
        $progreso = (float) ($estadoSim['progreso'] ?? 0);
        $enRuta = SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta);
        $llegadaConfirmada = $ruta->llegada_confirmada_at !== null;
        $recibido = $ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA;
        $tieneCondiciones = $this->tieneCondicionesVehiculo($ruta);
        $tieneIncidentes = $ruta->checklistIncidente !== null;
        $firmaTransportista = $ruta->firmaTransportista !== null;
        $firmaRecepcion = $ruta->firmaRecepcion !== null;
        $pendienteConfirmacionMinorista = ! SimulacionRutaCatalogo::pedidosListosParaSalida($ruta);
        $condicionesVigentes = $tieneCondiciones && ! $pendienteConfirmacionMinorista;

        $pasoActual = EnvioCierreAgricolaCatalogo::PASO_CONDICIONES;
        if ($recibido) {
            $pasoActual = EnvioCierreAgricolaCatalogo::PASO_COMPLETADO;
        } elseif ($pendienteConfirmacionMinorista) {
            $pasoActual = '__espera_confirmacion_minorista__';
        } elseif ($firmaTransportista && $firmaRecepcion) {
            $pasoActual = EnvioCierreAgricolaCatalogo::PASO_FIRMA_RECEPCION;
        } elseif ($firmaTransportista) {
            $pasoActual = EnvioCierreAgricolaCatalogo::PASO_FIRMA_RECEPCION;
        } elseif ($tieneIncidentes && $llegadaConfirmada) {
            $pasoActual = EnvioCierreAgricolaCatalogo::PASO_FIRMA_TRANSPORTISTA;
        } elseif ($llegadaConfirmada) {
            $pasoActual = EnvioCierreAgricolaCatalogo::PASO_INCIDENTES;
        } elseif ($enRuta) {
            $pasoActual = EnvioCierreAgricolaCatalogo::PASO_ESPERA_LLEGADA;
        } elseif ($condicionesVigentes) {
            $pasoActual = EnvioCierreAgricolaCatalogo::PASO_EN_RUTA;
        }

        return app(RecepcionQrFirmaService::class)->enriquecerResumen([
            'paso_actual' => $pasoActual,
            'tiene_condiciones' => $tieneCondiciones,
            'condiciones_vigentes' => $condicionesVigentes,
            'en_ruta' => $enRuta,
            'progreso' => $progreso,
            'esperando_confirmacion' => ! $recibido && ! $llegadaConfirmada && $progreso >= 100,
            'llegada_confirmada' => $llegadaConfirmada,
            'tiene_incidentes' => $tieneIncidentes,
            'firma_transportista' => $firmaTransportista,
            'firma_recepcion' => $firmaRecepcion,
            'recibido_pdv' => $recibido,
            'recibido_planta' => $recibido,
            'pendiente_confirmacion_minorista' => $pendienteConfirmacionMinorista,
            'puede_registrar_condiciones' => ! $pendienteConfirmacionMinorista
                && ! $tieneCondiciones && ! $enRuta && ! $recibido
                && $ruta->estado === RutaDistribucionCatalogo::ESTADO_PLANIFICADA
                && $ruta->transportista_usuarioid !== null,
            'puede_empezar_ruta' => SimulacionRutaCatalogo::puedeEmpezarDistribucion($ruta) && $tieneCondiciones,
            'puede_confirmar_llegada' => $enRuta && ! $llegadaConfirmada && ! $recibido && $progreso >= 100,
            'puede_registrar_incidentes' => $llegadaConfirmada && ! $tieneIncidentes && ! $recibido,
            'puede_firmar_transportista' => $llegadaConfirmada && $tieneIncidentes && ! $recibido && ! $firmaTransportista,
            'puede_firmar_recepcion' => $llegadaConfirmada && $tieneIncidentes && ! $recibido
                && $firmaTransportista && ! $firmaRecepcion,
            'puede_finalizar' => $llegadaConfirmada && $tieneIncidentes && $firmaTransportista && $firmaRecepcion && ! $recibido,
        ], $ruta);
    }

    /**
     * @param  array<int, array{id: int, valor: bool}>|null  $condiciones
     */
    public function registrarCondicionesVehiculo(
        RutaDistribucion $ruta,
        Usuario $usuario,
        bool $perfectasCondiciones,
        ?array $condiciones = null,
        ?string $observaciones = null,
    ): ChecklistCondicionLogistica {
        $this->validarRutaPdv($ruta);

        if ($this->tieneCondicionesVehiculo($ruta)) {
            throw new InvalidArgumentException('Las condiciones del vehículo ya fueron registradas.');
        }

        if (SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            throw new InvalidArgumentException('No puede modificar condiciones con la ruta en curso.');
        }

        if ($ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA) {
            throw new InvalidArgumentException('Esta entrega ya fue completada.');
        }

        if (! SimulacionRutaCatalogo::pedidosListosParaSalida($ruta)) {
            throw new InvalidArgumentException('El minorista del punto de venta debe confirmar el envío antes de registrar las condiciones del vehículo.');
        }

        $catalogo = CondicionTransporte::query()->orderBy('condiciontransporteid')->get();
        if ($catalogo->isEmpty()) {
            throw new InvalidArgumentException('No hay condiciones de transporte configuradas en el catálogo.');
        }

        return DB::transaction(function () use ($ruta, $usuario, $perfectasCondiciones, $condiciones, $observaciones, $catalogo) {
            $checklist = ChecklistCondicionLogistica::create([
                'rutadistribucionid' => $ruta->rutadistribucionid,
                'almacenid' => $ruta->almacen_mayorista_origenid,
                'revisado_por_usuarioid' => $usuario->usuarioid,
                'estado_general' => $perfectasCondiciones
                    ? EnvioCierreAgricolaCatalogo::ESTADO_VEHICULO_PERFECTO
                    : EnvioCierreAgricolaCatalogo::ESTADO_VEHICULO_REVISADO,
                'observaciones' => $observaciones ?? ($perfectasCondiciones
                    ? 'Vehículo en perfectas condiciones.'
                    : null),
                'fecha_revision' => now(),
                'created_at' => now(),
            ]);

            $mapaManual = collect($condiciones ?? [])->keyBy('id');

            foreach ($catalogo as $condicion) {
                $valor = $perfectasCondiciones
                    ? true
                    : $this->valorBooleano($mapaManual->get($condicion->condiciontransporteid)['valor'] ?? false);

                ChecklistCondicionLogisticaDetalle::create([
                    'checklistcondicionid' => $checklist->checklistcondicionid,
                    'condiciontransporteid' => $condicion->condiciontransporteid,
                    'valor' => $valor,
                    'comentario' => null,
                ]);
            }

            return $checklist->load('detalles.condicion');
        });
    }

    public function confirmarLlegada(RutaDistribucion $ruta, Usuario $usuario): void
    {
        $this->autorizarConfirmacionLlegada($usuario, $ruta);
        $this->validarRutaPdv($ruta);

        if ($ruta->llegada_confirmada_at) {
            throw new InvalidArgumentException('La llegada ya fue confirmada.');
        }

        if ($ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA) {
            throw new InvalidArgumentException('Esta entrega ya fue completada.');
        }

        if (! SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            throw new InvalidArgumentException('La ruta debe estar en camino para confirmar la llegada.');
        }

        $estado = $this->simulacion->estadoDistribucion($ruta, false);
        if ((float) ($estado['progreso'] ?? 0) < 100) {
            throw new InvalidArgumentException('Primero debe llegar al destino. Espere a que el recorrido GPS llegue al 100% antes de confirmar la llegada.');
        }

        $ruta->update([
            'llegada_confirmada_at' => now(),
            'llegada_confirmada_usuarioid' => $usuario->usuarioid,
        ]);
    }

    /**
     * @param  array<int, array{id: int, ocurrio: bool}>|null  $incidentes
     */
    public function registrarIncidentes(
        RutaDistribucion $ruta,
        Usuario $usuario,
        bool $sinIncidentes,
        ?array $incidentes = null,
        ?string $observaciones = null,
    ): ChecklistIncidenteEnvio {
        $this->autorizarIncidentes($usuario, $ruta);
        $this->validarRutaPdv($ruta);

        if ($ruta->llegada_confirmada_at === null) {
            throw new InvalidArgumentException('Debe confirmar la llegada antes de registrar incidentes.');
        }

        if ($ruta->checklistIncidente()->exists()) {
            throw new InvalidArgumentException('Los incidentes ya fueron registrados para esta entrega.');
        }

        $catalogo = TipoIncidenteTransporte::query()->orderBy('tipoincidentetransporteid')->get();
        if ($catalogo->isEmpty()) {
            throw new InvalidArgumentException('No hay tipos de incidente configurados en el catálogo.');
        }

        return DB::transaction(function () use ($ruta, $sinIncidentes, $incidentes, $observaciones, $catalogo) {
            $checklist = ChecklistIncidenteEnvio::create([
                'rutadistribucionid' => $ruta->rutadistribucionid,
                'fecha' => now(),
                'observaciones' => $observaciones ?? ($sinIncidentes
                    ? 'Transporte sin incidentes reportados.'
                    : null),
            ]);

            $mapaManual = collect($incidentes ?? [])->keyBy('id');

            foreach ($catalogo as $tipo) {
                $ocurrio = $sinIncidentes
                    ? false
                    : $this->valorBooleano($mapaManual->get($tipo->tipoincidentetransporteid)['ocurrio'] ?? false);

                ChecklistIncidenteEnvioDetalle::create([
                    'checklistincidenteenvioid' => $checklist->checklistincidenteenvioid,
                    'tipoincidentetransporteid' => $tipo->tipoincidentetransporteid,
                    'ocurrio' => $ocurrio,
                    'descripcion' => null,
                ]);
            }

            return $checklist->load('detalles.tipoIncidente');
        });
    }

    public function guardarFirmaTransportista(RutaDistribucion $ruta, Usuario $usuario, string $imagenBase64): FirmaTransportistaEnvio
    {
        $this->autorizarFirmaTransportista($usuario, $ruta);
        $this->validarPreFirmas($ruta);

        if ($ruta->firmaTransportista()->exists()) {
            throw new InvalidArgumentException('La firma del transportista ya fue registrada.');
        }

        $firma = FirmaTransportistaEnvio::create([
            'rutadistribucionid' => $ruta->rutadistribucionid,
            'imagenfirma' => $this->normalizarImagenFirma($imagenBase64),
            'nombrefirmante' => RecepcionQrFirmaService::nombreDesdeUsuario($usuario),
            'fechafirma' => now(),
        ]);

        app(RecepcionQrFirmaService::class)->ensureToken($ruta);

        app(NotificacionUsuarioService::class)->distribucionPdvPendienteFirmaMinorista(
            $ruta->fresh(['pedidos.puntoVenta', 'transportista'])
        );

        return $firma;
    }

    public function guardarFirmaRecepcion(RutaDistribucion $ruta, Usuario $usuario, string $imagenBase64): FirmaRecepcionEnvio
    {
        $this->autorizarFirmaRecepcion($usuario, $ruta);
        $this->validarPreFirmas($ruta);

        if ($ruta->firmaRecepcion()->exists()) {
            throw new InvalidArgumentException('La firma de recepción ya fue registrada.');
        }

        return FirmaRecepcionEnvio::create([
            'rutadistribucionid' => $ruta->rutadistribucionid,
            'imagenfirma' => $this->normalizarImagenFirma($imagenBase64),
            'fechafirma' => now(),
        ]);
    }

    public function finalizarEntrega(RutaDistribucion $ruta, Usuario $usuario): DocumentoEntrega
    {
        $this->autorizarFinalizar($usuario, $ruta);
        $resumen = $this->resumenPasos($ruta);

        if (! ($resumen['puede_finalizar'] ?? false)) {
            throw new InvalidArgumentException('Complete condiciones, llegada, incidentes y firmas antes de finalizar.');
        }

        return DB::transaction(function () use ($ruta, $usuario) {
            $ruta->loadMissing('pedidos');
            foreach ($ruta->pedidos as $pedido) {
                if (PedidoDistribucionCatalogo::puedeConfirmarRecepcion($pedido)) {
                    $this->recepcionPdv->confirmar($pedido, $usuario);
                }
            }

            $ruta->update([
                'estado' => RutaDistribucionCatalogo::ESTADO_COMPLETADA,
            ]);

            $ruta->refresh();
            $documento = $this->generarDocumentoTransporte($ruta, $usuario);
            app(NotificacionUsuarioService::class)->distribucionPdvRecibidaEnTienda(
                $ruta->fresh(['pedidos.puntoVenta', 'transportista'])
            );

            return $documento;
        });
    }

    public function autorizarConfirmacionLlegada(Usuario $usuario, RutaDistribucion $ruta): void
    {
        if ($this->esTransportistaAsignado($usuario, $ruta) || $this->esAdminOperativo($usuario)) {
            return;
        }

        throw new InvalidArgumentException('No tiene permiso para confirmar la llegada de esta entrega.');
    }

    public function autorizarIncidentes(Usuario $usuario, RutaDistribucion $ruta): void
    {
        if ($this->esTransportistaAsignado($usuario, $ruta) || $this->esAdminOperativo($usuario)) {
            return;
        }

        throw new InvalidArgumentException('No tiene permiso para registrar incidentes en esta entrega.');
    }

    private function autorizarFirmaTransportista(Usuario $usuario, RutaDistribucion $ruta): void
    {
        if (! $this->esTransportistaAsignado($usuario, $ruta) && ! $this->esAdminOperativo($usuario)) {
            throw new InvalidArgumentException('Solo el transportista asignado puede firmar como transportista.');
        }
    }

    private function autorizarFirmaRecepcion(Usuario $usuario, RutaDistribucion $ruta): void
    {
        if (
            $this->esAdminOperativo($usuario)
            || $this->esTransportistaAsignado($usuario, $ruta)
            || PuntoVentaAccess::puedeFirmarRecepcionRuta($usuario, $ruta)
        ) {
            return;
        }

        throw new InvalidArgumentException('No tiene permiso para firmar la recepción en el punto de venta.');
    }

    private function autorizarFinalizar(Usuario $usuario, RutaDistribucion $ruta): void
    {
        if ($this->esTransportistaAsignado($usuario, $ruta)
            || $this->esAdminOperativo($usuario)
            || PuntoVentaAccess::puedeFirmarRecepcionRuta($usuario, $ruta)
            || MayoristaAccess::puedeGestionarRutaDistribucion($usuario, $ruta)) {
            return;
        }

        throw new InvalidArgumentException('No tiene permiso para finalizar esta entrega.');
    }

    private function validarPreFirmas(RutaDistribucion $ruta): void
    {
        if ($ruta->llegada_confirmada_at === null) {
            throw new InvalidArgumentException('Debe confirmar la llegada antes de las firmas.');
        }

        if (! $ruta->checklistIncidente()->exists()) {
            throw new InvalidArgumentException('Debe registrar incidentes (o «Sin incidentes») antes de las firmas.');
        }
    }

    private function validarRutaPdv(RutaDistribucion $ruta): void
    {
        if ($ruta->esTrasladoPlantaMayorista()) {
            throw new InvalidArgumentException('La ruta no es una distribución mayorista → punto de venta.');
        }
    }

    private function esTransportistaAsignado(Usuario $usuario, RutaDistribucion $ruta): bool
    {
        return (int) $ruta->transportista_usuarioid === (int) $usuario->usuarioid;
    }

    private function normalizarImagenFirma(string $imagen): string
    {
        $imagen = trim($imagen);
        if ($imagen === '' || ! str_starts_with($imagen, 'data:image/')) {
            throw new InvalidArgumentException('La firma no es válida. Dibuje su firma en el recuadro.');
        }

        return $imagen;
    }

    private function valorBooleano(mixed $valor): bool
    {
        return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
    }

    private function esAdminOperativo(Usuario $usuario): bool
    {
        return UsuarioRol::esAdminGlobal($usuario) || $usuario->can('asignaciones.update');
    }

    private function generarDocumentoTransporte(RutaDistribucion $ruta, Usuario $usuario): DocumentoEntrega
    {
        $codigo = $ruta->codigo ?? ('DIST-'.$ruta->rutadistribucionid);
        $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $codigo) ?: 'distribucion';
        $path = 'documentos/entrega/'.$slug.'_pdv_'.now()->format('Ymd_His').'.pdf';

        $primerPedido = $ruta->pedidos()->with('detalles')->first();

        $existente = DocumentoEntrega::query()
            ->where('metadata->rutadistribucionid', $ruta->rutadistribucionid)
            ->where('tipo_documento', 'guia_transporte')
            ->where('metadata->envio_cierre_mayorista_pdv', true)
            ->first();

        if ($existente) {
            if ($primerPedido) {
                $existente->update([
                    'metadata' => array_merge($existente->metadata ?? [], [
                        'pedidodistribucionid' => $primerPedido->pedidodistribucionid,
                    ]),
                ]);
            }
            DocumentoEntregaArchivo::generarPdfOperativo($existente);

            return $existente->fresh();
        }

        $documento = DocumentoEntrega::create([
            'externo_envio_id' => $codigo,
            'pedidoid' => null,
            'usuarioid' => $usuario->usuarioid,
            'tipo_documento' => 'guia_transporte',
            'titulo' => 'Comprobante de entrega PDV — '.$codigo,
            'archivo_path' => $path,
            'almacenid' => $ruta->almacen_mayorista_origenid,
            'metadata' => [
                'envio_cierre_mayorista_pdv' => true,
                'rutadistribucionid' => $ruta->rutadistribucionid,
                'pedidodistribucionid' => $primerPedido?->pedidodistribucionid,
            ],
        ]);

        DocumentoEntregaArchivo::generarPdfOperativo($documento);

        return $documento;
    }
}
