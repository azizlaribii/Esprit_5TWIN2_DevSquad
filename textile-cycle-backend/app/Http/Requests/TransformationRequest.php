<?php

namespace App\Http\Requests;

use App\Models\Transformation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'titre'         => ['required', 'string', 'min:3', 'max:120'],
            'type_projet'   => ['required', Rule::in(Transformation::TYPES)],
            'statut'        => ['required', Rule::in(array_keys(Transformation::STATUTS))],
            // Le dépôt doit appartenir à l'utilisateur connecté
            'depot_id'      => ['nullable', 'integer', Rule::exists('depots', 'id')->where('user_id', $this->user()->id)],
            'description'   => ['nullable', 'string', 'max:2000'],
            'difficulte'    => ['nullable', Rule::in(array_keys(Transformation::DIFFICULTES))],
            'duree_estimee' => ['nullable', 'string', 'max:50'],
            'materiaux'     => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'min'      => 'Le champ :attribute doit contenir au moins :min caractères.',
            'max'      => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'in'       => 'La valeur choisie pour :attribute est invalide.',
            'exists'   => 'Le vêtement sélectionné est introuvable.',
        ];
    }

    public function attributes(): array
    {
        return [
            'titre'         => 'titre',
            'type_projet'   => 'type de projet',
            'statut'        => 'statut',
            'depot_id'      => 'vêtement',
            'description'   => 'description',
            'difficulte'    => 'difficulté',
            'duree_estimee' => 'durée estimée',
            'materiaux'     => 'matériaux',
        ];
    }
}
