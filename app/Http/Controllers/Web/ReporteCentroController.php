<?php



namespace App\Http\Controllers\Web;



use App\Http\Controllers\Controller;

use App\Services\ReporteCentroService;

use App\Support\ReporteCatalogo;

use App\Support\ReporteFiltrosCatalogo;

use Illuminate\Http\Request;

use Illuminate\View\View;



class ReporteCentroController extends Controller

{

    public function index(Request $request, ReporteCentroService $servicio): View

    {

        $user = $request->user();

        abort_unless(ReporteCatalogo::usuarioTieneAcceso($user), 403);



        $items = ReporteCatalogo::paraUsuario($user)->map(function (array $item) use ($servicio) {

            $preview = null;

            $metodo = $item['preview'] ?? null;

            if (is_string($metodo) && method_exists($servicio, $metodo)) {

                $preview = $servicio->{$metodo}();

            }



            return array_merge($item, ['preview' => $preview]);

        });



        return view('reportes.index', [

            'items' => $items,

        ]);

    }



    public function enviosEstado(Request $request, ReporteCentroService $servicio): View

    {

        $item = ReporteCatalogo::find('envios-estado');

        $filtros = ReporteFiltrosCatalogo::extraer($request, $item ?? []);



        return $this->mostrar($request, $servicio, 'envios-estado', function () use ($request, $servicio, $filtros) {

            $periodo = $servicio->resolverPeriodo($request);



            return $servicio->enviosEstado($periodo['desde'], $periodo['hasta'], $filtros);

        });

    }



    public function stockAmbito(Request $request, ReporteCentroService $servicio): View

    {

        $item = ReporteCatalogo::find('stock-ambito');

        $filtros = ReporteFiltrosCatalogo::extraer($request, $item ?? []);



        return $this->mostrar($request, $servicio, 'stock-ambito', function () use ($request, $servicio, $filtros) {

            $ambito = $request->string('ambito')->toString() ?: null;



            return $servicio->stockAmbito($ambito, $filtros);

        });

    }



    public function transportistas(Request $request, ReporteCentroService $servicio): View

    {

        $item = ReporteCatalogo::find('transportistas');

        $filtros = ReporteFiltrosCatalogo::extraer($request, $item ?? []);



        return $this->mostrar($request, $servicio, 'transportistas', function () use ($request, $servicio, $filtros) {

            $periodo = $servicio->resolverPeriodo($request);



            return $servicio->transportistas($periodo['desde'], $periodo['hasta'], $filtros);

        });

    }



    public function trasladosPlantaMayorista(Request $request, ReporteCentroService $servicio): View

    {

        $item = ReporteCatalogo::find('traslados-planta-mayorista');

        $filtros = ReporteFiltrosCatalogo::extraer($request, $item ?? []);



        return $this->mostrar($request, $servicio, 'traslados-planta-mayorista', function () use ($request, $servicio, $filtros) {

            $periodo = $servicio->resolverPeriodo($request);



            return $servicio->trasladosPlantaMayorista($periodo['desde'], $periodo['hasta'], $filtros);

        });

    }



    public function pedidosPdv(Request $request, ReporteCentroService $servicio): View

    {

        $item = ReporteCatalogo::find('pedidos-pdv');

        $filtros = ReporteFiltrosCatalogo::extraer($request, $item ?? []);



        return $this->mostrar($request, $servicio, 'pedidos-pdv', function () use ($request, $servicio, $filtros) {

            $periodo = $servicio->resolverPeriodo($request);



            return $servicio->pedidosPdv($periodo['desde'], $periodo['hasta'], $filtros);

        });

    }



    public function productosTerminados(Request $request, ReporteCentroService $servicio): View

    {

        $item = ReporteCatalogo::find('productos-terminados');

        $filtros = ReporteFiltrosCatalogo::extraer($request, $item ?? []);



        return $this->mostrar($request, $servicio, 'productos-terminados', function () use ($request, $servicio, $filtros) {

            $ambito = $request->string('ambito')->toString() ?: null;



            return $servicio->productosTerminados($ambito, $filtros);

        });

    }



    /**

     * @param  callable(): array<string, mixed>  $resolverDatos

     */

    private function mostrar(Request $request, ReporteCentroService $servicio, string $slug, callable $resolverDatos): View

    {

        $item = ReporteCatalogo::find($slug);

        abort_if($item === null, 404);

        ReporteCatalogo::autorizar($request->user(), $item);



        $datos = $resolverDatos();

        $periodo = $servicio->resolverPeriodo($request);

        $filtrosOpciones = ReporteFiltrosCatalogo::opciones($request->user());

        if ($slug === 'envios-estado') {
            $filtrosOpciones['estados_envio'] = ['' => 'Todos los estados']
                + $servicio->opcionesEstadoEnvioPeriodo($periodo['desde'], $periodo['hasta']);
        }

        return view('reportes.'.$item['vista'], [

            'item' => $item,

            'datos' => $datos,

            'periodo' => $datos['periodo'] ?? $periodo,

            'filtrosCampos' => ReporteFiltrosCatalogo::camposParaItem($item),

            'filtrosOpciones' => $filtrosOpciones,

            'filtrosActivos' => ReporteFiltrosCatalogo::hayActivos($request, $item),

            'filtrosEtiquetas' => ReporteFiltrosCatalogo::etiquetasActivas($request, $item, $filtrosOpciones),

            'sinDatos' => $this->datosVacios($datos, $slug),

        ]);

    }

    /** @param  array<string, mixed>  $datos */
    private function datosVacios(array $datos, string $slug): bool
    {
        return match ($slug) {
            'stock-ambito' => ($datos['detalleAlmacen'] ?? collect())->isEmpty(),
            default => ($datos['tabla'] ?? collect())->isEmpty(),
        };
    }

}


