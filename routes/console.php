<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Guardar clima automáticamente todos los días a las 8:00 AM
Schedule::command('clima:guardar')->dailyAt('08:00')->timezone('America/La_Paz');

// Operación agrícola: actividades desde insumos/cosechas + clima por lote + riegos programados
Schedule::command('operacion:sync')->dailyAt('06:30')->timezone('America/La_Paz');
Schedule::command('operacion:sync')->everySixHours();

// Sincronizar envíos pendientes cada 5 minutos
Schedule::command('envios:sincronizar')->everyFiveMinutes();