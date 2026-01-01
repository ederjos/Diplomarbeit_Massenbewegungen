<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Http\Request;

use App\Models\Location;
use App\Models\MeasurementValue;
use App\Models\Municipality;
use App\Models\Project;
use App\Models\User;

use App\Http\Controllers\ProjectController;

// Create table:
/* ./vendor/bin/sail artisan make:model Municipality -m
app/Models/Municipality.php
database/migrations/xxxx_xx_xx_create_municipalities_table.php

Then edit create_table.php and execute `sail artisan migrate`
*/

/*Route::get('/', function () {
    //$municipalities = Municipality::orderBy('id')->take(50)->get();

    // Alternatives:
    // $municipalities = DB::select('select * from municipalities order by id limit 50'); // Complex queries, think about injection!
    // $municipalities = DB::table('municipalities')->orderBy('id')->take(50)->get();

    // X,Y,Z Werte im EPSG 31254
    // $location = new MeasurementValue();
    // $location->x = 500000;
    // $location->y = 200000;
    // $location->z = 350;
    // $location->save();

    // return Inertia::render('Home');

})->name('home');*/

/* Prompt (Gemini 3 Pro)
 * "please review these vue files and grade them. if you find sensible simplifications or corrections, point them out to me!"
 */
Route::get('/', [ProjectController::class, 'index'])->name('home');

Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project');

/*
Route::post('/municipalities', function(Request $request) {
    $request->validate([
        'name' => 'required|string|max:255'
    ]);

    // Municipality::create(['name' => $request->name]); // Requires fillable to be set

    $municipality = new Municipality();
    $municipality->name = $request->name;
    $municipality->save();

    /* Alternative
    DB::table('municipalities')->insert([
        'name' => 'Wien'
    ]); * /

    return redirect()->route('home');
})->name('municipalities.store');

Route::delete('municipalities/{municipality}', function(Municipality $municipality){
    $municipality->delete();

    return redirect()->route('home');
})->name('municipalities.destroy');
*/