<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\AlmacenMovimiento;
use App\Models\AlmacenUsuario;
use App\Models\CertificacionLote;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Models\LoteInsumo;
use App\Models\OperadorPlanta;
use App\Models\PerfilTransportista;
use App\Models\RegistroMovimientoMateria;
use App\Models\RegistroProcesoMaquinaPlanta;
use App\Models\Usuario;
use App\Models\UsuarioNotificacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsuarioEliminacionService
{
    /** @return list<string> */
    public static function emailsProtegidos(): array
    {
        return [
            'admin@agrofusion.com',
            'agricultor@agrofusion.com',
            'planta@agrofusion.com',
            'transportista@agrofusion.com',
        ];
    }

    public function puedeEliminar(Usuario $usuario): bool
    {
        return ! in_array(strtolower((string) $usuario->email), array_map('strtolower', self::emailsProtegidos()), true);
    }

    public function eliminar(Usuario $usuario): void
    {
        if (! $this->puedeEliminar($usuario)) {
            throw new \InvalidArgumentException('No se puede eliminar un usuario esencial del sistema.');
        }

        $id = (int) $usuario->usuarioid;
        $respaldoId = $this->idUsuarioRespaldo($id);

        DB::transaction(function () use ($usuario, $id, $respaldoId) {
            $usuario->syncRoles([]);

            $this->eliminarPivotPermisos($id);
            UsuarioNotificacion::query()->where('usuarioid', $id)->delete();

            if (Schema::hasTable('usuario')) {
                Usuario::query()->where('revisado_por', $id)->update(['revisado_por' => null]);
            }

            PerfilTransportista::query()->where('usuarioid', $id)->delete();
            OperadorPlanta::query()->where('usuarioid', $id)->delete();
            AlmacenUsuario::query()->where('usuarioid', $id)->delete();

            $this->anularReferenciasNullable($id);
            $this->reasignarReferenciasObligatorias($id, $respaldoId);

            $usuario->delete();
        });
    }

    /** @return array{eliminados: int, omitidos: list<string>} */
    public function eliminarUsuariosNoEsenciales(): array
    {
        $omitidos = [];
        $eliminados = 0;

        $usuarios = Usuario::query()
            ->whereNotIn('email', self::emailsProtegidos())
            ->orderBy('usuarioid')
            ->get();

        foreach ($usuarios as $usuario) {
            if (! $this->puedeEliminar($usuario)) {
                $omitidos[] = $usuario->email;

                continue;
            }

            $this->eliminar($usuario);
            $eliminados++;
        }

        return ['eliminados' => $eliminados, 'omitidos' => $omitidos];
    }

    private function idUsuarioRespaldo(int $excluirId): ?int
    {
        foreach (self::emailsProtegidos() as $email) {
            $id = Usuario::query()->where('email', $email)->value('usuarioid');
            if ($id && (int) $id !== $excluirId) {
                return (int) $id;
            }
        }

        return Usuario::query()
            ->where('usuarioid', '!=', $excluirId)
            ->orderBy('usuarioid')
            ->value('usuarioid');
    }

    private function eliminarPivotPermisos(int $usuarioid): void
    {
        $morph = Usuario::class;

        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->where('model_type', $morph)
                ->where('model_id', $usuarioid)
                ->delete();
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')
                ->where('model_type', $morph)
                ->where('model_id', $usuarioid)
                ->delete();
        }

        if (Schema::hasTable('usuario_rol')) {
            DB::table('usuario_rol')->where('usuarioid', $usuarioid)->delete();
        }
    }

    private function anularReferenciasNullable(int $usuarioid): void
    {
        $actualizaciones = [
            'pedido' => ['aceptado_por_usuarioid'],
            'incidente_envio' => ['reportadopor_usuarioid', 'resueltopor_usuarioid'],
            'documento_entrega' => ['usuarioid'],
            'direccion_geo_envio' => ['usuarioid'],
            'envios_pendientes' => ['usuarioid'],
            'calificacion_envio' => ['usuarioid'],
            'ruta_multi_entrega' => ['creadopor_usuarioid', 'transportista_usuarioid'],
            'envio_asignacion_multiple' => ['transportista_usuarioid', 'asignadopor_usuarioid'],
            'evaluacion_final_lote_produccion' => ['inspector_usuarioid'],
            'checklist_condicion_logistica' => ['revisado_por_usuarioid'],
        ];

        foreach ($actualizaciones as $tabla => $columnas) {
            if (! Schema::hasTable($tabla)) {
                continue;
            }
            foreach ($columnas as $columna) {
                if (! Schema::hasColumn($tabla, $columna)) {
                    continue;
                }
                DB::table($tabla)->where($columna, $usuarioid)->update([$columna => null]);
            }
        }
    }

    private function reasignarReferenciasObligatorias(int $usuarioid, ?int $respaldoId): void
    {
        if (! $respaldoId) {
            return;
        }

        $modelos = [
            Lote::class,
            Actividad::class,
            LoteInsumo::class,
            HistorialEstadoLote::class,
            CertificacionLote::class,
            AlmacenMovimiento::class,
            RegistroMovimientoMateria::class,
            RegistroProcesoMaquinaPlanta::class,
        ];

        foreach ($modelos as $modelo) {
            if (! class_exists($modelo)) {
                continue;
            }
            $modelo::query()->where('usuarioid', $usuarioid)->update(['usuarioid' => $respaldoId]);
        }

        $distribucionColumnas = [
            'distribucion_salida' => ['operador_usuarioid', 'transportista_usuarioid', 'administrador_usuarioid'],
            'distribucion_ingreso' => ['operador_usuarioid', 'transportista_usuarioid', 'administrador_usuarioid'],
            'distribucion_pedido_almacen' => ['operador_usuarioid', 'transportista_usuarioid', 'administrador_usuarioid'],
        ];

        foreach ($distribucionColumnas as $tabla => $columnas) {
            if (! Schema::hasTable($tabla)) {
                continue;
            }
            foreach ($columnas as $columna) {
                if (! Schema::hasColumn($tabla, $columna)) {
                    continue;
                }
                DB::table($tabla)->where($columna, $usuarioid)->update([$columna => $respaldoId]);
            }
        }
    }
}
