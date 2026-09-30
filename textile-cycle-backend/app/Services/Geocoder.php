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
                $response = Http::withHeaders([
                    'User-Agent' => config('app.name', 'TexTileCycle') . ' (don-intelligent)',
                ])->timeout(8)->get('https://nominatim.openstreetmap.org/search', [
                    'q'            => $city,
                    'countrycodes' => $country,
                    'format'       => 'json',
                    'limit'        => 1,
                ]);

                $hit = $response->json('0');

                if (! $response->successful() || ! $hit) {
                    return null;
                }

                return ['lat' => (float) $hit['lat'], 'lng' => (float) $hit['lon']];
            } catch (Throwable $e) {
                report($e);

                return null;
            }
        });
    }
}
