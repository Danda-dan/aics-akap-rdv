<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MultiStep;
use App\Http\Controllers\ImportServed;
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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/submit-form', [MultiStep::class, 'submitForm'])->name('form.submit');
Route::post('/import-form', [ImportServed::class, 'importForm'])->name('form.import');
Route::get('/file/show/{path}/{name}', [MultiStep::class, 'show'])->name('file.show');

require __DIR__.'/auth.php';
