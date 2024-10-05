<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('http://localhost:5173/');
});

// Route::get('/', function () {
//     return to_route('filament.siteman.auth.login');
// });
