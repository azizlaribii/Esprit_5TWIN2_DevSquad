<?php

namespace App\Support;

/**
 * Messages d'erreur de validation en français, passés explicitement à chaque validate()
 * du module (ils s'affichent champ par champ avec @error dans les vues).
 */
class ValidationMessages
{
    /** @return array<string, mixed> */
    public static function messages(): array
    {
        return [
            'required'       => 'Le champ :attribute est obligatoire.',
            'string'         => 'Le champ :attribute doit être un texte.',
            'integer'        => 'Le champ :attribute doit être un nombre entier.',
            'numeric'        => 'Le champ :attribute doit être un nombre.',
            'date'           => 'Le champ :attribute doit être une date valide.',
            'in'             => 'La valeur choisie pour :attribute n\'est pas valide.',
            'array'          => 'Le champ :attribute est invalide.',
            'image'          => 'Le fichier :attribute doit être une image.',
            'mimes'          => 'Le fichier :attribute doit être de type : :values.',
            'after'          => 'Le champ :attribute doit être une date postérieure à maintenant.',
            'after_or_equal' => 'Le champ :attribute ne peut pas être dans le passé.',
            'max' => [
                'string'  => 'Le champ :attribute ne doit pas dépasser :max caractères.',
                'numeric' => 'Le champ :attribute ne doit pas être supérieur à :max.',
                'file'    => 'Le fichier :attribute ne doit pas dépasser :max Ko.',
                'array'   => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
            ],
            'min' => [
                'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
                'numeric' => 'Le champ :attribute doit être au moins égal à :min.',
                'file'    => 'Le fichier :attribute doit faire au moins :min Ko.',
                'array'   => 'Veuillez ajouter au moins :min élément(s) pour :attribute.',
            ],
            'between' => [
                'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
                'string'  => 'Le champ :attribute doit contenir entre :min et :max caractères.',
                'file'    => 'Le fichier :attribute doit faire entre :min et :max Ko.',
                'array'   => 'Le champ :attribute doit contenir entre :min et :max éléments.',
            ],
        ];
    }

    /** @return array<string, string> */
    public static function attributes(): array
    {
        return [
            'title'               => 'titre',
            'description'         => 'description',
            'quantity'            => 'quantité',
            'city'                => 'ville',
            'lat'                 => 'latitude',
            'lng'                 => 'longitude',
            'category'            => 'catégorie',
            'age_group'           => 'public',
            'size'                => 'taille',
            'gender'              => 'genre',
            'season'              => 'saison',
            'condition'           => 'état',
            'photos'              => 'photos',
            'photos.*'            => 'photo',
            'quantity_needed'     => 'quantité nécessaire',
            'urgency'             => 'urgence',
            'expires_at'          => 'date d\'expiration',
            'name'                => 'nom',
            'capacity'            => 'capacité',
            'opening_hours'       => 'horaires',
            'phone'               => 'téléphone',
            'accepted_conditions' => 'états acceptés',
            'accepted_categories' => 'catégories acceptées',
            'meeting_type'        => 'type de remise',
            'meeting_at'          => 'date de remise',
            'meeting_note'        => 'précisions',
        ];
    }
}
