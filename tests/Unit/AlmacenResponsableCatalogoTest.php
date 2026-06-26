<?php

namespace Tests\Unit;

use App\Models\Usuario;
use App\Support\AlmacenAmbito;
use App\Support\AlmacenResponsableCatalogo;
use Tests\TestCase;

class AlmacenResponsableCatalogoTest extends TestCase
{
    public function test_admin_no_es_responsable_valido(): void
    {
        $admin = new Usuario(['role' => 'admin', 'usuarioid' => 1]);
        $admin->setRelation('roles', collect());

        $this->assertFalse(AlmacenResponsableCatalogo::usuarioValidoParaAmbito($admin, AlmacenAmbito::AGRICOLA));
    }

    public function test_jefe_agricultor_legacy_es_valido(): void
    {
        $jefe = new Usuario(['role' => 'jefe_agricultor', 'usuarioid' => 2]);
        $jefe->setRelation('roles', collect());

        $this->assertTrue(AlmacenResponsableCatalogo::usuarioValidoParaAmbito($jefe, AlmacenAmbito::AGRICOLA));
    }

    public function test_roles_agricola_incluyen_jefe_y_operario(): void
    {
        $roles = AlmacenResponsableCatalogo::rolesSpatie(AlmacenAmbito::AGRICOLA);

        $this->assertContains('jefe_agricultor', $roles);
        $this->assertContains('agricultor', $roles);
    }

    public function test_planta_solo_jefe_planta_en_selector(): void
    {
        $roles = AlmacenResponsableCatalogo::rolesSpatie(AlmacenAmbito::PLANTA);

        $this->assertSame(['jefe_planta'], $roles);

        $operario = new Usuario(['role' => 'planta', 'usuarioid' => 9]);
        $operario->setRelation('roles', collect());

        $this->assertFalse(AlmacenResponsableCatalogo::usuarioValidoParaAmbito($operario, AlmacenAmbito::PLANTA));
    }
}
