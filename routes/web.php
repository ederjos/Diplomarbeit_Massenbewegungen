<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MeasurementManagementController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectManagementController;
use App\Http\Controllers\RegistrationRequestController;
use App\Http\Middleware\EnsureAdminPermissions;
use App\Http\Middleware\EnsureMeasurementManagementPermission;
use App\Http\Middleware\EnsureProjectManagementPermission;
use App\Http\Middleware\EnsureProjectMember;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegistrationRequestController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationRequestController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('home');

    // '/projects/create' must be registered before '/projects/{project}' to avoid conflicts with the {project} wildcard
    Route::middleware(EnsureProjectManagementPermission::class)->group(function () {
        Route::get('/projects/create', [ProjectManagementController::class, 'create'])->name('project.create');
        Route::post('/projects', [ProjectManagementController::class, 'store'])->name('project.store');
    });

    Route::middleware(EnsureProjectMember::class)->group(function () {
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project');
        Route::get('/projects/{project}/displacements', [ProjectController::class, 'displacementsForPair'])->name('project.displacements');
        Route::post('projects/{project}/favorite', [ProjectController::class, 'toggleFavorite'])->name('project.toggleFavorite');
        Route::get('/projects/{project}/image', [ProjectController::class, 'image'])->name('project.image');

        Route::middleware(EnsureProjectManagementPermission::class)->group(function () {
            Route::get('/projects/{project}/edit', [ProjectManagementController::class, 'edit'])->name('project.edit');
            Route::put('/projects/{project}', [ProjectManagementController::class, 'update'])->name('project.update');
            Route::delete('/projects/{project}', [ProjectManagementController::class, 'destroy'])->name('project.destroy');
        });

        Route::middleware(EnsureMeasurementManagementPermission::class)->group(function () {
            Route::get('/projects/{project}/measurements/create', [MeasurementManagementController::class, 'create'])->name('project.measurement.create');
            Route::post('/projects/{project}/measurements', [MeasurementManagementController::class, 'store'])->name('project.measurement.store');
            Route::get('/projects/{project}/measurements/{measurement}/edit', [MeasurementManagementController::class, 'edit'])->name('project.measurement.edit');
            Route::put('/projects/{project}/measurements/{measurement}', [MeasurementManagementController::class, 'update'])->name('project.measurement.update');
            Route::delete('/projects/{project}/measurements/{measurement}', [MeasurementManagementController::class, 'destroy'])->name('project.measurement.destroy');
        });
    });

    Route::middleware(EnsureAdminPermissions::class)->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
        Route::post('/admin/registration-requests/{registrationRequest}', [AdminController::class, 'approve'])->name('admin.registration-requests.approve');
        Route::delete('/admin/registration-requests/{registrationRequest}', [AdminController::class, 'reject'])->name('admin.registration-requests.reject');
    });
});
