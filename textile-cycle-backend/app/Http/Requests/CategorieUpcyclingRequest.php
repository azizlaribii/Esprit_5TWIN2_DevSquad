<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategorieUpcyclingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'nom'         => ['required', 'string', 'min:2', 'max:60',
                              Rule::unique('categories_upcycling', 'nom')->ignore($this->route('categorie'))],
            'description' => ['nullable', 'string', 'max:255'],
            'icone'       => ['nullable', 'string', 'max:40', 'regex:/^[a-z0-9_]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'     => 'Le champ :attribute est obligatoire.',
            'min'          => 'Le champ :attribute doit contenir au moins :min caractères.',
            'max'          => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'unique'       => 'Cette catégorie existe déjà.',
            'icone.regex'  => 'L\'icône ne doit contenir que des minuscules, chiffres et underscores (ex. shopping_bag).',
        ];
    }

    public function attributes(): array
    {
        return ['nom' => 'nom', 'description' => 'description', 'icone' => 'icône'];
    }
}
