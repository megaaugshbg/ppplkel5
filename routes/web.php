<?php

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [AppController::class, 'showRegister']);
Route::post('/register', [AppController::class, 'storeRegister']);

Route::get('/woman-parking', function () {
    return view('woman-parking');
});

Route::get('/cari-parkir', [\App\Http\Controllers\ParkirController::class, 'index']);