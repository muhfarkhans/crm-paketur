<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->middleware(RoleMiddleware::class . ':1,2,3');
    Route::get('/{id}', [UserController::class, 'find'])->middleware(RoleMiddleware::class . ':1,2,3');
    Route::post('/', [UserController::class, 'create'])->middleware(RoleMiddleware::class . ':1,2');
    Route::put('/{id}', [UserController::class, 'update'])->middleware(RoleMiddleware::class . ':1,2');
    Route::delete('/{id}', [UserController::class, 'delete'])->middleware(RoleMiddleware::class . ':1,2');
});

Route::prefix('companies')->middleware(RoleMiddleware::class . ':1')->group(function () {
    Route::get('/', [CompanyController::class, 'index']);
    Route::get('/{id}', [CompanyController::class, 'find']);
    Route::post('/', [CompanyController::class, 'create']);
    Route::put('/{id}', [CompanyController::class, 'update']);
    Route::delete('/{id}', [CompanyController::class, 'delete']);
});
