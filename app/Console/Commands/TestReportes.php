<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Venta;
use App\Models\Produccion;
use App\Models\Insumo;
use App\Models\Actividad;
use Illuminate\Support\Facades\Storage;

class TestReportes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reportes:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera reportes de prueba en PDF para evidencia';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando generación de pruebas de reportes...');

        $outputDir = public_path('reportes_test');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $fechaDesde = now()->startOfYear()->toDateString();
        $fechaHasta = now()->toDateString();

        // 1. Reporte de Ventas
        $this->line('Generando reporte de Ventas...');
        try {
            $datosVentas = Venta::with(['produccion.lote.cultivo', 'unidadMedida'])
                ->limit(20)
                ->orderBy('fechaventa', 'desc')
                ->get();

            $pdf = Pdf::loadView('reportes.pdf.ventas', [
                'datos' => $datosVentas,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta
            ]);
            $pdf->save($outputDir . '/prueba_ventas.pdf');
            $this->info('✔️ Ventas: guardado en public/reportes_test/prueba_ventas.pdf');
        } catch (\Exception $e) {
            $this->error('❌ Error en Ventas: ' . $e->getMessage());
        }

        // 2. Reporte de Producción
        $this->line('Generando reporte de Producción...');
        try {
            $datosProd = Produccion::with(['lote.cultivo', 'unidadMedida'])
                ->limit(20)
                ->orderBy('fechacosecha', 'desc')
                ->get();

            $pdf = Pdf::loadView('reportes.pdf.produccion', [
                'datos' => $datosProd,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta
            ]);
            $pdf->save($outputDir . '/prueba_produccion.pdf');
            $this->info('✔️ Producción: guardado en public/reportes_test/prueba_produccion.pdf');
        } catch (\Exception $e) {
            $this->error('❌ Error en Producción: ' . $e->getMessage());
        }

        // 3. Reporte de Inventario
        $this->line('Generando reporte de Inventario...');
        try {
            $datosInv = Insumo::with(['tipo', 'unidadMedida'])
                ->limit(50)
                ->orderBy('nombre')
                ->get();

            $pdf = Pdf::loadView('reportes.pdf.inventario', [
                'datos' => $datosInv
            ]);
            $pdf->save($outputDir . '/prueba_inventario.pdf');
            $this->info('✔️ Inventario: guardado en public/reportes_test/prueba_inventario.pdf');
        } catch (\Exception $e) {
            $this->error('❌ Error en Inventario: ' . $e->getMessage());
        }

        // 4. Reporte de Actividades
        $this->line('Generando reporte de Actividades...');
        try {
            $datosAct = Actividad::with(['lote', 'tipoActividad', 'usuario'])
                ->limit(20)
                ->orderBy('fechainicio', 'desc')
                ->get();

            $pdf = Pdf::loadView('reportes.pdf.actividades', [
                'datos' => $datosAct,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta
            ]);
            $pdf->save($outputDir . '/prueba_actividades.pdf');
            $this->info('✔️ Actividades: guardado en public/reportes_test/prueba_actividades.pdf');
        } catch (\Exception $e) {
            $this->error('❌ Error en Actividades: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('¡Proceso finalizado! Los archivos están en la carpeta public/reportes_test');
    }
}
