<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProjectController;

/* Prompt (Gemini 3 Pro)
 * "please review these vue files and grade them. if you find sensible simplifications or corrections, point them out to me!"
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('home');

    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project');
});
