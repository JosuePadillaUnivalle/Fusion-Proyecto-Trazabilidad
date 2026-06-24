<?php

namespace App\Support;

use App\Models\AsignacionEtapaPlanta;
use App\Models\LoteProduccionPedido;
use App\Models\MaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Models\RegistroProcesoMaquinaPlanta;
use App\Models\Usuario;
use App\Services\NotificacionUsuarioService;
use App\Support\UsuarioRol;
use Illuminate\Support\Facades\DB;

class AsignacionEtapaPlantaService
{
    public function __construct(
        private readonly LoteProduccionTrazabilidadService $trazabilidad,
        private readonly LoteProduccionTransformacionService $transformacion,
        private readonly NotificacionUsuarioService $notificaciones,
    ) {}

    public function puedeAsignar(LoteProduccionPedido $lote): bool
    {
        return ! $this->trazabilidad->transformacionCompleta($lote)
            && $this->transformacion->puedeAsignarNuevaEtapa($lote);
    }

    public function puedeCompletar(AsignacionEtapaPlanta $asignacion): bool
    {
        if (! $asignacion->estaPendiente()) {
            return false;
        }

        $lote = $asignacion->loteProduccion;
        if (! $lote) {
            return false;
        }

        return ! $this->trazabilidad->transformacionCompleta($lote);
    }

    /**
     * @param  array{procesoplantaid:int,maquinaplantaid:int,operador_usuarioid:int,observaciones?:?string}  $data
     */
    public function asignar(LoteProduccionPedido $lote, array $data, Usuario $asignador): AsignacionEtapaPlanta
    {
        if (! $this->puedeAsignar($lote)) {
            throw new \InvalidArgumentException(
                $this->transformacion->mensajeBloqueoAsignacion($lote)
                ?? 'La transformación de este lote ya no admite nuevas asignaciones.'
            );
        }

        $proceso = ProcesoPlanta::query()->findOrFail($data['procesoplantaid']);
        $errorProceso = $this->transformacion->validarProcesoParaAsignar($lote, (int) $proceso->procesoplantaid);
        if ($errorProceso !== null) {
            throw new \InvalidArgumentException($errorProceso);
        }

        if (in_array($proceso->nombre, ['Control de Calidad'], true)) {
            throw new \InvalidArgumentException('«Control de Calidad» corresponde a la fase de certificación, no a transformación.');
        }

        $maquina = MaquinaPlanta::findOrFail($data['maquinaplantaid']);
        if (! MaquinaProcesoCompatibilidad::compatible((int) $data['procesoplantaid'], (int) $data['maquinaplantaid'])) {
            throw new \InvalidArgumentException('La maquinaria no es compatible con el proceso seleccionado.');
        }
        if ($maquina->enMantenimiento()) {
            throw new \InvalidArgumentException('La maquinaria está en mantenimiento.');
        }

        $operador = UsuarioRol::queryOperariosPlanta()
            ->where('usuarioid', $data['operador_usuarioid'])
            ->first();

        if (! $operador || ! UsuarioRol::esOperarioPlanta($operador)) {
            throw new \InvalidArgumentException('El operario seleccionado debe tener rol planta (no jefe de planta).');
        }

        $asignacion = AsignacionEtapaPlanta::create([
            'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
            'procesoplantaid' => $data['procesoplantaid'],
            'maquinaplantaid' => $data['maquinaplantaid'],
            'operador_usuarioid' => $operador->usuarioid,
            'asignado_por_usuarioid' => $asignador->usuarioid,
            'estado' => AsignacionEtapaPlanta::ESTADO_PENDIENTE,
            'observaciones' => $data['observaciones'] ?? null,
            'creado_en' => now(),
        ]);

        $this->notificaciones->etapaPlantaAsignada($asignacion);

        return $asignacion;
    }

