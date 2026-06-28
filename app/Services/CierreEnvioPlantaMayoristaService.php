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
use App\Models\RutaDistribucion;
use App\Models\TipoIncidenteTransporte;
use App\Models\Usuario;
use App\Support\DocumentoEntregaArchivo;
use App\Support\EnvioCierreAgricolaCatalogo;
use App\Support\MayoristaAccess;
use App\Support\RutaDistribucionCatalogo;
use App\Support\SimulacionRutaCatalogo;
use App\Support\TrasladoPlantaMayoristaPresentacion;
use App\Support\UsuarioRol;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CierreEnvioPlantaMayoristaService
{
    public function __construct(
        private readonly TrasladoPlantaMayoristaService $traslados,
        private readonly SimulacionRutaService $simulacion,
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
            ->where('metadata->envio_cierre_planta_mayorista', true)
            ->orderByDesc('documentoentregaid')
            ->first();
    }

    public function repararMetadataDocumento(RutaDistribucion $ruta): bool
    {
        if (! $ruta->esTrasladoPlantaMayorista()) {
            return false;
        }

        $documento = $this->documentoEntrega($ruta);
        if ($documento === null) {
            return false;
        }

        $meta = $documento->metadata ?? [];
        $actualizaciones = [];

        if (trim((string) ($meta['destino_mayorista_nombre'] ?? '')) === '') {
            $nombre = TrasladoPlantaMayoristaPresentacion::nombreDestinoMayorista($ruta);
            if ($nombre !== null && $nombre !== '') {
                $actualizaciones['destino_mayorista_nombre'] = $nombre;
            }
        }

        $lineas = $meta['lineas_producto'] ?? null;
        if (! is_array($lineas) || $lineas === []) {
            $snap = TrasladoPlantaMayoristaPresentacion::snapshotLineasParaMetadata($ruta);
            if ($snap !== []) {
                $actualizaciones['lineas_producto'] = $snap;
            }
        }

        if ($actualizaciones === []) {
            return false;
        }

        $documento->update([
            'metadata' => array_merge($meta, $actualizaciones),
        ]);

        return true;
    }

    /** @return array{revisados: int, actualizados: int} */
    public function repararMetadataDocumentos(): array
    {
        $revisados = 0;
        $actualizados = 0;

        RutaDistribucion::query()
            ->where('tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA)
            ->with(['almacenMayoristaDestino', 'paradas', 'detallesTraslado.insumo.unidadMedida'])
            ->orderBy('rutadistribucionid')
            ->chunkById(50, function ($rutas) use (&$revisados, &$actualizados) {
                foreach ($rutas as $ruta) {
                    $revisados++;
                    if ($this->repararMetadataDocumento($ruta)) {
                        $actualizados++;
                    }
                }
            }, 'rutadistribucionid');

        return ['revisados' => $revisados, 'actualizados' => $actualizados];
    }

    /** @return array<string, mixed> */
    public function resumenPasos(RutaDistribucion $ruta): array
    {
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

        $pasoActual = EnvioCierreAgricolaCatalogo::PASO_CONDICIONES;
        if ($recibido) {
            $pasoActual = EnvioCierreAgricolaCatalogo::PASO_COMPLETADO;
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
        } elseif ($tieneCondiciones) {
            $pasoActual = EnvioCierreAgricolaCatalogo::PASO_EN_RUTA;
        }

        return app(RecepcionQrFirmaService::class)->enriquecerResumen([
            'paso_actual' => $pasoActual,
            'tiene_condiciones' => $tieneCondiciones,
            'en_ruta' => $enRuta,
            'progreso' => $progreso,
            'esperando_confirmacion' => ! $recibido && ! $llegadaConfirmada && $progreso >= 100,
            'llegada_confirmada' => $llegadaConfirmada,
            'tiene_incidentes' => $tieneIncidentes,
            'firma_transportista' => $firmaTransportista,
            'firma_recepcion' => $firmaRecepcion,
            'recibido_planta' => $recibido,
            'puede_registrar_condiciones' => ! $tieneCondiciones && ! $enRuta && ! $recibido
                && RutaDistribucionCatalogo::puedeEmpezarTrasladoPlanta($ruta),
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
        $this->validarTrasladoActivo($ruta);

        if ($this->tieneCondicionesVehiculo($ruta)) {
            throw new InvalidArgumentException('Las condiciones del vehículo ya fueron registradas.');
        }

        if (SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            throw new InvalidArgumentException('No puede modificar condiciones con la ruta en curso.');
        }

        if ($ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA) {
            throw new InvalidArgumentException('Este traslado ya fue completado.');
        }

        $catalogo = CondicionTransporte::query()->orderBy('condiciontransporteid')->get();
        if ($catalogo->isEmpty()) {
            throw new InvalidArgumentException('No hay condiciones de transporte configuradas en el catálogo.');
        }

        return DB::transaction(function () use ($ruta, $usuario, $perfectasCondiciones, $condiciones, $observaciones, $catalogo) {
            $checklist = ChecklistCondicionLogistica::create([
                'rutadistribucionid' => $ruta->rutadistribucionid,
                'almacenid' => $ruta->almacen_planta_origenid,
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
        $this->validarTrasladoActivo($ruta);

        if ($ruta->llegada_confirmada_at) {
            throw new InvalidArgumentException('La llegada ya fue confirmada.');
        }

        if ($ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA) {
            throw new InvalidArgumentException('Este traslado ya fue completado.');
        }

        if (! SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            throw new InvalidArgumentException('El traslado debe estar en ruta para confirmar la llegada.');
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
        $this->validarTrasladoActivo($ruta);

        if ($ruta->llegada_confirmada_at === null) {
            throw new InvalidArgumentException('Debe confirmar la llegada antes de registrar incidentes.');
        }

        if ($ruta->checklistIncidente()->exists()) {
            throw new InvalidArgumentException('Los incidentes ya fueron registrados para este traslado.');
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

        app(NotificacionUsuarioService::class)->trasladoPlantaPendienteFirmaMayorista($ruta->fresh(['almacenMayoristaDestino', 'transportista']));

        return $firma;
    }

    public function guardarFirmaRecepcion(RutaDistribucion $ruta, Usuario $usuario, string $imagenBase64): FirmaRecepcionEnvio
    {
        $this->autorizarFirmaRecepcion($usuario, $ruta);
        $this->validarPreFirmas($ruta);

        if ($ruta->firmaRecepcion()->exists()) {
            throw new InvalidArgumentException('La firma de recepción ya fue registrada.');
        }

        $firma = FirmaRecepcionEnvio::create([
            'rutadistribucionid' => $ruta->rutadistribucionid,
            'imagenfirma' => $this->normalizarImagenFirma($imagenBase64),
            'fechafirma' => now(),
        ]);

        return $firma;
    }

    public function finalizarEntrega(RutaDistribucion $ruta, Usuario $usuario): DocumentoEntrega
    {
        $this->autorizarFinalizar($usuario, $ruta);
        $resumen = $this->resumenPasos($ruta);

        if (! ($resumen['puede_finalizar'] ?? false)) {
            throw new InvalidArgumentException('Complete condiciones, llegada, incidentes y firmas antes de finalizar.');
        }

        $documento = DB::transaction(function () use ($ruta, $usuario) {
            $this->traslados->transferirInventarioAlCompletar($ruta, $usuario);

            $ruta->update([
                'estado' => RutaDistribucionCatalogo::ESTADO_COMPLETADA,
            ]);

            $ruta->refresh();

            $documento = $this->generarDocumentoTransporte($ruta, $usuario);
            app(NotificacionUsuarioService::class)->trasladoPlantaRecibidoEnMayorista($ruta->fresh(['almacenMayoristaDestino', 'transportista']));

            return $documento;
        });

        DocumentoEntregaArchivo::materializarPdfDocumento($documento);

        return $documento;
    }

    public function autorizarConfirmacionLlegada(Usuario $usuario, RutaDistribucion $ruta): void
    {
        if ($this->esTransportistaAsignado($usuario, $ruta) || $this->esAdminOperativo($usuario)) {
            return;
        }

        throw new InvalidArgumentException('No tiene permiso para confirmar la llegada de este traslado.');
    }

    public function autorizarIncidentes(Usuario $usuario, RutaDistribucion $ruta): void
    {
        if ($this->esTransportistaAsignado($usuario, $ruta) || $this->esAdminOperativo($usuario)) {
            return;
        }

        throw new InvalidArgumentException('No tiene permiso para registrar incidentes en este traslado.');
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
            || MayoristaAccess::puedeGestionarTraslado($usuario, $ruta)
            || $this->esTransportistaAsignado($usuario, $ruta)
        ) {
            return;
        }

        throw new InvalidArgumentException('No tiene permiso para firmar la recepción en el almacén mayorista.');
    }

    private function autorizarFinalizar(Usuario $usuario, RutaDistribucion $ruta): void
    {
        if ($this->esTransportistaAsignado($usuario, $ruta)
            || $this->esAdminOperativo($usuario)
            || MayoristaAccess::puedeGestionarTraslado($usuario, $ruta)) {
            return;
        }

        throw new InvalidArgumentException('No tiene permiso para finalizar este traslado.');
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

    private function validarTrasladoActivo(RutaDistribucion $ruta): void
    {
        if (! $ruta->esTrasladoPlantaMayorista()) {
            throw new InvalidArgumentException('La ruta no es un traslado planta → mayorista.');
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
        $codigo = $ruta->codigo ?? ('TRASL-'.$ruta->rutadistribucionid);
        $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $codigo) ?: 'traslado';
        $path = 'documentos/entrega/'.$slug.'_transporte_'.now()->format('Ymd_His').'.pdf';

        $existente = DocumentoEntrega::query()
            ->where('metadata->rutadistribucionid', $ruta->rutadistribucionid)
            ->where('tipo_documento', 'guia_transporte')
            ->where('metadata->envio_cierre_planta_mayorista', true)
            ->first();

        if ($existente) {
            $existente->update([
                'metadata' => array_merge($existente->metadata ?? [], [
                    'destino_mayorista_nombre' => TrasladoPlantaMayoristaPresentacion::nombreDestinoMayorista($ruta),
                    'lineas_producto' => TrasladoPlantaMayoristaPresentacion::snapshotLineasParaMetadata($ruta),
                ]),
            ]);
            DocumentoEntregaArchivo::generarPdfOperativo($existente);

            return $existente;
        }

        $documento = DocumentoEntrega::create([
            'externo_envio_id' => $codigo,
            'pedidoid' => null,
            'usuarioid' => $usuario->usuarioid,
            'tipo_documento' => 'guia_transporte',
            'titulo' => 'Documento de transporte de carga — '.$codigo,
            'archivo_path' => $path,
            'almacenid' => $ruta->almacen_planta_origenid,
            'metadata' => [
                'envio_cierre_planta_mayorista' => true,
                'rutadistribucionid' => $ruta->rutadistribucionid,
                'destino_mayorista_nombre' => TrasladoPlantaMayoristaPresentacion::nombreDestinoMayorista($ruta),
                'lineas_producto' => TrasladoPlantaMayoristaPresentacion::snapshotLineasParaMetadata($ruta),
            ],
        ]);

        DocumentoEntregaArchivo::generarPdfOperativo($documento);

        return $documento;
    }
}
