<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsProjectOwnerMiddleware;
use App\Http\Controllers\DocumentController;

Route::middleware([
    'auth:sanctum',
    IsProjectOwnerMiddleware::class
])->prefix('documents')->group(function () {
    Route::delete('/{document}', [DocumentController::class, 'destroy']);
});
