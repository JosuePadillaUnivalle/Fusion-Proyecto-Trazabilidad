<?php

namespace Tests\Unit;

use App\Models\DocumentoEntrega;
use App\Models\RutaDistribucion;
use App\Models\RutaDistribucionParada;
use App\Models\Usuario;
use App\Support\RutaDistribucionCatalogo;
use App\Support\TrasladoPlantaMayoristaPresentacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TrasladoPlantaMayoristaPresentacionTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioMinimo(): Usuario
    {
        return Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'test.presentacion@local',
            'nombreusuario' => 'test_presentacion',
            'passwordhash' => Hash::make('secret'),
            'role' => 'admin',
            'fecharegistro' => now(),
            'activo' => true,
        ]);
    }

    public function test_nombre_destino_desde_parada_cuando_almacen_eliminado(): void
    {
        $transportista = $this->usuarioMinimo();

        $ruta = RutaDistribucion::create([
            'codigo' => 'TPM-TEST-0001',
            'nombre' => 'Test',
            'tipo_ruta' => RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA,
            'almacen_planta_origenid' => null,
            'almacen_mayorista_destinoid' => 99999,
            'transportista_usuarioid' => $transportista->usuarioid,
            'estado' => RutaDistribucionCatalogo::ESTADO_COMPLETADA,
        ]);

        RutaDistribucionParada::create([
            'rutadistribucionid' => $ruta->rutadistribucionid,
            'orden' => 2,
            'tipo' => RutaDistribucionCatalogo::PARADA_ENTREGA_MAYORISTA,
            'destino' => 'Entrega: Almacén Mayorista Demo - MAY_001',
            'estado' => 'completada',
        ]);

        $nombre = TrasladoPlantaMayoristaPresentacion::nombreDestinoMayorista($ruta->fresh('paradas'));

        $this->assertSame('Almacén Mayorista Demo - MAY_001', $nombre);
    }

    public function test_lineas_producto_desde_metadata_documento(): void
    {
        $transportista = $this->usuarioMinimo();

        $ruta = RutaDistribucion::create([
            'codigo' => 'TPM-TEST-0002',
            'nombre' => 'Test',
            'tipo_ruta' => RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA,
            'transportista_usuarioid' => $transportista->usuarioid,
            'estado' => RutaDistribucionCatalogo::ESTADO_COMPLETADA,
        ]);

        DocumentoEntrega::create([
            'externo_envio_id' => 'TPM-TEST-0002',
            'tipo_documento' => 'guia_transporte',
            'titulo' => 'Guía test',
            'archivo_path' => 'documentos/test.pdf',
            'metadata' => [
                'envio_cierre_planta_mayorista' => true,
                'rutadistribucionid' => $ruta->rutadistribucionid,
                'lineas_producto' => [
                    [
                        'producto' => 'Salsa de tomate',
                        'presentacion' => 'Frasco 340 g',
                        'cantidad' => 68,
                        'unidad' => 'kg',
                    ],
                ],
            ],
        ]);

        $lineas = TrasladoPlantaMayoristaPresentacion::lineasProducto($ruta->fresh());

        $this->assertCount(1, $lineas);
        $this->assertSame('Salsa de tomate', $lineas->first()['producto']);
        $this->assertSame(68.0, $lineas->first()['cantidad']);
    }

    public function test_resumen_carga_lista_desde_metadata(): void
    {
        $transportista = $this->usuarioMinimo();

        $ruta = RutaDistribucion::create([
            'codigo' => 'TPM-TEST-0003',
            'nombre' => 'Test',
            'tipo_ruta' => RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA,
            'transportista_usuarioid' => $transportista->usuarioid,
            'estado' => RutaDistribucionCatalogo::ESTADO_COMPLETADA,
        ]);

        DocumentoEntrega::create([
            'externo_envio_id' => 'TPM-TEST-0003',
            'tipo_documento' => 'guia_transporte',
            'titulo' => 'Guía test',
            'archivo_path' => 'documentos/test.pdf',
            'metadata' => [
                'envio_cierre_planta_mayorista' => true,
                'rutadistribucionid' => $ruta->rutadistribucionid,
                'lineas_producto' => [
                    [
                        'producto' => 'Salsa de tomate Perita',
                        'presentacion' => 'Frasco 340 g',
                        'cantidad_unidades' => 200,
                        'cantidad' => 68,
                        'unidad' => 'kg',
                    ],
                ],
            ],
        ]);

        $resumen = TrasladoPlantaMayoristaPresentacion::resumenCargaLista($ruta->fresh());

        $this->assertStringContainsString('Salsa de tomate Perita', $resumen);
        $this->assertStringContainsString('Frasco 340 g', $resumen);
        $this->assertStringContainsString('200 u', $resumen);
        $this->assertStringContainsString('68,00 kg', $resumen);
    }
}
