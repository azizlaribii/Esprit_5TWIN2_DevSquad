<?php

use App\Http\Controllers\Api\RepairController;
use App\Http\Controllers\Api\WorkshopController;
use Illuminate\Support\Facades\Route;

// Routes Réparations
Route::get('/reparations', [RepairController::class, 'index']);
Route::get('/repairs', [RepairController::class, 'index']);
Route::post('/reparations', [RepairController::class, 'store']);
Route::post('/repairs', [RepairController::class, 'store']);
Route::get('/reparations/{repairRequest}', [RepairController::class, 'show']);
Route::get('/repairs/{repairRequest}', [RepairController::class, 'show']);
Route::delete('/reparations/{repairRequest}', [RepairController::class, 'destroy']);
Route::delete('/repairs/{repairRequest}', [RepairController::class, 'destroy']);

// Routes Ateliers (Workshops) CRUD & Choix
Route::post('/workshops', [WorkshopController::class, 'store']);
Route::get('/workshops/{workshop}', [WorkshopController::class, 'show']);
Route::put('/workshops/{workshop}', [WorkshopController::class, 'update']);
Route::delete('/workshops/{workshop}', [WorkshopController::class, 'destroy']);

Route::get('/reparations/{repairRequest}/ateliers', [WorkshopController::class, 'forRepair']);
Route::get('/repairs/{repairRequest}/workshops', [WorkshopController::class, 'forRepair']);
Route::post('/reparations/{repairRequest}/ateliers/choisir', [WorkshopController::class, 'choose']);
Route::post('/repairs/{repairRequest}/workshop', [WorkshopController::class, 'choose']);