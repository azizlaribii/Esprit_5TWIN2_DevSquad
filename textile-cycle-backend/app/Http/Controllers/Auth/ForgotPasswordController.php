<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email'], [
            'email.required' => 'Veuillez saisir votre adresse e-mail.',
            'email.email'    => 'Adresse e-mail invalide.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Aucun compte trouvé avec cette adresse e-mail.']);
        }

        // Génère le token valide et l'enregistre en base
        $token = Password::broker()->createToken($user);
        $user->sendPasswordResetNotification($token);

        // Lien de réinitialisation sécurisé avec token
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        return back()
            ->with('success', '✅ Le lien de réinitialisation a été généré avec succès.')
            ->with('dev_reset_url', $resetUrl);
    }
}
