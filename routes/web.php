<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'entry'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'browserLogin'])->middleware('throttle:login')->name('login.store');
Route::get('/up', fn () => response()->json(['status' => 'ok']))->name('health');
Route::get('/app/preview', [AuthController::class, 'preview'])->name('login.preview');
Route::view('/app/{path?}', 'layouts.master')->middleware('auth')->where('path', '.*')->name('app');
