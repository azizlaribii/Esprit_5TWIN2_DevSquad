<?php

use App\Http\Controllers\TransformationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module « Transformation / Upcycling »
| À inclure à la fin de routes/web.php :  require __DIR__.'/upcycling.php';
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('upcycling')->group(function () {
    Route::post('/idees', [TransformationController::class, 'suggest'])
        ->middleware('throttle:10,1')
        ->name('transformations.suggest');

    Route::resource('projets', TransformationController::class)
        ->parameters(['projets' => 'transformation'])
        ->names('transformations');
});
