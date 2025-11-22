<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;

Route::get('projects/{project}/points-with-measurements', [ProjectController::class, 'pointsWithMeasurements'])->name('projects.points');
