<?php

use App\Http\Controllers\Web\RepairWebController;
use App\Http\Controllers\Web\WorkshopWebController;
use App\Http\Controllers\API\RepairController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Routes du module « Réparation Intelligente » (Blade + IA)
|--------------------------------------------------------------------------
*/

Route::get('/reparations/nouvelle', [RepairWebController::class, 'create'])->name('reparations.create');

// Route web pour l'analyse IA — accessible via session web + CSRF (pas besoin de token Sanctum)
Route::post('/reparations/analyser-ia', [RepairController::class, 'store'])->name('reparations.analyser-ia');

Route::get('/reparations/{repairRequest}', [RepairWebController::class, 'show'])->name('reparations.show');
Route::get('/reparations/{repairRequest}/ateliers', [WorkshopWebController::class, 'forRepair'])->name('reparations.workshops');

Route::get('/storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);
    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*');
