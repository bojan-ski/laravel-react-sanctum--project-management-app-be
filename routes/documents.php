<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

Route::middleware(['auth:sanctum'])->prefix('documents')->group(function () {
    Route::get('/{document}/download', [DocumentController::class, 'download'])
        ->name('document.download');
    Route::delete('/{document}/destroy', [DocumentController::class, 'destroy'])
        ->name('document.destroy');
});
