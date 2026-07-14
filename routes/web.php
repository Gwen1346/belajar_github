<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/contacts', [ContactController::class, 'index']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::put('/contacts/{contact}', [ContactController::class, 'update']);
Route::delete('/contacts/{contact}', [ContactController::class, 'destroy']);