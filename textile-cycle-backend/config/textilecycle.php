<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module « Don intelligent » : réglages du matching
    |--------------------------------------------------------------------------
    | Les poids doivent totaliser 100. Ils sont normalisés par le service,
    | donc un total différent ne casse rien, mais 100 reste plus lisible.
    */
    'weights' => [
        'need'      => 40, // correspondance avec un besoin actif (type, âge, taille, genre, saison)
        'urgency'   => 15, // urgence du besoin (1 à 5)
        'proximity' => 20, // distance donateur ↔ association
        'condition' => 15, // état des vêtements
        'capacity'  => 10, // capacité restante + taux d'acceptation passé
    ],

    // Au-delà de cette distance, l'association n'est pas proposée.
    'max_distance_km' => (float) env('TEXTILECYCLE_MAX_DISTANCE_KM', 50),

    // Nombre de suggestions présentées au donateur.
    'suggestions' => 3,

    // Anti-abus : nombre maximum de dons créés par un particulier sur 24 h.
    'max_donations_per_day' => (int) env('TEXTILECYCLE_MAX_DONATIONS_PER_DAY', 5),

    // Photos par don.
    'max_photos' => 5,
    'max_photo_kb' => 4096, // l'API Claude refuse les images > 5 Mo

    // Code pays ISO pour le géocodage (Nominatim). « tn » = Tunisie.
    'geocoding_country' => env('TEXTILECYCLE_GEOCODING_COUNTRY', 'tn'),

    /*
    |--------------------------------------------------------------------------
    | IA (API Claude)
    |--------------------------------------------------------------------------
    | Sans clé API, le module fonctionne quand même : le donateur complète
    | lui-même les caractéristiques et le matching (déterministe) tourne.
    */
    'ai' => [
        'key'     => env('ANTHROPIC_API_KEY'),
        'model'   => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
        'url'     => env('ANTHROPIC_API_URL', 'https://api.anthropic.com/v1/messages'),
        'version' => '2023-06-01',
        'timeout' => 45,
    ],
];
