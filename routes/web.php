<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('https://eklontong.hakimasrori.my.id/');
});

// Route::get('/', function () {
//     return to_route('filament.siteman.auth.login');
// });

Route::prefix('export')->group(function () {
    Route::prefix('pdf')->group(function () {
        Route::get('order/{id}', [OrderController::class, 'exportPdf']);
    });
});
