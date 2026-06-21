<?php

namespace Tests\Unit;

use App\Models\Lote;
use App\Models\Usuario;
use App\Support\UsuarioGestionResumen;
use PHPUnit\Framework\TestCase;

/**
 * Prueba unitaria — Lotes como responsable en detalle de usuario (AgroFusion).
 *
 * Ejemplo operativo: Luis Guerrero con 3 lotes (Maíz, Tomate 2, Lote Zanahoria Imperator).
 */
class UsuarioLotesResponsableTest extends TestCase
{
    public function test_usuario_lista_lotes_como_responsable(): void
    {
        $usuario = new Usuario([
            'nombre' => 'Luis',
            'apellido' => 'Guerrero',
            'nombreusuario' => 'lguerrero8718',
        ]);

        $usuario->setRelation('lotes', collect([
            new Lote(['loteid' => 1, 'nombre' => 'Maiz']),
            new Lote(['loteid' => 2, 'nombre' => 'Tomate 2']),
            new Lote(['loteid' => 3, 'nombre' => 'Lote Zanahoria Imperator']),
        ]));

        $this->assertSame(3, UsuarioGestionResumen::cantidadLotesComoResponsable($usuario));
        $this->assertSame(
            ['Maiz', 'Tomate 2', 'Lote Zanahoria Imperator'],
            UsuarioGestionResumen::nombresLotesComoResponsable($usuario)
        );
    }
}
