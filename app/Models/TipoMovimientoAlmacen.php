<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TipoMovimientoAlmacen extends Model
{
    use HasFactory;

    protected $table = 'tipo_movimiento_almacen';

    protected $primaryKey = 'tipo_movimiento_almacenid';

    protected $fillable = [
        'nombre',
        'naturaleza',
        'activo',
    ];

    protected $casts = [
        'tipo_movimiento_almacenid' => 'integer',
        'activo' => 'boolean',
    ];

    public static function normalizeNombre(string $nombre): string
    {
        return Str::ascii(strtolower(trim($nombre)));
    }

    /**
     * @param  Collection<int, self>  $tipos
     * @return Collection<int, self>
     */
    public static function deduplicateByNombre(Collection $tipos): Collection
    {
        $seen = [];

        return $tipos->filter(function (self $tipo) use (&$seen) {
            $key = self::normalizeNombre($tipo->nombre).'|'.$tipo->naturaleza;
            if (isset($seen[$key])) {
                return false;
            }
            $seen[$key] = true;

            return true;
        })->values();
    }

    public static function activosPorNaturaleza(string $naturaleza): Collection
    {
        return self::deduplicateByNombre(
            self::query()
                ->where('activo', true)
                ->where('naturaleza', $naturaleza)
                ->orderBy('nombre')
                ->get()
        );
    }
}
