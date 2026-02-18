<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MultiStep;
use App\Http\Controllers\ImportServed;
use App\Http\Controllers\NoShowController;
use App\Http\Controllers\ForceEntryController;
use App\Http\Controllers\ServedController;
use App\Http\Controllers\CleanListController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/home', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'prevent-back-history'])->name('home');

Route::get('/import-files', function () {
    return view('import-files');
})->middleware(['auth', 'verified', 'prevent-back-history'])->name('import-files');

Route::get('/no-show', function () {
    return view('no-show');
})->middleware(['auth', 'verified', 'prevent-back-history'])->name('no-show');

Route::get('/force-entry', function () {
    return view('force-entry');
})->middleware(['auth', 'verified', 'prevent-back-history'])->name('force-entry');

Route::middleware(['auth', 'prevent-back-history'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/submit-form', [MultiStep::class, 'submitForm'])->name('form.submit');
    Route::post('/stop-deduplication', [MultiStep::class, 'stopDeduplication'])->name('form.stop');
    Route::get('/deduplication-status', [MultiStep::class, 'checkDeduplicationStatus'])->name('dedup.status');
    Route::get('/served-benes', [ServedController::class, 'index'])->name('served.benes');
    Route::get('/clean_list', [CleanListController::class, 'index'])->name('clean.list');
    Route::post('/import-form', [ImportServed::class, 'importForm'])->name('form.import');
    Route::post('/import-file', [NoShowController::class, 'importFile'])->name('file.import');
    Route::post('/import-force-entry', [ForceEntryController::class, 'importFile'])->name('upload.csv');
    Route::get('/file/show/{path}/{name}', [MultiStep::class, 'show'])->name('file.show');
});

require __DIR__.'/auth.php';
