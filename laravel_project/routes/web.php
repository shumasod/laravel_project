<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReservationController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('accommodations', AccommodationController::class);
Route::resource('rooms', RoomController::class);
Route::resource('customers', CustomerController::class);
Route::resource('reservations', ReservationController::class);
