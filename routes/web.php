<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RegistrationRequestController;
use App\Http\Middleware\EnsureAdminPermissions;
use App\Http\Middleware\EnsureProjectMember;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegistrationRequestController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationRequestController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('home');

    Route::middleware(EnsureProjectMember::class)->group(function () {
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project');
        /**
         * GET '/' is the home page — it reads a list. Reusing it as a POST endpoint for a mutation would mix two unrelated concerns on the same URL.
         * /projects/{project}/favorite is scoped to a specific resource (one project), which is the standard RESTful convention.
         */
        Route::post('projects/{project}/favorite', [ProjectController::class, 'toggleFavorite'])->name('project.toggleFavorite');
    });

    Route::middleware(EnsureAdminPermissions::class)->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
        Route::post('/admin/registration-requests/{registrationRequest}/approve', [AdminController::class, 'approve'])->name('admin.registration-requests.approve');
        Route::delete('/admin/registration-requests/{registrationRequest}', [AdminController::class, 'reject'])->name('admin.registration-requests.reject');
    });
});
