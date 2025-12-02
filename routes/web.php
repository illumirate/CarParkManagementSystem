<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('zones', App\Http\Controllers\ZoneController::class);

