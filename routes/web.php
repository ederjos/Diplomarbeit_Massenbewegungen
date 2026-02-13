<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RegistrationRequestController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegistrationRequestController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationRequestController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('home');

    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project');

    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
        Route::post('/admin/registration-requests/{registrationRequest}/approve', [AdminController::class, 'approve'])->name('admin.registration-requests.approve');
        Route::delete('/admin/registration-requests/{registrationRequest}', [AdminController::class, 'reject'])->name('admin.registration-requests.reject');
    });
});
