<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Insumo;
use App\Models\ProduccionAlmacenamiento;
use App\Services\AlmacenCapacidadService;
use App\Support\AlmacenAmbito;
use App\Support\AlmacenPlantaCosechaCatalogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlmacenPlantaCosechaController extends Controller
{
    public function show(
        Request $request,
        Almacen $almacen,
        string $clave,
        AlmacenCapacidadService $capacidadService
    ): View {
        $ctx = $this->contextoCosecha($request, $almacen);

        $lineas = AlmacenPlantaCosechaCatalogo::lineasDetalladas(
            $almacen,
            $clave,
            $capacidadService,
            $ctx['rutaPrefijo']
        );
        abort_unless($lineas->isNotEmpty(), 404);

        $nombre = AlmacenPlantaCosechaCatalogo::etiquetaCultivo($lineas->first()['titulo'] ?? $clave, $clave);
        $kgTotal = (float) $lineas->sum(fn (array $l) => (float) ($l['kg'] ?? 0));

        return view('almacenes.cosecha.show', array_merge($ctx, [
            'almacen' => $almacen,
            'clave' => $clave,
            'nombre' => $nombre,
            'kgTotal' => $kgTotal,
            'lineas' => $lineas,
        ]));
    }

    public function destroyProduccion(
        Request $request,
        Almacen $almacen,
        string $clave,
        ProduccionAlmacenamiento $produccionAlmacenamiento
    ): RedirectResponse {
        $ctx = $this->contextoCosecha($request, $almacen);

        abort_unless(
            (int) $produccionAlmacenamiento->almacenid === (int) $almacen->almacenid,
            404
        );
        abort_unless($produccionAlmacenamiento->fechasalida === null, 404);

        $produccionAlmacenamiento->update(['fechasalida' => now()]);

        return $this->redirigirTrasQuitarEntrada(
            $almacen,
            $clave,
            $ctx['rutaPrefijo']
        );
    }

    public function destroyRecepcion(
        Request $request,
        Almacen $almacen,
        string $clave,
        Insumo $insumo
    ): RedirectResponse {
        $ctx = $this->contextoCosecha($request, $almacen);

        abort_unless((int) $insumo->almacenid === (int) $almacen->almacenid, 404);
        abort_unless(AlmacenPlantaCosechaCatalogo::esRecepcionPedidoInsumo($insumo), 404);

        $insumo->delete();

        return $this->redirigirTrasQuitarEntrada(
            $almacen,
            $clave,
            $ctx['rutaPrefijo']
        );
    }

    /**
     * @return array{rutaPrefijo: string, ambito: string, tituloModulo: string}
     */
    private function contextoCosecha(Request $request, Almacen $almacen): array
    {
        $ctx = AlmacenAmbito::contexto($request);
        $ambito = (string) ($ctx['ambito'] ?? '');
        abort_unless(in_array($ambito, [AlmacenAmbito::PLANTA, AlmacenAmbito::AGRICOLA], true), 404);
        abort_unless(($almacen->ambito ?? '') === $ambito, 404);

        return $ctx;
    }

    private function redirigirTrasQuitarEntrada(
        Almacen $almacen,
        string $clave,
        string $rutaPrefijo
    ): RedirectResponse {
        $capacidad = app(AlmacenCapacidadService::class);
        $quedan = AlmacenPlantaCosechaCatalogo::lineasDetalladas($almacen, $clave, $capacidad, $rutaPrefijo)->isNotEmpty();

        if ($quedan) {
            return redirect()->route($rutaPrefijo.'.cosecha.show', [$almacen, $clave]);
        }

        return redirect()->route($rutaPrefijo.'.show', $almacen);
    }
}
