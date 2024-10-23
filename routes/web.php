<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('https://eklontong.hakimasrori.my.id/');
});

// Route::get('/', function () {
//     return to_route('filament.siteman.auth.login');
// });
