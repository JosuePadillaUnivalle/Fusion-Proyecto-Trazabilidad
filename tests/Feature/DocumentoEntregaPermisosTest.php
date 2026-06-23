<?php

namespace Tests\Feature;

use App\Models\DocumentoEntrega;
use App\Models\Usuario;
use App\Support\DocumentoEntregaCatalogo;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentoEntregaPermisosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function transportista(): Usuario
    {
        Role::findOrCreate('transportista', 'web');

        $user = Usuario::create([
            'nombre' => 'Lucía',
            'apellido' => 'Chofer',
            'email' => 'transportista.docs@test.local',
            'nombreusuario' => 'transportista_docs',
            'passwordhash' => Hash::make('secret'),
            'role' => 'transportista',
            'fecharegistro' => now(),
            'activo' => true,
        ]);
        $user->assignRole('transportista');

        return $user;
    }

    private function documento(Usuario $generador): DocumentoEntrega
    {
        return DocumentoEntrega::create([
            'titulo' => 'Comprobante de entrega PDV — RD-TEST-0001',
            'tipo_documento' => 'guia_transporte',
            'externo_envio_id' => 'RD-TEST-0001',
            'archivo_path' => 'documentos_entrega/test.pdf',
            'usuarioid' => $generador->usuarioid,
            'metadata' => [
                'original_name' => 'test.pdf',
                'mime' => 'application/pdf',
            ],
        ]);
    }

    public function test_transportista_no_puede_editar_ni_eliminar_documento(): void
    {
        $transportista = $this->transportista();
        $documento = $this->documento($transportista);

        $this->assertFalse(DocumentoEntregaCatalogo::puedeEditar($documento, $transportista));
        $this->assertFalse(DocumentoEntregaCatalogo::puedeEliminar($documento, $transportista));

        $this->actingAs($transportista)
            ->get(route('logistica.documentos.edit', $documento))
            ->assertForbidden();

        $this->actingAs($transportista)
            ->delete(route('logistica.documentos.destroy', $documento))
            ->assertForbidden();
    }

    public function test_transportista_puede_ver_y_descargar_documento(): void
    {
        $transportista = $this->transportista();
        $documento = $this->documento($transportista);

        $this->actingAs($transportista)
            ->get(route('logistica.documentos.show', $documento))
            ->assertOk();
    }
}
