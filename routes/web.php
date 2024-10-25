<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::post('users/store', [UserController::class, 'store'])->name('users.store');
Route::get('users/show/data/{id}', [UserController::class, 'showData'])->name('users.showData');
Route::get('users/show/{id}', [UserController::class, 'show'])->name('users.show');
Route::put('users/update/{id}', [UserController::class, 'update'])->name('users.update');
Route::delete('users/delete/{id}', [UserController::class, 'destroy'])->name('users.delete');
