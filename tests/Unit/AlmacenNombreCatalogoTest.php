<?php

namespace Tests\Unit;

use App\Support\AlmacenNombreCatalogo;
use Tests\TestCase;

class AlmacenNombreCatalogoTest extends TestCase
{
    public function test_etiqueta_lista_desde_nombre_canonico(): void
    {
        $etiqueta = AlmacenNombreCatalogo::etiquetaListaDesdeNombreCanonico(
            'Almacén Planta, Parque Industrial, Santa Cruz - PLA_D44C5'
        );

        $this->assertSame('PLA_D44C5 · Parque Industrial, Santa Cruz · Almacén Planta', $etiqueta);
    }

    public function test_etiqueta_lista_desde_prefijo_parada(): void
    {
        $etiqueta = AlmacenNombreCatalogo::etiquetaListaDesdeNombreCanonico(
            'Entrega: Almacén Mayorista, Avenida Noel Kempff, Piraí - MAY_BCF02'
        );

        $this->assertSame('MAY_BCF02 · Avenida Noel Kempff, Piraí · Almacén Mayorista', $etiqueta);
    }

    public function test_sugerir_nombre_nuevo_desde_mapa(): void
    {
        $nombre = AlmacenNombreCatalogo::sugerirNombreNuevo(
            \App\Support\AlmacenAmbito::PLANTA,
            -17.7833,
            -63.1821,
            'Parque Industrial, Santa Cruz'
        );

        $this->assertStringStartsWith('Almacén Planta, Parque Industrial, Santa Cruz - PLA_', $nombre);
    }
}
