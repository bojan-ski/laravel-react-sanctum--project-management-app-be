<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsTaskCreatorOrAssigneeMiddleware;

Route::middleware([
    'auth:sanctum',
    IsTaskCreatorOrAssigneeMiddleware::class
])->prefix('messages')->group(function () {
    
});