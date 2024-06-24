<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dellserver/details/{order}', [App\Services\DellServer\Http\Controllers\ServerController::class, 'view'])
        ->name('dellserver.details.view');
});
