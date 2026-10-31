<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Connexion utilisateur (API Sanctum)
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects. Veuillez vérifier votre e-mail et mot de passe.',
            ], 401);
        }

        // Révoquer les anciens tokens de ce device
        $user->tokens()->where('name', 'api-token')->delete();

        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie !',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'role'       => $user->role,
                    'avatar_url' => $user->avatar_url,
                    'created_at' => $user->created_at,
                ],
            ],
        ]);
    }

    /**
     * Inscription nouvel utilisateur
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'nullable|string|in:user,atelier,association',
        ], [
            'name.required'     => 'Le nom est obligatoire.',
            'email.required'    => 'L\'adresse e-mail est obligatoire.',
            'email.unique'      => 'Cette adresse e-mail est déjà utilisée.',
            'password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role ?? 'user',
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Compte créé avec succès ! Veuillez vérifier votre e-mail.',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'role'              => $user->role,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at'        => $user->created_at,
                ],
            ],
        ], 201);
    }

    /**
     * Profil utilisateur connecté
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'role'              => $user->role,
                    'avatar_url'        => $user->avatar_url,
                    'email_verified_at' => $user->email_verified_at,
                    'two_factor_enabled' => $user->two_factor_enabled,
                    'created_at'        => $user->created_at,
                ],
            ],
        ]);
    }

    /**
     * Déconnexion (révocation du token Sanctum)
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Mise à jour du profil via API
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        $validated = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour.',
            'data'    => ['user' => $user->fresh()],
        ]);
    }
}
