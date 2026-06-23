<?php



namespace App\Services;



use App\Models\Actividad;

use App\Models\Lote;

use App\Models\Prioridad;

use App\Models\TipoActividad;

use App\Support\LoteTrazabilidadService;

use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;



class LoteSiembraService

{

    public function __construct(

        private readonly LoteTrazabilidadService $trazabilidad,

        private readonly NotificacionUsuarioService $notificaciones,

    ) {}



    public function asignar(Lote $lote, int $responsableId, ?int $asignadoPorId = null): Actividad

    {

        $tipo = $this->tipoSiembra();

        $faseActual = $this->trazabilidad->resolverFaseActual($lote);



        if ($this->trazabilidad->siembraCompletada($lote)) {

            throw ValidationException::withMessages([

                'siembra' => 'Este lote ya fue sembrado.',

            ]);

        }



        if ($this->trazabilidad->actividadSiembraPendiente($lote) !== null) {

            throw ValidationException::withMessages([

                'siembra' => 'Ya hay una siembra asignada pendiente de realizar.',

            ]);

        }



        if (! $this->trazabilidad->tipoActividadPermitidoEnFase($tipo->nombre, $faseActual)) {

            throw ValidationException::withMessages([

                'siembra' => 'El lote no está en fase de siembra.',

            ]);

        }



        $bloqueo = $this->trazabilidad->mensajeActividadNoPermitida($lote, $tipo->nombre);

        if ($bloqueo !== null) {

            throw ValidationException::withMessages(['siembra' => $bloqueo]);

        }



        $ahora = now();



        $actividad = DB::transaction(function () use ($lote, $tipo, $responsableId, $ahora) {

            $prioridadId = Prioridad::query()->orderBy('prioridadid')->value('prioridadid');

            if (! $prioridadId) {
                $prioridadId = Prioridad::firstOrCreate(['nombre' => 'Media'], ['nombre' => 'Media'])->prioridadid;
            }

            return Actividad::create([

                'loteid' => $lote->loteid,

                'usuarioid' => $responsableId,

                'descripcion' => $tipo->nombre ?? 'Siembra',

                'fechainicio' => $ahora,

                'fechafin' => null,

                'tipoactividadid' => $tipo->tipoactividadid,

                'prioridadid' => $prioridadId,

                'observaciones' => 'Siembra asignada desde trazabilidad del lote.',

            ]);

        });



        if ($asignadoPorId !== null && (int) $asignadoPorId !== $responsableId) {

            $this->notificaciones->actividadAsignada($actividad);

        }



        return $actividad;

    }



    private function tipoSiembra(): TipoActividad

    {

        $existente = TipoActividad::query()

            ->whereRaw('LOWER(TRIM(nombre)) LIKE ?', ['%siembra%'])

            ->orderBy('tipoactividadid')

            ->first();

        if ($existente) {

            return $existente;

        }

        return TipoActividad::updateOrCreate(

            ['nombre' => 'Siembra'],

            ['descripcion' => 'Siembra']

        );

    }

}

