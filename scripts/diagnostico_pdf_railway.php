<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'gd='.(extension_loaded('gd') ? 'yes' : 'no').PHP_EOL;
echo 'dompdf='.(class_exists(\Barryvdh\DomPDF\Facade\Pdf::class) ? 'yes' : 'no').PHP_EOL;

$paths = [
    '/app/storage/app/public',
    '/var/www/storage/app/public',
    storage_path('app/public'),
];
foreach ($paths as $p) {
    echo $p.' exists='.(file_exists($p) ? 'yes' : 'no').' writable='.(is_writable($p) ? 'yes' : 'no').' link='.(is_link($p) ? 'yes' : 'no').PHP_EOL;
}

$id = (int) ($argv[1] ?? 37);
$doc = App\Models\DocumentoEntrega::find($id);
if (! $doc) {
    echo "doc missing\n";
    exit(1);
}

try {
    $ctx = App\Support\DocumentoEntregaArchivo::contextoPdf($doc);
    echo 'ctx_ok lines='.count($ctx['lineasProducto'] ?? []).PHP_EOL;
    $pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('logistica.documentos.pdf.comprobante', $ctx)->setPaper('a4', 'portrait');
    $out = $pdf->output();
    echo 'pdf_bytes='.strlen($out).PHP_EOL;
} catch (Throwable $e) {
    echo 'ERR: '.$e->getMessage().PHP_EOL;
    echo $e->getFile().':'.$e->getLine().PHP_EOL;
}
