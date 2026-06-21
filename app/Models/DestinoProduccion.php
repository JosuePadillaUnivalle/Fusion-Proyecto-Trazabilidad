<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DestinoProduccion extends Model
{
    use HasFactory;

    protected $table = 'destinoproduccion';
    protected $primaryKey = 'destinoproduccionid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    protected $hidden = [
        'producciones',
    ];

    // Producciones con este destino
    public function producciones()
    {
        return $this->hasMany(Produccion::class, 'destinoproduccionid', 'destinoproduccionid');
    }

    public static function normalizarNombre(?string $nombre): string
    {
        $n = mb_strtolower(trim((string) $nombre));
        $n = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü'], ['a', 'e', 'i', 'o', 'u', 'u'], $n);

        return $n;
    }

    /** Opciones únicas para filtros (evita duplicados por mayúsculas/minúsculas). */
    public static function opcionesFiltro(): \Illuminate\Support\Collection
    {
        $etiquetas = [
            'almacenamiento' => 'Almacenamiento',
            'venta' => 'Venta',
            'procesamiento' => 'Procesamiento',
            'envio' => 'Envío',
        ];

        return static::query()
            ->orderBy('destinoproduccionid')
            ->get(['destinoproduccionid', 'nombre'])
            ->groupBy(fn (self $d) => static::normalizarNombre($d->nombre))
            ->map(function ($grupo, $clave) use ($etiquetas) {
                $rep = $grupo->first();

                return (object) [
                    'destinoproduccionid' => (int) $rep->destinoproduccionid,
                    'nombre' => $etiquetas[$clave] ?? ucfirst((string) $clave),
                ];
            })
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /** IDs con el mismo nombre normalizado (para filtrar registros duplicados en catálogo). */
    public static function idsEquivalentes(int $destinoproduccionid): array
    {
        $destino = static::find($destinoproduccionid);
        if (! $destino) {
            return [$destinoproduccionid];
        }

        $clave = static::normalizarNombre($destino->nombre);

        return static::query()
            ->get(['destinoproduccionid', 'nombre'])
            ->filter(fn (self $d) => static::normalizarNombre($d->nombre) === $clave)
            ->pluck('destinoproduccionid')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}