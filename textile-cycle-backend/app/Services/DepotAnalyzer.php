<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DepotAnalyzer
{
    /**
     * Envoie la photo au service IA. Retourne null si le service est indisponible,
     * pour que le formulaire reste utilisable en saisie manuelle.
     */
    public function analyze(UploadedFile $photo): ?array
    {
        try {
            $response = Http::timeout(60)
                ->attach('file', file_get_contents($photo->getRealPath()), $photo->getClientOriginalName())
                ->post(config('services.depot_ai.url') . '/analyze-depot');

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('DepotAnalyzer indisponible : ' . $e->getMessage());
            return null;
        }
    }
}