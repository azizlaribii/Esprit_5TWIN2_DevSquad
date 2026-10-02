<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\StatistiqueController;
use App\Http\Controllers\API\PredictionController;

/*
|--------------------------------------------------------------------------
| API Routes - TexTileCycle
|--------------------------------------------------------------------------
*/

// ==================== AUTHENTICATION ====================
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ==================== DASHBOARD ====================
Route::prefix('dashboard')->group(function () {
    Route::get('/overview', [DashboardController::class, 'overview']);
    Route::get('/kpis', [DashboardController::class, 'kpis']);
    Route::get('/recent-activity', [DashboardController::class, 'recentActivity']);
    Route::get('/alerts', [DashboardController::class, 'alerts']);
});

// ==================== STATISTIQUES ====================
Route::prefix('statistiques')->group(function () {
    Route::get('/depots', [StatistiqueController::class, 'depots']);
    Route::get('/reparations', [StatistiqueController::class, 'reparations']);
    Route::get('/dons', [StatistiqueController::class, 'dons']);
    Route::get('/transformations', [StatistiqueController::class, 'transformations']);
    Route::get('/categories', [StatistiqueController::class, 'categories']);
    Route::get('/evolution-mensuelle', [StatistiqueController::class, 'evolutionMensuelle']);
    Route::get('/repartition-par-type', [StatistiqueController::class, 'repartitionParType']);
    Route::get('/top-ateliers', [StatistiqueController::class, 'topAteliers']);
    Route::get('/top-associations', [StatistiqueController::class, 'topAssociations']);
    Route::get('/impact-ecologique', [StatistiqueController::class, 'impactEcologique']);
    Route::get('/par-region', [StatistiqueController::class, 'parRegion']);
    Route::get('/utilisateurs-actifs', [StatistiqueController::class, 'utilisateursActifs']);
});

// ==================== PRÉDICTIONS IA ====================
Route::prefix('predictions')->group(function () {
    Route::get('/depots', [PredictionController::class, 'predictDepots']);
    Route::get('/reparations', [PredictionController::class, 'predictReparations']);
    Route::get('/dons', [PredictionController::class, 'predictDons']);
    Route::get('/tendances', [PredictionController::class, 'tendances']);
    Route::get('/recommandations', [PredictionController::class, 'recommandations']);
    Route::get('/anomalies', [PredictionController::class, 'detecterAnomalies']);
    Route::get('/categories-populaires', [PredictionController::class, 'categoriesPopulaires']);
    Route::get('/rapport-complet', [PredictionController::class, 'rapportComplet']);
});

// ==================== RÉPARATIONS & ATELIERS (IA & CRUD) ====================
Route::get('/repairs', [\App\Http\Controllers\Api\RepairController::class, 'index']);
Route::post('/repairs', [\App\Http\Controllers\Api\RepairController::class, 'store']);
Route::get('/repairs/{repairRequest}', [\App\Http\Controllers\Api\RepairController::class, 'show']);
Route::delete('/repairs/{repairRequest}', [\App\Http\Controllers\Api\RepairController::class, 'destroy']);

Route::post('/workshops', [\App\Http\Controllers\Api\WorkshopController::class, 'store']);
Route::get('/workshops/{workshop}', [\App\Http\Controllers\Api\WorkshopController::class, 'show']);
Route::put('/workshops/{workshop}', [\App\Http\Controllers\Api\WorkshopController::class, 'update']);
Route::delete('/workshops/{workshop}', [\App\Http\Controllers\Api\WorkshopController::class, 'destroy']);

Route::get('/repairs/{repairRequest}/workshops', [\App\Http\Controllers\Api\WorkshopController::class, 'forRepair']);
Route::post('/repairs/{repairRequest}/workshop', [\App\Http\Controllers\Api\WorkshopController::class, 'choose']);
Route::post('/reparations/{repairRequest}/ateliers/choisir', [\App\Http\Controllers\Api\WorkshopController::class, 'choose']);

