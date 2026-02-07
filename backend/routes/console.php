<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic demo data reset (runs daily at midnight)
Schedule::command('app:reset-demo-data')
    ->daily()
    ->at('00:00')
    ->description('Reset demo database and clean up storage files (runs at midnight)')
    ->onSuccess(function () {
        Artisan::call('cache:clear');
    });
