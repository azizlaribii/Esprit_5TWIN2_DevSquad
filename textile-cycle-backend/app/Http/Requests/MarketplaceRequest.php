<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarketplaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre'          => 'required|string|min:5|max:120',
            'description'    => 'required|string|min:20|max:2000',
            'categorie'      => 'required|string|max:80',
            'marque'         => 'nullable|string|max:80',
            'taille'         => 'required|string|max:10',
            'genre'          => 'required|in:Homme,Femme,Enfant,Unisexe',
            'etat'           => 'required|string',
            'type'           => 'required|in:vente,echange',
            'prix'           => 'nullable|numeric|min:0|max:9999',
            'article_echange'=> 'nullable|string|max:150',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    /**
     * Get the validation error messages.
     */
    public function messages(): array
    {
        return [
            'titre.required'       => 'Le titre est obligatoire.',
            'titre.min'            => 'Le titre doit contenir au moins 5 caractères.',
            'titre.max'            => 'Le titre ne peut pas dépasser 120 caractères.',
            'description.required' => 'La description est obligatoire.',
            'description.min'      => 'La description doit contenir au moins 20 caractères.',
            'description.max'      => 'La description ne peut pas dépasser 2000 caractères.',
            'categorie.required'   => 'Veuillez sélectionner une catégorie.',
            'taille.required'      => 'Veuillez préciser la taille.',
            'genre.required'       => 'Veuillez sélectionner le genre.',
            'etat.required'        => 'Veuillez préciser l\'état du vêtement.',
            'type.required'        => 'Veuillez choisir un type de transaction.',
            'type.in'              => 'Le type de transaction doit être "vente" ou "echange".',
            'prix.numeric'         => 'Le prix doit être un nombre valide.',
            'prix.min'             => 'Le prix ne peut pas être négatif.',
            'prix.max'             => 'Le prix ne peut pas dépasser 9999.',
            'image.image'          => 'Le fichier doit être une image.',
            'image.mimes'          => 'L\'image doit être au format JPEG, PNG, JPG ou WebP.',
            'image.max'            => 'L\'image ne doit pas dépasser 5 MB.',
        ];
    }
}
