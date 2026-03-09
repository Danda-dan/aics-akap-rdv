<?php

use App\Http\Controllers\Api\ReceiveAssistanceController;
use Illuminate\Support\Facades\Route;

Route::post('/receive-assistance', [ReceiveAssistanceController::class, 'store']);
