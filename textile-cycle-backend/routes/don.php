<?php

use App\Http\Controllers\Admin\AssociationVerificationController;
use App\Http\Controllers\Association\DashboardController;
use App\Http\Controllers\Association\NeedController;
use App\Http\Controllers\Association\ProfileController;
use App\Http\Controllers\Association\RequestController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
| Routes du module « Don intelligent », toutes sous /don-intelligent
| (le chemin /dons est déjà utilisé par la page de statistiques du projet).
| À inclure à la fin de routes/web.php :  require __DIR__.'/don.php';
|
| Rôles du projet : user (donateur), association, admin.
*/

// Le middleware « auth » redirige vers route('login'). Si l'équipe a déjà déclaré ces routes,
// on ne touche à rien ; sinon on branche le WebAuthController existant.
// Route::has() ne voit les noms de routes déjà déclarées qu'après ce rafraîchissement.
Route::getRoutes()->refreshNameLookups();

if (! Route::has('login')) {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
        Route::get('/register', [WebAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [WebAuthController::class, 'register'])->name('register.post');
    });
    Route::match(['get', 'post'], '/logout', [WebAuthController::class, 'logout'])->name('logout');
}

Route::prefix('don-intelligent')->group(function () {

    Route::middleware('auth')->group(function () {

        // Redirige chaque rôle vers son espace.
        Route::get('/', function () {
            $user = request()->user();

            return match (true) {
                $user->isAdmin()       => redirect()->route('admin.associations.index'),
                $user->isAssociation() => redirect()->route('association.dashboard'),
                default                => redirect()->route('donations.index'),
            };
        })->name('don.home');

        // ---- Formulaire de don (accessible à tout utilisateur authentifié) ----
        Route::get('/dons/nouveau', [DonationController::class, 'create'])->name('donations.create');
        Route::post('/dons', [DonationController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('donations.store');

        // ---- Particulier (gestion de ses propres dons) ----------------------
        Route::middleware('role:user')->group(function () {
            Route::get('/dons', [DonationController::class, 'index'])->name('donations.index');
            Route::get('/dons/{donation}', [DonationController::class, 'show'])->name('donations.show');
            Route::get('/dons/{donation}/modifier', [DonationController::class, 'edit'])->name('donations.edit');
            Route::put('/dons/{donation}', [DonationController::class, 'update'])->name('donations.update');
            Route::post('/dons/{donation}/relancer', [DonationController::class, 'rematch'])->name('donations.rematch');
            Route::delete('/dons/{donation}', [DonationController::class, 'destroy'])->name('donations.destroy');

            Route::post('/dons/{donation}/suggestions/{donationMatch}/choisir', [MatchController::class, 'choose'])
                ->name('matches.choose');
        });

        // ---- Association -------------------------------------------------------
        Route::middleware('role:association')->prefix('association')->name('association.')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');

            Route::get('/besoins', [NeedController::class, 'index'])->name('needs.index');
            Route::post('/besoins', [NeedController::class, 'store'])->name('needs.store');
            Route::delete('/besoins/{need}', [NeedController::class, 'destroy'])->name('needs.destroy');

            Route::get('/demandes/{donationMatch}', [RequestController::class, 'show'])->name('requests.show');
            Route::post('/demandes/{donationMatch}/accepter', [RequestController::class, 'accept'])->name('requests.accept');
            Route::post('/demandes/{donationMatch}/refuser', [RequestController::class, 'reject'])->name('requests.reject');
            Route::post('/demandes/{donationMatch}/remis', [RequestController::class, 'complete'])->name('requests.complete');
        });

        // ---- Admin -------------------------------------------------------------
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/associations', [AssociationVerificationController::class, 'index'])->name('associations.index');
            Route::post('/associations/{association}/verifier', [AssociationVerificationController::class, 'toggle'])
                ->name('associations.toggle');
        });
    });
});
