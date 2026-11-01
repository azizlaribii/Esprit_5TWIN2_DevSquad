<?php

namespace App\Http\Requests;

use App\Models\Valorisation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValorisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'depot_id'        => 'required|exists:depots,id',
            'filiere'         => ['required', Rule::in(array_keys(Valorisation::FILIERES))],
            'statut'          => ['nullable', Rule::in(array_keys(Valorisation::STATUTS))],
            'estimation_prix' => 'nullable|numeric|min:0|max:100000',
            'commentaire'     => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'depot_id.required'      => 'Choisissez un dépôt.',
            'depot_id.exists'        => "Ce dépôt n'existe pas.",
            'filiere.required'       => 'La filière est obligatoire.',
            'filiere.in'             => "La filière choisie n'est pas valide.",
            'statut.required'        => 'Le statut est obligatoire.',
            'estimation_prix.numeric' => 'Le prix doit être un nombre.',
            'estimation_prix.min'    => 'Le prix ne peut pas être négatif.',
            'commentaire.max'        => 'Le commentaire ne doit pas dépasser 1000 caractères.',
        ];
    }
}