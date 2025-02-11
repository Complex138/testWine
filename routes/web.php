<?php

use App\Http\Controllers\CsvImportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'title' => 'Welcome'
    ]);
});


Route::resource('users', UserController::class);
Route::post('users/import-csv', [CsvImportController::class, 'upload'])->name('users.import-csv');
Route::get('imports', [CsvImportController::class, 'index'])->name('imports.index');
Route::get('imports/{import}', [CsvImportController::class, 'show'])->name('imports.show');

// Route::get('/', function () {
//     return view('welcome');
// });
