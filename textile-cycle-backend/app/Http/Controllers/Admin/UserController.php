<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs avec filtres et recherche.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Recherche par nom ou email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par rôle
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Filtre par statut 2FA
        if ($twoFactor = $request->input('two_factor')) {
            if ($twoFactor === 'enabled') {
                $query->whereNotNull('two_factor_secret')
                      ->whereNotNull('two_factor_confirmed_at');
            } elseif ($twoFactor === 'disabled') {
                $query->where(function ($q) {
                    $q->whereNull('two_factor_secret')
                      ->orWhereNull('two_factor_confirmed_at');
                });
            }
        }

        $users = $query->latest()->paginate(12)->withQueryString();

        // Statistiques pour les badges du haut
        $stats = [
            'total'        => User::count(),
            'admins'       => User::where('role', 'admin')->count(),
            'particuliers' => User::where('role', 'user')->count(),
            'vendeurs'     => User::where('role', 'vendeur')->count(),
            'acheteurs'    => User::where('role', 'acheteur')->count(),
            'ateliers'     => User::where('role', 'atelier')->count(),
            'associations' => User::where('role', 'association')->count(),
            'two_factor'   => User::whereNotNull('two_factor_confirmed_at')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Formulaire de création d'un utilisateur.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Enregistrer un nouvel utilisateur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', 'string', Rule::in(['admin', 'user', 'vendeur', 'acheteur', 'atelier', 'association'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Le nom est obligatoire.',
            'email.required'     => 'L\'adresse e-mail est obligatoire.',
            'email.unique'       => 'Cette adresse e-mail est déjà attribuée.',
            'role.required'      => 'Veuillez sélectionner un rôle.',
            'role.in'            => 'Le rôle sélectionné est invalide.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'role'              => $validated['role'],
            'password'          => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur « {$user->name} » a été créé avec succès.");
    }

    /**
     * Afficher le profil détaillé d'un utilisateur.
     */
    public function show(User $user)
    {
        // Charger les relations et statistiques de l'utilisateur
        $counts = [
            'depots'       => $user->depots()->count(),
            'articles'     => $user->articlesMarketplace()->count(),
            'armoires'     => $user->vetementsArmoire()->count(),
            'donations'    => method_exists($user, 'donations') ? $user->donations()->count() : 0,
        ];

        return view('admin.users.show', compact('user', 'counts'));
    }

    /**
     * Formulaire d'édition d'un utilisateur.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Mettre à jour un utilisateur.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'     => ['required', 'string', Rule::in(['admin', 'user', 'vendeur', 'acheteur', 'atelier', 'association'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Le nom est obligatoire.',
            'email.required'     => 'L\'adresse e-mail est obligatoire.',
            'email.unique'       => 'Cette adresse e-mail est déjà attribuée.',
            'role.required'      => 'Veuillez sélectionner un rôle.',
            'role.in'            => 'Le rôle sélectionné est invalide.',
            'password.min'       => 'Le nouveau mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Le profil de « {$user->name} » a été mis à jour.");
    }

    /**
     * Supprimer un utilisateur.
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte administrateur.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur « {$name} » a été supprimé.");
    }

    /**
     * Désactiver la double authentification (2FA) d'un utilisateur en cas de perte d'accès.
     */
    public function toggleTwoFactor(User $user, DisableTwoFactorAuthentication $disable)
    {
        $disable($user);

        return back()->with('success', "La double authentification (2FA) de « {$user->name} » a été réinitialisée.");
    }
}

