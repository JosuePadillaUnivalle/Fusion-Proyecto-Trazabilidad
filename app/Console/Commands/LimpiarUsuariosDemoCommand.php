<?php

namespace App\Console\Commands;

use App\Services\UsuarioEliminacionService;
use Illuminate\Console\Command;

class LimpiarUsuariosDemoCommand extends Command
{
    protected $signature = 'usuarios:limpiar-demo
                            {--dry-run : Solo listar usuarios que se eliminarían}
                            {--force : Eliminar sin confirmación}';

    protected $description = 'Elimina usuarios demo dejando solo admin, agricultor, planta y transportista';

    public function handle(UsuarioEliminacionService $service): int
    {
        $protegidos = UsuarioEliminacionService::emailsProtegidos();
        $this->info('Usuarios protegidos: '.implode(', ', $protegidos));

        $aEliminar = \App\Models\Usuario::query()
            ->whereNotIn('email', $protegidos)
            ->orderBy('usuarioid')
            ->get(['usuarioid', 'nombre', 'apellido', 'email']);

        if ($aEliminar->isEmpty()) {
            $this->info('No hay usuarios extra para eliminar.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Nombre', 'Correo'],
            $aEliminar->map(fn ($u) => [$u->usuarioid, trim($u->nombre.' '.$u->apellido), $u->email])->all()
        );

        if ($this->option('dry-run')) {
            $this->warn('Modo simulación: no se eliminó nada.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('¿Eliminar '.$aEliminar->count().' usuario(s)?', true)) {
            return self::SUCCESS;
        }

        $resultado = $service->eliminarUsuariosNoEsenciales();
        $this->info('Eliminados: '.$resultado['eliminados']);

        return self::SUCCESS;
    }
}
