<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MultiStep;
use App\Http\Controllers\ImportServed;
use App\Http\Controllers\NoShowController;
use App\Http\Controllers\ForceEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::get('/import-served', function () {
    return view('import-served');
})->middleware(['auth', 'verified'])->name('import-served');

Route::get('/no-show', function () {
    return view('no-show');
})->middleware(['auth', 'verified'])->name('no-show');

Route::get('/force-entry', function () {
    return view('force-entry');
})->middleware(['auth', 'verified'])->name('force-entry');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/submit-form', [MultiStep::class, 'submitForm'])->name('form.submit');
Route::post('/import-form', [ImportServed::class, 'importForm'])->name('form.import');
Route::post('/import-file', [NoShowController::class, 'importFIle'])->name('file.import');
Route::post('/import-force-entry', [ForceEntryController::class, 'importFIle'])->name('upload.csv');
Route::get('/file/show/{path}/{name}', [MultiStep::class, 'show'])->name('file.show');

require __DIR__.'/auth.php';
