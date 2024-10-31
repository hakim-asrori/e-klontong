<?php

use App\Facades\MessageFixer;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('https://kenshuuexpress.id/');
});

Route::get('/optimize/{command}', function ($command) {
    try {
        Artisan::call($command);

        $output = Artisan::output();

        return MessageFixer::success($output);
    } catch (\Exception $e) {
        return MessageFixer::error($e->getMessage());
    }
});

// Route::get('/', function () {
//     return to_route('filament.siteman.auth.login');
// });

Route::prefix('export')->group(function () {
    Route::prefix('pdf')->group(function () {
        Route::get('order/{id}', [OrderController::class, 'exportPdf']);
    });
});
