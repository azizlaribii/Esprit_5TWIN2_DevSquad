<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\DashboardViewController;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\DepotController;

/*
|--------------------------------------------------------------------------
| Routes Web – TexTileCycle
| Héritage Blade : layout maître (layouts/app) + @extends + @include
|--------------------------------------------------------------------------
*/

// ── Redirection racine & Tableau de bord
Route::get('/',              [DashboardViewController::class, 'dashboard'])->name('home');
Route::get('/dashboard',     [DashboardViewController::class, 'dashboard'])->name('dashboard');

// ── Pages de suivi et analyse
Route::get('/statistiques',  [DashboardViewController::class, 'statistiques'])->name('statistiques.index');
Route::get('/predictions',   [DashboardViewController::class, 'predictions'])->name('predictions.index');

// ── Gestion du circuit textile
Route::resource('depots', DepotController::class);
Route::get('/reparations',   [DashboardViewController::class, 'reparations'])->name('reparations.index');
Route::get('/dons',          [DashboardViewController::class, 'dons'])->name('dons.index');

// ── Partenaires
Route::get('/ateliers',      [DashboardViewController::class, 'ateliers'])->name('ateliers.index');
Route::get('/associations',  [DashboardViewController::class, 'associations'])->name('associations.index');
Route::post('/associations', [AssociationController::class, 'store'])->name('associations.store');

// ── Marketplace Circulaire (CRUD + Favoris + Demandes + Évaluations)
Route::get('/marketplace/mes-articles',           [MarketplaceController::class, 'mesArticles'])->name('marketplace.mes-articles');
Route::get('/marketplace/mes-favoris',            [MarketplaceController::class, 'mesFavoris'])->name('marketplace.favoris');
Route::post('/marketplace/{article}/favori',      [MarketplaceController::class, 'toggleFavori'])->name('marketplace.toggle-favori');
Route::post('/marketplace/{article}/demande',     [MarketplaceController::class, 'demandeAchat'])->name('marketplace.demande');
Route::post('/marketplace/{article}/evaluer',     [MarketplaceController::class, 'evaluerVendeur'])->name('marketplace.evaluer');

Route::resource('marketplace', MarketplaceController::class)->parameters([
    'marketplace' => 'article'
]);
require __DIR__.'/don.php';
require __DIR__.'/reparation.php';
require __DIR__.'/upcycling.php';