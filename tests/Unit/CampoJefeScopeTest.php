<?php

namespace Tests\Unit;

use App\Models\Usuario;
use App\Support\CampoJefeScope;
use Tests\TestCase;

class CampoJefeScopeTest extends TestCase
{
    public function test_usuario_nulo_no_acota(): void
    {
        $this->assertFalse(CampoJefeScope::debeAcotar(null));
    }

    public function test_admin_legacy_no_acota(): void
    {
        $admin = new Usuario(['role' => 'admin', 'usuarioid' => 1]);
        $admin->setRelation('roles', collect());

        $this->assertFalse(CampoJefeScope::debeAcotar($admin));
    }
}
