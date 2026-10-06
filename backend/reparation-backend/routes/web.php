<?php

use App\Http\Controllers\Web\RepairWebController;
use App\Http\Controllers\Web\WorkshopWebController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', [RepairWebController::class, 'create'])->name('home');
Route::get('/reparations', [RepairWebController::class, 'index'])->name('reparations.index');
Route::get('/reparations/nouvelle', [RepairWebController::class, 'create'])->name('reparations.create');
Route::get('/reparations/{repairRequest}', [RepairWebController::class, 'show'])->name('reparations.show');
Route::get('/reparations/{repairRequest}/ateliers', [WorkshopWebController::class, 'forRepair'])->name('reparations.workshops');

Route::get('/storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);
    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*');