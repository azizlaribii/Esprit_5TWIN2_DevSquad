<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Géocodage d'une ville via Nominatim (OpenStreetMap).
 *
 * Nominatim impose un User-Agent identifiable et 1 requête/seconde maximum :
 * les résultats sont donc mis en cache 30 jours. Pour un fort trafic, remplacer
 * par un service payant (Google, Mapbox, LocationIQ…) en gardant cette signature.
 */
class Geocoder
{
    /**
     * @return array{lat: float, lng: float}|null
     */
    public function lookup(string $city): ?array
    {
        $city = trim($city);
        if ($city === '') {
            return null;
        }

        $country = (string) config('textilecycle.geocoding_country', 'tn');
        $key     = 'geocode:' . $country . ':' . mb_strtolower($city);

        return Cache::remember($key, now()->addDays(30), function () use ($city, $country) {
            try {
                $headers = [
                    'User-Agent' => config('app.name', 'TexTileCycle') . ' (don-intelligent)',
                ];

                // 1. Essai avec recherche structurée par ville (évite de confondre la ville de Tunis avec le pays Tunisie)
                $response = Http::withHeaders($headers)->timeout(8)->get('https://nominatim.openstreetmap.org/search', [
                    'city'         => $city,
                    'countrycodes' => $country,
                    'format'       => 'json',
                    'limit'        => 3,
                ]);

                $hits = $response->json();

                // 2. Si aucun résultat structuré, repli sur recherche globale
                if (! $response->successful() || empty($hits)) {
                    $response = Http::withHeaders($headers)->timeout(8)->get('https://nominatim.openstreetmap.org/search', [
                        'q'            => $city,
                        'countrycodes' => $country,
                        'format'       => 'json',
                        'limit'        => 5,
                    ]);
                    $hits = $response->json();
                }

                if (! $response->successful() || empty($hits)) {
                    return null;
                }

                // Trouver de préférence un type 'city', 'town', 'village', ou 'municipality'
                $hit = collect($hits)->first(function ($h) {
                    return in_array($h['type'] ?? '', ['city', 'town', 'village', 'municipality', 'suburb'], true);
                }) ?? $hits[0];

                return ['lat' => (float) $hit['lat'], 'lng' => (float) $hit['lon']];
            } catch (Throwable $e) {
                report($e);

                return null;
            }
        });
    }
}
