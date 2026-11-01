<?php

use App\Http\Controllers\Admin\CategorieUpcyclingController;
use App\Http\Controllers\TransformationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module « Transformation / Upcycling »
| À inclure à la fin de routes/web.php :  require __DIR__.'/upcycling.php';
|--------------------------------------------------------------------------
*/

// ---- FrontOffice : projets de transformation (table 1) ----
Route::middleware('auth')->prefix('upcycling')->group(function () {
    Route::post('/idees', [TransformationController::class, 'suggest'])
        ->middleware('throttle:10,1')
        ->name('transformations.suggest');

    Route::resource('projets', TransformationController::class)
        ->parameters(['projets' => 'transformation'])
        ->names('transformations');
});

// ---- BackOffice : catégories d'upcycling (table 2), réservé à l'admin ----
Route::middleware(['auth', 'role:admin'])->prefix('upcycling/admin')->group(function () {
    Route::resource('categories', CategorieUpcyclingController::class)
        ->except('show')
        ->parameters(['categories' => 'categorie'])
        ->names('admin.categories');
});
