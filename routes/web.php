<?php

use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/program', [ProgramController::class, 'index'])->name('program.index');
Route::get('/program/{slug}', [ProgramController::class, 'show'])->name('program.show');