    /**
     * @param  array{hora_inicio:string,hora_fin:string}  $data
     */
    /**
     * @param  array{hora_inicio: string, hora_fin: string, parametros?: list<array{variableestandarid: int, valor: float|int|string}>}  $data
     */
    public function completar(AsignacionEtapaPlanta $asignacion, array $data, Usuario $usuario): RegistroProcesoMaquinaPlanta
    {
        if (! $asignacion->estaPendiente()) {
            throw new \InvalidArgumentException('Esta tarea ya fue completada.');
        }

        $esOperador = UsuarioRol::esOperarioPlanta($usuario)
            && (int) $asignacion->operador_usuarioid === (int) $usuario->usuarioid;
        $esSupervisor = UsuarioRol::gestionaPlanta($usuario) || UsuarioRol::esAdminGlobal($usuario);

        if (! $esOperador && ! $esSupervisor) {
            throw new \InvalidArgumentException('No tiene permiso para completar esta tarea.');
        }

        $lote = $asignacion->loteProduccion()->firstOrFail();
        if ($this->trazabilidad->transformacionCompleta($lote)) {
            throw new \InvalidArgumentException('La transformación del lote ya finalizó.');
        }

        $paramService = app(LoteProduccionParametrosService::class);
        $parametrosRegistrados = $paramService->validarYFormatearValoresEtapa(
            $asignacion,
            $data['parametros'] ?? [],
        );

        $registro = DB::transaction(function () use ($asignacion, $data, $lote, $esSupervisor, $usuario, $parametrosRegistrados) {
            $paso = $this->transformacion->resolverPasoProcesoMaquina(
                (int) $asignacion->procesoplantaid,
                (int) $asignacion->maquinaplantaid
            );

            $proceso = $asignacion->proceso;
            $maquina = $asignacion->maquina;
            $observaciones = $asignacion->observaciones;
            if ($esSupervisor && (int) $asignacion->operador_usuarioid !== (int) $usuario->usuarioid) {
                $observaciones = trim(($observaciones ? $observaciones.' ' : '').'(Completada por '.$usuario->nombreCompleto().')');
            }

            $registro = RegistroProcesoMaquinaPlanta::create([
                'procesomaquinaplantaid' => $paso->procesomaquinaplantaid,
                'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
                'usuarioid' => $asignacion->operador_usuarioid,
                'variables_ingresadas' => json_encode([
                    'proceso' => $proceso?->nombre,
                    'maquina' => $maquina?->nombre,
                    'asignacion_id' => $asignacion->asignacionetapaplantaid,
                    'completada_por_supervisor' => $esSupervisor && (int) $asignacion->operador_usuarioid !== (int) $usuario->usuarioid,
                    'parametros' => $parametrosRegistrados,
                ], JSON_UNESCAPED_UNICODE),
                'cumple_estandar' => true,
                'observaciones' => $observaciones,
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'fecha_registro' => $data['hora_fin'],
            ]);

            $asignacion->update([
                'estado' => AsignacionEtapaPlanta::ESTADO_COMPLETADA,
                'registroprocesomaquinaplantaid' => $registro->registroprocesomaquinaplantaid,
                'completada_en' => now(),
            ]);

            if (! $lote->hora_inicio) {
                $lote->update(['hora_inicio' => $data['hora_inicio']]);
            }
            $lote->update(['procesoplantaid' => $asignacion->procesoplantaid]);

            return $registro;
        });

        $this->notificaciones->descartarEtapaPlantaAsignada((int) $asignacion->asignacionetapaplantaid);

        $this->transformacion->limpiarAsignacionesObsoletas($lote->fresh());

        return $registro;
    }

    /**
     * @param  list<array{variableestandarid: int, valor: float|int|string}>  $parametros
     */
    public function completarPorSupervisor(AsignacionEtapaPlanta $asignacion, Usuario $supervisor, array $parametros = []): RegistroProcesoMaquinaPlanta
    {
        if (! UsuarioRol::gestionaPlanta($supervisor) && ! UsuarioRol::esAdminGlobal($supervisor)) {
            throw new \InvalidArgumentException('Solo el jefe de planta o administrador puede completar etapas desde procesamiento.');
        }

        $inicio = $asignacion->creado_en ?? now();

        return $this->completar($asignacion, [
            'hora_inicio' => $inicio->toDateTimeString(),
            'hora_fin' => now()->toDateTimeString(),
            'parametros' => $parametros,
        ], $supervisor);
    }
}
