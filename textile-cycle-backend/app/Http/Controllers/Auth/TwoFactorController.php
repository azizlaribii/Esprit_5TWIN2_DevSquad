<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class TwoFactorController extends Controller
{
    /**
     * Afficher la page de gestion 2FA
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $qrCodeSvg = null;
        $secret = null;

        if (!$user->hasEnabledTwoFactorAuthentication() && $user->two_factor_secret) {
            $qrCodeSvg = $user->twoFactorQrCodeSvg();
            $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
        }

        return view('auth.two-factor', compact('qrCodeSvg', 'secret'));
    }

    /**
     * Générer le secret 2FA avec Fortify (étape 1 : préparer)
     */
    public function enable(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $user = $request->user();

        if (!$user->two_factor_secret) {
            $enable($user);
        }

        return redirect()->route('2fa.show');
    }

    /**
     * Confirmer et activer le 2FA avec Fortify (étape 2 : vérifier le code OTP)
     */
    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();

        try {
            $confirm($user, $request->code);
        } catch (ValidationException $e) {
            return back()->withErrors(['code' => 'Code invalide. Veuillez réessayer.']);
        }

        $recoveryCodes = $user->recoveryCodes();

        return redirect()->route('2fa.show')
            ->with('success', '✅ Authentification à deux facteurs activée avec succès !')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Désactiver le 2FA avec Fortify
     */
    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $request->validate(['password' => 'required|string']);

        if (!Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        $disable($request->user());

        return redirect()->route('2fa.show')
            ->with('success', '🔓 Authentification à deux facteurs désactivée.');
    }

    /**
     * Formulaire de challenge 2FA lors de la connexion
     */
    public function challenge(Request $request)
    {
        return view('auth.two-factor-challenge');
    }

    /**
     * Vérifier le challenge 2FA (code TOTP ou code de récupération) avec Fortify
     */
    public function verifyChallenge(Request $request, TwoFactorAuthenticationProvider $provider)
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();

        if (!$user || !$user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('dashboard');
        }

        $code = trim($request->code);

        // 1. Vérifier le code TOTP avec le provider Fortify (tolérance window = 2)
        $decryptedSecret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
        if ($provider->verify($decryptedSecret, $code)) {
            $request->session()->put('2fa_verified', true);
            return redirect()->intended(route('dashboard'));
        }

        // 2. Vérifier si c'est un code de récupération valide
        foreach ($user->recoveryCodes() as $recoveryCode) {
            if (hash_equals($recoveryCode, $code)) {
                $user->replaceRecoveryCode($recoveryCode);
                $request->session()->put('2fa_verified', true);
                return redirect()->intended(route('dashboard'));
            }
        }

        return back()->withErrors(['code' => 'Code invalide ou expiré.']);
    }
}
