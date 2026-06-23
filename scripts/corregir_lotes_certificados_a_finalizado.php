<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EstadoLoteTipo;
use App\Models\Lote;
use App\Support\LoteEstadoPorActividad;

$estadoCertificadoId = EstadoLoteTipo::query()
    ->whereRaw('LOWER(TRIM(nombre)) = ?', ['certificado'])
    ->value('estadolotetipoid');

if (! $estadoCertificadoId) {
    echo "No hay estado «Certificado» en la base de datos.\n";
    exit(0);
}

$lotes = Lote::query()
    ->where('estadolotetipoid', $estadoCertificadoId)
    ->whereHas('producciones.almacenamientos')
    ->get();

if ($lotes->isEmpty()) {
    echo "No hay lotes certificados con cosecha ya almacenada.\n";
    exit(0);
}

$estados = app(LoteEstadoPorActividad::class);
$actualizados = 0;

foreach ($lotes as $lote) {
    $nombre = $estados->aplicarEstado(
        $lote,
        'Finalizado',
        'Corrección automática: la cosecha ya ingresó al almacén agrícola.',
        null
    );
    if ($nombre) {
        echo "→ {$lote->nombre} → Finalizado\n";
        $actualizados++;
    }
}

echo "Lotes actualizados: {$actualizados}\n";
