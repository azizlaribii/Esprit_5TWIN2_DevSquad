<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\DashboardViewController;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\ArmoireController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\ValorisationController;
/*
|--------------------------------------------------------------------------
| Routes Web â€“ TexTileCycle
|--------------------------------------------------------------------------
*/

// ==================== AUTHENTIFICATION (GÉRÉE PAR FORTIFY) ====================
// Fortify fournit automatiquement les routes :
// GET|POST /login, GET|POST /register, POST /logout, GET|POST /two-factor-challenge

// Alias de rétrocompatibilité si des vues ou liens externes appellent les anciens noms
Route::middleware('guest')->group(function () {
    Route::post('/login', [\Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'store'])->name('login.post');
    Route::post('/register', [\Laravel\Fortify\Http\Controllers\RegisteredUserController::class, 'store'])->name('register.post');


    // Reset mot de passe
    Route::get('/forgot-password',         [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password',        [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}',  [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',         [ResetPasswordController::class, 'reset'])->name('password.update');
});


// ==================== VÉRIFICATION EMAIL ====================
Route::middleware('auth')->group(function () {
    Route::get('/email/verify',            [VerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->name('verification.send');
    Route::post('/email/verify-instant',            [VerificationController::class, 'directVerify'])->name('verification.instant');
});
Route::get('/email/verify/{id}/{hash}',    [VerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// ==================== 2FA ====================
Route::middleware('auth')->group(function () {
    Route::get('/two-factor',             [TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/two-factor/enable',     [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/two-factor/confirm',    [TwoFactorController::class, 'confirm'])->name('2fa.confirm');
    Route::post('/two-factor/disable',    [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::get('/two-factor/challenge',   fn() => redirect()->route('two-factor.login'))->name('2fa.challenge');
});

// ==================== PROFIL UTILISATEUR ====================
Route::middleware(['auth', 'verified', '2fa'])->group(function () {
    Route::get('/profile',             [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile',             [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',    [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',          [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== PAGES PROTÃ‰GÃ‰ES ====================
Route::middleware(['auth', 'verified', '2fa'])->group(function () {

    // Redirection racine & Tableau de bord
    Route::get('/',              [DashboardViewController::class, 'dashboard'])->name('home');
    Route::get('/dashboard',     [DashboardViewController::class, 'dashboard'])->name('dashboard');

    // Pages de suivi et analyse
    Route::get('/statistiques',  [DashboardViewController::class, 'statistiques'])->name('statistiques.index');
    Route::get('/predictions',   [DashboardViewController::class, 'predictions'])->name('predictions.index');

    // Gestion du circuit textile
    Route::post('depots/analyser', [DepotController::class, 'analyser'])->name('depots.analyser');
    Route::resource('depots', DepotController::class);
    Route::resource('valorisations', ValorisationController::class);
    Route::get('/reparations',   [DashboardViewController::class, 'reparations'])->name('reparations.index');
    Route::get('/dons',          [DashboardViewController::class, 'dons'])->name('dons.index');

    // Partenaires
    Route::get('/ateliers',      [DashboardViewController::class, 'ateliers'])->name('ateliers.index');
    Route::get('/associations',  [DashboardViewController::class, 'associations'])->name('associations.index');
    Route::post('/associations', [AssociationController::class, 'store'])->name('associations.store');

    // Marketplace Circulaire
    Route::get('/marketplace/mes-articles',           [MarketplaceController::class, 'mesArticles'])->name('marketplace.mes-articles');
    Route::get('/marketplace/mes-favoris',            [MarketplaceController::class, 'mesFavoris'])->name('marketplace.favoris');
    Route::post('/marketplace/{article}/favori',      [MarketplaceController::class, 'toggleFavori'])->name('marketplace.toggle-favori');
    Route::post('/marketplace/{article}/demande',     [MarketplaceController::class, 'demandeAchat'])->name('marketplace.demande');
    Route::post('/marketplace/{article}/evaluer',       [MarketplaceController::class, 'evaluerVendeur'])->name('marketplace.evaluer');
    Route::post('/marketplace/{article}/toggle-statut', [MarketplaceController::class, 'toggleStatut'])->name('marketplace.toggle-statut');
    Route::post('/marketplace/ameliorer-ia',            [MarketplaceController::class, 'ameliorerDescriptionIA'])->name('marketplace.ameliorer-ia');
    Route::get('/marketplace/recu/{article}',           [MarketplaceController::class, 'telechargerRecu'])->name('marketplace.recu');
    Route::resource('marketplace', MarketplaceController::class)->parameters(['marketplace' => 'article']);

    // Section Armoire
    Route::get('/armoire',                              [ArmoireController::class, 'index'])->name('armoire.index');
    Route::post('/armoire',                             [ArmoireController::class, 'store'])->name('armoire.store');
    Route::post('/armoire/analyser-ia',                 [ArmoireController::class, 'analyserIA'])->name('armoire.analyser-ia');
    Route::patch('/armoire/{vetement}/dossier',         [ArmoireController::class, 'updateDossier'])->name('armoire.update-dossier');
    Route::delete('/armoire/{vetement}',                [ArmoireController::class, 'destroy'])->name('armoire.destroy');

    // ==================== ADMINISTRATION (ADMIN CRUD) ====================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::post('users/{user}/toggle-2fa', [\App\Http\Controllers\Admin\UserController::class, 'toggleTwoFactor'])->name('users.toggle-2fa');

          // ── BackOffice dépôts et valorisations ──
    Route::get('depots', [\App\Http\Controllers\Admin\DepotAdminController::class, 'index'])->name('depots.index');
    Route::get('depots/{depot}', [\App\Http\Controllers\Admin\DepotAdminController::class, 'show'])->name('depots.show');
    Route::post('depots/{depot}/valider', [\App\Http\Controllers\Admin\DepotAdminController::class, 'valider'])->name('depots.valider');
    Route::post('depots/{depot}/refuser', [\App\Http\Controllers\Admin\DepotAdminController::class, 'refuser'])->name('depots.refuser');
    Route::post('depots/{depot}/traiter', [\App\Http\Controllers\Admin\DepotAdminController::class, 'traiter'])->name('depots.traiter');

    Route::get('valorisations', [\App\Http\Controllers\Admin\DepotAdminController::class, 'valorisations'])->name('valorisations.index');
    Route::post('valorisations/{valorisation}/valider', [\App\Http\Controllers\Admin\DepotAdminController::class, 'validerValorisation'])->name('valorisations.valider');
    Route::post('valorisations/{valorisation}/refuser', [\App\Http\Controllers\Admin\DepotAdminController::class, 'refuserValorisation'])->name('valorisations.refuser');
    Route::post('valorisations/{valorisation}/realiser', [\App\Http\Controllers\Admin\DepotAdminController::class, 'realiserValorisation'])->name('valorisations.realiser');
        
    });

    require __DIR__.'/don.php';
    require __DIR__.'/reparation.php';
    require __DIR__.'/upcycling.php';
});
