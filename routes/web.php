<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Municipality;

// Create table:
/* ./vendor/bin/sail artisan make:model Municipality -m
app/Models/Municipality.php
database/migrations/xxxx_xx_xx_create_municipalities_table.php

Then edit create_table.php and execute `sail artisan migrate`
*/

Route::get('/', function () {
    $municipalities = Municipality::orderBy('id')->take(50)->get();

    return Inertia::render('Home', [
        'municipalities' => $municipalities
    ]);
})->name('home');

Route::post('/municipalities', function(Request $request) {
    $request->validate([
        'name' => 'required|string|max:255'
    ]);

    Municipality::create(['name' => $request->name]);

    return redirect()->route('home');
})->name('municipalities.store');

Route::delete('municipalities/{municipality}', function(Municipality $municipality){
    $municipality->delete();

    return redirect()->route('home');
})->name('municipalities.destroy');
