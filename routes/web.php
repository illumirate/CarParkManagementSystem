<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('zones', App\Http\Controllers\ZoneController::class);

Route::resource('zones.floors', App\Http\Controllers\FloorController::class);

Route::resource('zones.floors.slots', App\Http\Controllers\SlotController::class);

Route::post('zones/{zone}/floors/{floor}/slots/generate', 
            [App\Http\Controllers\SlotController::class, 'generate'])
            ->name('zones.floors.slots.generate');
