<?php

namespace App\Console\Commands;

use App\Models\{Almacen, AlmacenMovimiento, DocumentoEntrega, Insumo, InsumoPresentacion, Lote, LoteProduccionPedido, PedidoDistribucion, DetallePedidoDistribucion, DetalleTrasladoPlantaMayorista, PuntoVenta, RutaDistribucion, RutaDistribucionParada, TipoEmpaque, TipoMovimientoAlmacen, TipoVehiculo, Usuario};
use App\Support\{DocumentoEntregaArchivo, EmpaquePlantaCatalogo, InsumoCatalogo, TrazabilidadProductoPdvService};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Schema, Storage};

/** Targeted recovery: no deletes, no resets of existing stock, dry run by default. */
class RestaurarCatalogosSeguroCommand extends Command
{
    protected $signature = 'agrofusion:restaurar-catalogos-seguro {--apply : Commit the scoped recovery}';
    protected $description = 'Recupera catálogos y dos recorridos QR conservando registros y stock existentes';
    private array $documentos = [];

    public function handle(): int
    {
        DB::connection()->beforeExecuting(function ($sql) {
            if (preg_match('/^\s*(delete|truncate|drop|alter)\b/i', $sql)) {
                throw new \RuntimeException('Operación destructiva bloqueada durante recuperación.');
            }
        });
        $stocks = Insumo::pluck('stock', 'insumoid')->all();
        DB::beginTransaction();
        try {
            $this->empaques();
            foreach ([['CAMIONETA','Camioneta','pequeno',1500,8],['CAMION_PQ','Camión pequeño','mediano',3500,15],['CAMION_GR','Camión grande','grande',10000,40]] as [$codigo,$nombre,$tamano,$kg,$m3]) {
                TipoVehiculo::firstOrCreate(['codigo'=>$codigo], ['nombre'=>$nombre,'tamano'=>$tamano,'capacidad_kg'=>$kg,'capacidad_m3'=>$m3,'activo'=>true]);
            }
            foreach (['FlotaVehiculosPorAmbitoSeeder','MaquinasProcesoPlantaSeeder','PlantillasTransformacionSeeder','PlantaInsumosOperativosSeeder'] as $seeder) {
                $this->call('db:seed', ['--class'=>$seeder,'--force'=>true]);
            }
            $this->documentos();
            $this->restaurarPdv('PAP', 'TRZ-PDV-PAPA-HUAYCHA-202609', 'TRAZ-PAP-SAC-2026-001', 'Papa Huaycha lavada', 2);
            $this->restaurarPdv('ZAN', 'TRZ-PDV-ZANAHORIA-202601', 'TRAZ-ZAN-TIQ-2026-001', 'Zanahoria Imperator envasada', 1);
            foreach ($stocks as $id=>$stock) {
                $actual = Insumo::findOrFail($id);
                if ((float)$actual->stock !== (float)$stock) throw new \RuntimeException('Cambió stock previo: '.$id);
            }
            $summary=[];
            foreach (['TRZ-PDV-PAPA-HUAYCHA-202609','TRZ-PDV-ZANAHORIA-202601'] as $code) {
                $r=app(TrazabilidadProductoPdvService::class)->reportePorCodigo($code);
                if (!$r || count($r['eventos_agrupados'])!==5) throw new \RuntimeException('Recorrido incompleto: '.$code);
                $summary[$code]=['eventos'=>array_sum(array_column($r['eventos_agrupados'],'total')),'etapas'=>5,'stock'=>$r['stock_actual']];
            }
            if (!$this->option('apply')) {
                DB::rollBack();
                $this->info('Simulación correcta; cambios revertidos. '.json_encode($summary,JSON_UNESCAPED_UNICODE));
                return self::SUCCESS;
            }
            DB::commit();
        } catch (\Throwable $e) {
            if (DB::transactionLevel()>0) DB::rollBack();
            throw $e;
        }
        foreach ($this->documentos as $id) {
            $doc=DocumentoEntrega::findOrFail($id);
            if (!DocumentoEntregaArchivo::materializarPdfDocumento($doc)) throw new \RuntimeException('No se pudo materializar PDF '.$id);
            $bytes=Storage::disk('public')->get($doc->archivo_path);
            if (!str_starts_with($bytes,'%PDF-')) throw new \RuntimeException('Archivo no es PDF: '.$id);
        }
        $this->info('Recuperación aplicada. Stock previo conservado. PDFs: '.count($this->documentos));
        $this->line(json_encode($summary,JSON_UNESCAPED_UNICODE));
        return self::SUCCESS;
    }

