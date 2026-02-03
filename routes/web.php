<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('home');

    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project');

    Route::get('/admin', function () {
        return Inertia::render('Admin');
    })->name('admin');
});
