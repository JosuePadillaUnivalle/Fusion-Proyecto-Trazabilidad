<?php

namespace Database\Seeders;

use App\Models\CertificacionLote;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de demostración para el módulo Certificaciones.
 * Ejecutar: php artisan db:seed --class=CertificacionesModuloSeeder
 */
class CertificacionesModuloSeeder extends Seeder
{
    private const MARK = '[MOD-CERT]';

    private const ADMIN_EMAIL = 'admin@agronexus.com';

    public function run(): void
    {
        if (! Schema::hasTable('certificacion_lote')) {
            $this->command?->warn('Omitido: tabla certificacion_lote no existe.');

            return;
        }

        $admin = Usuario::where('email', self::ADMIN_EMAIL)->first();
        if (! $admin) {
            $this->command?->error('No existe admin '.self::ADMIN_EMAIL.'. Ejecute DatosPruebaSeeder.');

            return;
        }

        if (Lote::count() === 0) {
            $this->command?->warn('No hay lotes. Ejecute LotesActividadesModuloSeeder primero.');
            $this->call(LotesActividadesModuloSeeder::class);
        }

        $estadoCertificado = EstadoLoteTipo::firstOrCreate(
            ['nombre' => 'Certificado'],
            ['descripcion' => 'Lote validado para despacho y trazabilidad']
        );

        $certificaciones = [
            [
                'codigo' => 'CERT-LOTE-A1-2026',
                'lote' => 'Lote Norte A1',
                'observaciones' => 'Cumple trazabilidad completa y controles de calidad para tomate.',
                'fecha' => now()->subDays(12)->format('Y-m-d H:i:s'),
                'marcar_lote_certificado' => true,
            ],
            [
                'codigo' => 'CERT-LOTE-B2-2026',
                'lote' => 'Lote Este B2',
                'observaciones' => 'Certificación emitida para cosecha de papa destinada a almacén.',
                'fecha' => now()->subDays(8)->format('Y-m-d H:i:s'),
                'marcar_lote_certificado' => true,
            ],
            [
                'codigo' => 'CERT-LOTE-C3-2026',
                'lote' => 'Lote Sur C3',
                'observaciones' => 'Lechuga certificada para pedidos locales y restaurantes.',
                'fecha' => now()->subDays(5)->format('Y-m-d H:i:s'),
                'marcar_lote_certificado' => true,
            ],
        ];

        $lotesSinCertificar = ['Lote Central D4', 'Lote Oeste E5'];

        DB::transaction(function () use ($certificaciones, $admin, $estadoCertificado, $lotesSinCertificar) {
            foreach ($certificaciones as $def) {
                $lote = Lote::where('nombre', $def['lote'])->first();
                if (! $lote) {
                    $this->command?->warn(self::MARK." Lote «{$def['lote']}» no encontrado.");

                    continue;
                }

                CertificacionLote::updateOrCreate(
                    ['codigo_certificado' => $def['codigo']],
                    [
                        'loteid' => $lote->loteid,
                        'usuarioid' => $admin->usuarioid,
                        'observaciones' => self::MARK.' '.$def['observaciones'],
                        'fecha_certificacion' => $def['fecha'],
                    ]
                );

                if ($def['marcar_lote_certificado']) {
                    $lote->update(['estadolotetipoid' => $estadoCertificado->estadolotetipoid]);

                    if (Schema::hasTable('historial_estados_lote')) {
                        HistorialEstadoLote::updateOrCreate(
                            ['observaciones' => self::MARK.' historial · '.$def['codigo']],
                            [
                                'loteid' => $lote->loteid,
                                'estadolotetipoid' => $estadoCertificado->estadolotetipoid,
                                'fecha_cambio' => $def['fecha'],
                                'usuarioid' => $admin->usuarioid,
                            ]
                        );
                    }
                }
            }

            foreach ($lotesSinCertificar as $nombre) {
                $lote = Lote::where('nombre', $nombre)->first();
                if (! $lote) {
                    continue;
                }
                if (CertificacionLote::where('loteid', $lote->loteid)->exists()) {
                    continue;
                }
                $this->command?->line(self::MARK." «{$nombre}» queda pendiente de certificar en la UI.");
            }
        });

        $totalMod = CertificacionLote::where('observaciones', 'like', self::MARK.'%')->count();
        $pendientes = Lote::whereNotIn('loteid', CertificacionLote::select('loteid'))->count();

        $this->command?->info(sprintf(
            '%s Listo: %d certificados del módulo, %d total en BD, %d lotes aún sin certificar.',
            self::MARK,
            $totalMod,
            CertificacionLote::count(),
            $pendientes
        ));
    }
}