    private function empaques(): void
    {
        EmpaquePlantaCatalogo::asegurarTiposEmpaqueEnBd();
    }

    private function documentos(): void
    {
        // Solo comprobantes reales de cierre (guia_transporte + metadata envio_cierre_*).
        // No inventar nota_entrega / guia_entrega / confirmacion_entrega.
        $this->call('agrofusion:reparar-documentos-empaque', ['--apply' => true]);
        $this->documentos = DocumentoEntrega::query()
            ->where('tipo_documento', 'guia_transporte')
            ->where(function ($q) {
                $q->where('metadata->envio_cierre_agricola', true)
                    ->orWhere('metadata->envio_cierre_planta_mayorista', true)
                    ->orWhere('metadata->envio_cierre_mayorista_pdv', true);
            })
            ->pluck('documentoentregaid')
            ->all();
    }

    private function restaurarPdv(string $suffix,string $qr,string $loteCodigo,string $producto,int $peso): void
    {
        $pedido=PedidoDistribucion::where('numero_solicitud','PDV-'.$suffix.'-2026-001')->firstOrFail();
        $punto=PuntoVenta::findOrFail($pedido->puntoventaid);
        $lote=Lote::where('codigo_trazabilidad',$loteCodigo)->firstOrFail();
        $lp=LoteProduccionPedido::where('codigo_lote','LPP-'.$suffix.'-2026-001')->firstOrFail();
        $planta=Insumo::where('nombre',$producto)->where('almacenid',$pedido->almacen_planta_origenid)->firstOrFail();
        $tipo=$planta->tipoinsumoid;$um=$planta->unidadmedidaid;
        $catalogo=json_decode(file_get_contents(public_path('images/catalogo-fuentes.json')),true);
        $nombrePdv=$producto.' · Bolsa '.$peso.' kg';
        $imagen=$catalogo['inputs'][$nombrePdv]??$catalogo['inputs'][$producto]??null;
        $may=Insumo::firstOrCreate(['nombre'=>$producto,'almacenid'=>$pedido->almacen_mayorista_origenid],['tipoinsumoid'=>$tipo,'unidadmedidaid'=>$um,'stock'=>160,'stockminimo'=>20,'descripcion'=>'Producto de demostración restaurado — origen lote '.$loteCodigo,'imagenurl'=>$imagen]);
        $pdv=Insumo::where('codigo_trazabilidad',$qr)->first();
        if (!$pdv) {
            $pdv=Insumo::firstOrCreate(['nombre'=>$nombrePdv,'almacenid'=>$punto->almacenid],['tipoinsumoid'=>$tipo,'unidadmedidaid'=>$um,'stock'=>40,'stockminimo'=>5,'descripcion'=>'Producto procesado recibido del mayorista — trazabilidad lote '.$loteCodigo.' — '.$pedido->numero_solicitud,'codigo_trazabilidad'=>$qr,'imagenurl'=>$imagen]);
            if (!$pdv->codigo_trazabilidad) $pdv->update(['codigo_trazabilidad'=>$qr]);
            if ($pdv->codigo_trazabilidad!==$qr) throw new \RuntimeException('Código previo distinto; no se reemplazó: '.$pdv->insumoid);
        }
        $presentaciones=[];
        foreach ([$planta,$may,$pdv] as $insumo) {
            $presentaciones[$insumo->insumoid]=InsumoPresentacion::firstOrCreate(['insumoid'=>$insumo->insumoid,'nombre'=>'Bolsa '.$peso.' kg'],['tipoempaqueid'=>TipoEmpaque::where('nombre','Bolsa plástica')->value('tipoempaqueid'),'tipo_envase'=>'bolsa','peso_neto_kg'=>$peso,'orden'=>1,'activo'=>true]);
        }
        $detalle=DetallePedidoDistribucion::where('pedidodistribucionid',$pedido->pedidodistribucionid)->where('producto_nombre',$nombrePdv)->firstOrFail();
        $patch=[];
        if (!$detalle->insumoid) $patch['insumoid']=$may->insumoid;
        if (!InsumoPresentacion::find($detalle->insumo_presentacionid)) $patch['insumo_presentacionid']=$presentaciones[$may->insumoid]->insumo_presentacionid;
        if ($patch) DetallePedidoDistribucion::updateOrCreate(['detallepedidodistribucionid'=>$detalle->detallepedidodistribucionid],$patch);
        $rpm=RutaDistribucion::where('codigo','RUT-PM-'.$suffix.'-001')->firstOrFail();
        DetalleTrasladoPlantaMayorista::firstOrCreate(['rutadistribucionid'=>$rpm->rutadistribucionid,'insumoid'=>$planta->insumoid,'loteproduccionpedidoid'=>$lp->loteproduccionpedidoid],['insumo_presentacionid'=>$presentaciones[$planta->insumoid]->insumo_presentacionid,'presentacion_nombre'=>'Bolsa '.$peso.' kg','producto_nombre'=>$producto,'cantidad'=>200,'cantidad_unidades'=>200/$peso,'observaciones'=>'Relación de demostración restaurada por solicitud del usuario.']);
        $rpdv=RutaDistribucion::firstOrCreate(['codigo'=>'RUT-PDV-'.$suffix.'-001'],['nombre'=>'Centro mayorista → '.$punto->nombre,'tipo_ruta'=>'mayorista_pdv','almacen_mayorista_origenid'=>$pedido->almacen_mayorista_origenid,'transportista_usuarioid'=>$rpm->transportista_usuarioid,'creado_por_usuarioid'=>$pedido->creado_por_usuarioid,'estado'=>'completada','fecha_salida'=>$pedido->fecha_envio,'llegada_confirmada_at'=>$pedido->fecha_recepcion]);
        if (!$pedido->rutadistribucionid) PedidoDistribucion::updateOrCreate(['pedidodistribucionid'=>$pedido->pedidodistribucionid],['rutadistribucionid'=>$rpdv->rutadistribucionid]);
        // Coordinates are the existing demonstration locations, never guessed real delivery addresses.
        $coords=$suffix==='PAP'?[[-17.3935,-66.157],[-17.414,-66.1655]]:[[-17.7833,-63.1821],[-17.7908,-63.1816]];
        foreach ([[$rpm,1,'carga_planta',$pedido->almacen_planta_origenid,null,$coords[0]],[$rpm,2,'entrega_mayorista',$pedido->almacen_mayorista_origenid,null,$coords[1]],[$rpdv,1,'carga_mayorista',$pedido->almacen_mayorista_origenid,null,$coords[1]],[$rpdv,2,'entrega_pdv',null,$punto->puntoventaid,[$punto->latitud,$punto->longitud]]] as [$ruta,$orden,$tipoParada,$almacen,$pv,$geo]) {
            RutaDistribucionParada::firstOrCreate(['rutadistribucionid'=>$ruta->rutadistribucionid,'orden'=>$orden],['tipo'=>$tipoParada,'almacenid'=>$almacen,'puntoventaid'=>$pv,'pedidodistribucionid'=>$pv?$pedido->pedidodistribucionid:null,'destino'=>$pv?$punto->nombre:Almacen::findOrFail($almacen)->nombre,'latitud'=>$geo[0],'longitud'=>$geo[1],'estado'=>'completada']);
        }
        $ingreso=TipoMovimientoAlmacen::where('naturaleza','ingreso')->where('activo',true)->firstOrFail();
        foreach ([[$may,$rpm->codigo,$rpm->fecha_aprobacion_mayorista,200,'[Recepción mayorista]'],[$pdv,$pedido->numero_solicitud,$pedido->fecha_recepcion,40,'[Recepción PDV]']] as [$i,$ref,$fecha,$cantidad,$obs]) {
            AlmacenMovimiento::firstOrCreate(['referencia'=>$ref,'insumoid'=>$i->insumoid],['almacenid'=>$i->almacenid,'tipo_movimiento_almacenid'=>$ingreso->tipo_movimiento_almacenid,'usuarioid'=>$pedido->creado_por_usuarioid,'fecha'=>$fecha,'cantidad'=>$cantidad,'observaciones'=>$obs.' Relación restaurada — '.$loteCodigo]);
        }
        // Supply catalog entry is separate from the protected harvested lot and finished products.
        if ($suffix==='PAP') Insumo::firstOrCreate(['nombre'=>'Papa Huaycha','almacenid'=>$pedido->almacen_planta_origenid],['tipoinsumoid'=>$lote->loteInsumos->first()?->insumo?->tipoinsumoid??$tipo,'unidadmedidaid'=>$um,'stock'=>320,'stockminimo'=>5,'descripcion'=>'Materia prima del catálogo de demostración para planta.']);
    }
}
