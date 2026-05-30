<?php

namespace App\Support;

use App\Models\Actividad;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Support\EstadoLoteCatalogo;
use Illuminate\Support\Facades\Schema;

/**
 * Sincroniza el estado del lote según el tipo de actividad completada.
 */
class LoteEstadoPorActividad
{
    /** @var array<string, string> tipo de actividad (slug) → nombre de estado en catálogo */
    private const MAPEO = [
        'labranza' => 'planificado',
        'siembra' => 'sembrado',
        'riego' => 'en crecimiento',
        'fumigación' => 'en crecimiento',
        'fumigacion' => 'en crecimiento',
        'fertilización' => 'en crecimiento',
        'fertilizacion' => 'en crecimiento',
        'control de plagas' => 'en crecimiento',
        'cosecha' => 'cosechado',
    ];

    public function estadoParaTipo(?string $tipoNombre): ?string
    {
        if ($tipoNombre === null || trim($tipoNombre) === '') {
            return null;
        }

        $key = mb_strtolower(trim($tipoNombre));

        if (isset(self::MAPEO[$key])) {
            return self::MAPEO[$key];
        }

        if (str_contains($key, 'siembra')) {
            return 'sembrado';
        }
        if (str_contains($key, 'riego') || str_contains($key, 'fumig') || str_contains($key, 'fertil') || str_contains($key, 'plaga')) {
            return 'en crecimiento';
        }
        if (str_contains($key, 'cosecha')) {
            return 'cosechado';
        }
        if (str_contains($key, 'labranza')) {
            return 'planificado';
        }

        return null;
    }

    /**
     * Actualiza el lote si el tipo de actividad implica un nuevo estado.
     *
     * @return string|null Nombre del estado aplicado, o null si no hubo cambio.
     */
    public function aplicarDesdeActividad(Actividad $actividad, ?string $observacionExtra = null): ?string
    {
        $actividad->loadMissing(['lote', 'tipoActividad']);
        $lote = $actividad->lote;
        if (! $lote) {
            return null;
        }

        $nombreEstado = $this->estadoParaTipo($actividad->tipoActividad->nombre ?? null);
        if (! $nombreEstado) {
            return null;
        }

        $obs = $observacionExtra
            ?? "Actividad «{$actividad->tipoActividad->nombre}» completada";

        return $this->aplicarEstado($lote, $nombreEstado, $obs, $actividad->usuarioid ?? $lote->usuarioid);
    }

    public function aplicarEstado(Lote $lote, string $nombreEstado, string $observaciones, ?int $usuarioid = null): ?string
    {
        $nuevoEstado = EstadoLoteTipo::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($nombreEstado))])->first();
        if (! $nuevoEstado) {
            return null;
        }

        if ((int) $lote->estadolotetipoid === (int) $nuevoEstado->estadolotetipoid) {
            return null;
        }

        $lote->estadolotetipoid = $nuevoEstado->estadolotetipoid;
        $lote->fechamodificacion = now();
        $lote->save();

        if (Schema::hasTable('historial_estados_lote') && ! $this->omitirHistorialEvento($nuevoEstado->nombre)) {
            HistorialEstadoLote::create([
                'loteid' => $lote->loteid,
                'estadolotetipoid' => $nuevoEstado->estadolotetipoid,
                'fecha_cambio' => now(),
                'observaciones' => $observaciones,
                'usuarioid' => $usuarioid ?? $lote->usuarioid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $nuevoEstado->nombre;
    }

    public function aplicarCambioManual(Lote $lote, string $slugEstado, string $motivo, ?\App\Models\Usuario $usuario): ?string
    {
        $label = EstadoLoteCatalogo::label($slugEstado);
        $obs = self::formatearObservacionCambio($motivo, $usuario);

        return $this->aplicarEstado($lote, $label, $obs, $usuario?->usuarioid ?? $lote->usuarioid);
    }

    public static function formatearObservacionCambio(string $motivo, ?\App\Models\Usuario $usuario): string
    {
        $nombre = trim(($usuario->nombre ?? '').' '.($usuario->apellido ?? '')) ?: '—';
        $rol = ucfirst($usuario->role ?? 'Usuario');
        $fecha = now()->format('d/m/Y H:i');

        return "Motivo: {$motivo} · Realizado por: {$nombre} ({$rol}) — {$fecha}";
    }

    private function omitirHistorialEvento(?string $nombreEstado): bool
    {
        return EstadoLoteCatalogo::slugFromNombre($nombreEstado) === 'sembrado';
    }

    /** @return array<string, string> */
    public static function leyendaMapeo(): array
    {
        return [
            'Labranza' => 'Planificado',
            'Siembra' => 'Sembrado',
            'Riego / Fumigación / Fertilización' => 'En crecimiento',
            'Cosecha' => 'Cosechado',
        ];
    }
}
