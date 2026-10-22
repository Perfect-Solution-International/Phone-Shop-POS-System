<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/login', 'auth.login')->name('login');
Route::get('/up', fn () => response()->json(['status' => 'ok']))->name('health');
Route::view('/app/{path?}', 'layouts.master')->where('path', '.*')->name('app');
