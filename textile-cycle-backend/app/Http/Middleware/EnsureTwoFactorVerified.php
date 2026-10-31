<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si Fortify a mis l'authentification en attente du challenge 2FA
        if ($request->session()->has('login.id')) {
            return redirect()->route('two-factor.login');
        }

        $user = $request->user();

        // Si le 2FA est actif sur l'utilisateur mais non vérifié dans la session
        if ($user && $user->two_factor_enabled && $request->session()->has('2fa_pending')) {
            return redirect()->route('two-factor.login');
        }

        return $next($request);
    }
}
