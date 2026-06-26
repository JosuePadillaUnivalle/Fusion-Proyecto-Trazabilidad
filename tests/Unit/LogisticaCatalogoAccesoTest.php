<?php

namespace Tests\Unit;

use App\Models\Usuario;
use App\Support\LogisticaCatalogoAcceso;
use Tests\TestCase;

class LogisticaCatalogoAccesoTest extends TestCase
{
    private function usuarioConRol(string $rol): Usuario
    {
        $user = new Usuario(['usuarioid' => 1]);
        $user->setRelation('roles', collect([(object) ['name' => $rol]]));

        return $user;
    }

    public function test_jefe_planta_puede_ver_catalogos_pero_no_editar_tipos_empaque(): void
    {
        $user = $this->usuarioConRol('jefe_planta');

        $this->assertTrue(LogisticaCatalogoAcceso::puedeVer($user));
        $this->assertFalse(LogisticaCatalogoAcceso::puedeGestionar($user, 'tipos-empaque'));
        $this->assertFalse(LogisticaCatalogoAcceso::puedeGestionar($user, 'condiciones'));
        $this->assertFalse(LogisticaCatalogoAcceso::puedeGestionar($user, 'incidentes'));
        $this->assertFalse(LogisticaCatalogoAcceso::puedeGestionar($user, 'tamano-conteo'));
        $this->assertTrue(LogisticaCatalogoAcceso::puedeGestionar($user, 'tipos-vehiculo'));
        $this->assertTrue(LogisticaCatalogoAcceso::puedeGestionar($user, 'tipos-transporte'));
    }

    public function test_jefe_agricultor_puede_editar_tamano_conteo_y_vehiculos(): void
    {
        $user = $this->usuarioConRol('jefe_agricultor');

        $this->assertTrue(LogisticaCatalogoAcceso::puedeVer($user));
        $this->assertFalse(LogisticaCatalogoAcceso::puedeGestionar($user, 'tipos-empaque'));
        $this->assertTrue(LogisticaCatalogoAcceso::puedeGestionar($user, 'tamano-conteo'));
        $this->assertTrue(LogisticaCatalogoAcceso::puedeGestionar($user, 'tipos-vehiculo'));
    }

    public function test_mayorista_puede_editar_vehiculos_y_transporte(): void
    {
        $user = $this->usuarioConRol('mayorista');

        $this->assertTrue(LogisticaCatalogoAcceso::puedeVer($user));
        $this->assertFalse(LogisticaCatalogoAcceso::puedeGestionar($user, 'tamano-conteo'));
        $this->assertTrue(LogisticaCatalogoAcceso::puedeGestionar($user, 'tipos-vehiculo'));
        $this->assertTrue(LogisticaCatalogoAcceso::puedeGestionar($user, 'tipos-transporte'));
    }
}
