<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user with custom validation messages and role.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'role' => ['nullable', 'string', Rule::in(['user', 'atelier', 'association'])],
        ], [
            'name.required' => 'Veuillez renseigner votre nom complet.',
            'name.string'   => 'Le nom doit être une chaîne de caractères valide.',
            'name.max'      => 'Le nom ne doit pas dépasser 255 caractères.',
            'email.required'=> 'L\'adresse e-mail est obligatoire.',
            'email.email'   => 'Veuillez renseigner une adresse e-mail valide.',
            'email.unique'  => 'Cette adresse e-mail est déjà utilisée.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.in'            => 'Le type de compte sélectionné est invalide.',
        ])->validate();

        return User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
            'role'     => $input['role'] ?? 'user',
        ]);
    }
}
