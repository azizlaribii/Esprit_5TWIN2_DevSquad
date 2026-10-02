<?php

namespace App\Support;

/**
 * Référentiel textile partagé par les formulaires, la validation,
 * le prompt IA et le matching. Une seule source de vérité.
 */
final class Textile
{
    public const CATEGORIES = [
        'manteau'      => 'Manteaux et vestes',
        'pull'         => 'Pulls et gilets',
        'haut'         => 'Hauts (t-shirts, chemises)',
        'pantalon'     => 'Pantalons et shorts',
        'robe_jupe'    => 'Robes et jupes',
        'chaussures'   => 'Chaussures',
        'accessoires'  => 'Accessoires (écharpes, bonnets…)',
        'linge_maison' => 'Linge de maison',
    ];

    public const AGE_GROUPS = [
        'bebe'   => 'Bébé (0-2 ans)',
        'enfant' => 'Enfant (3-12 ans)',
        'ado'    => 'Adolescent',
        'adulte' => 'Adulte',
    ];

    /** Ordre croissant, utilisé pour détecter des tailles voisines. */
    public const SIZE_ORDER = [
        '0-3m', '3-6m', '6-12m', '1-2a', '3-4a', '5-6a', '7-8a', '9-10a', '11-12a', '13-14a',
        'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL',
    ];

    public const GENDERS = [
        'mixte'  => 'Mixte',
        'femme'  => 'Femme / Fille',
        'homme'  => 'Homme / Garçon',
    ];

    public const SEASONS = [
        'toutes'     => 'Toutes saisons',
        'ete'        => 'Été',
        'hiver'      => 'Hiver',
        'mi_saison'  => 'Mi-saison',
    ];

    public const CONDITIONS = [
        'neuf'      => 'Neuf (étiquette ou jamais porté)',
        'bon'       => 'Bon état',
        'usage'     => 'Usagé mais propre et portable',
        'a_reparer' => 'À réparer',
    ];

    /** Valeur « qualité » d'un état, utilisée dans le score (0-100). */
    public const CONDITION_VALUE = [
        'neuf'      => 100,
        'bon'       => 90,
        'usage'     => 65,
        'a_reparer' => 40,
    ];

    public const MEETING_TYPES = [
        'depot'    => 'Dépôt à l\'association',
        'collecte' => 'Collecte chez le donateur',
    ];

    public static function label(string $group, ?string $key): string
    {
        if ($key === null || $key === '') {
            return '—';
        }

        $map = constant(self::class . '::' . strtoupper($group));

        return $map[$key] ?? $key;
    }

    /** Position d'une taille dans SIZE_ORDER, ou null si inconnue. */
    public static function sizeIndex(?string $size): ?int
    {
        if ($size === null) {
            return null;
        }

        $index = array_search(strtoupper(trim($size)), array_map('strtoupper', self::SIZE_ORDER), true);

        return $index === false ? null : $index;
    }
}
