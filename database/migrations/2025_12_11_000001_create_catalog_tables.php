<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Catalogos Simples
        $catalogs = [
            ['table' => 'tipoinsumo',          'pk' => 'tipoinsumoid',          'desc' => false],
            ['table' => 'unidadmedida',        'pk' => 'unidadmedidaid',        'desc' => false],
            ['table' => 'cultivo',             'pk' => 'cultivoid',             'desc' => false],
            ['table' => 'tipoactividad',       'pk' => 'tipoactividadid',       'desc' => true],
            ['table' => 'tipoalmacen',         'pk' => 'tipoalmacenid',         'desc' => true],
            ['table' => 'prioridad',           'pk' => 'prioridadid',           'desc' => false],
            ['table' => 'estadoloteinsumo',    'pk' => 'estadoloteinsumoid',    'desc' => false],
            ['table' => 'estadolote_tipo',     'pk' => 'estadolotetipoid',      'desc' => true], // ✅ AQUÍ
            ['table' => 'destinoproduccion',   'pk' => 'destinoproduccionid',   'desc' => false],
        ];

        foreach ($catalogs as $c) {
            if (!Schema::hasTable($c['table'])) {
                Schema::create($c['table'], function (Blueprint $table) use ($c) {
                    $table->id($c['pk']);
                    $table->string('nombre');
                    if ($c['desc']) $table->text('descripcion')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        $catalogs = [
            'destinoproduccion',
            'estadolote_tipo',
            'estadoloteinsumo',
            'prioridad',
            'tipoalmacen',
            'tipoactividad',
            'cultivo',
            'unidadmedida',
            'tipoinsumo',
        ];

        foreach ($catalogs as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
