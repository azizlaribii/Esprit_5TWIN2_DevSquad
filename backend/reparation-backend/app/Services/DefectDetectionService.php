<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DefectDetectionService
{
    private const COST_TABLE = [
        'Trou'               => ['min' => 20, 'max' => 35, 'severity' => 'elevee'],
        'Déchirure'          => ['min' => 15, 'max' => 25, 'severity' => 'moyenne'],
        'Tache'              => ['min' => 10, 'max' => 20, 'severity' => 'moyenne'],
        'Fermeture cassée'   => ['min' => 20, 'max' => 40, 'severity' => 'moyenne'],
        'Bouton manquant'    => ['min' => 5,  'max' => 10, 'severity' => 'faible'],
        'Usure'              => ['min' => 10, 'max' => 30, 'severity' => 'faible'],
    ];

    private const REPAIR_SUGGESTIONS = [
        'Trou'             => ['Couture', 'Pièce de renfort', 'Reprisage'],
        'Déchirure'        => ['Couture', 'Renforcement du tissu'],
        'Tache'            => ['Nettoyage spécialisé', 'Détachage pressing'],
        'Fermeture cassée' => ['Remplacement de la fermeture éclair'],
        'Bouton manquant'  => ['Remplacement du bouton', 'Renfort du point d\'attache'],
        'Usure'            => ['Renforcement du tissu', 'Doublure de protection'],
    ];

    private const LOCATIONS = [
        'Col', 'Épaule', 'Manche', 'Poitrine', 'Ventre', 'Dos',
        'Poche', 'Ourlet', 'Genou', 'Entrejambe', 'Ceinture', 'Autre',
    ];

    public function analyze(UploadedFile $photo): array
    {
        $driver = config('services.defect_ai.driver', env('DEFECT_AI_DRIVER', env('AI_SERVICE_URL') ? 'flask' : 'mock'));

        return match ($driver) {
            'openai'      => $this->analyzeWithOpenAi($photo),
            'huggingface' => $this->analyzeWithHuggingFace($photo),
            'flask'       => $this->analyzeWithFlask($photo),
            'gemini'      => $this->analyzeWithGemini($photo),
            default       => $this->analyzePixels($photo),
        };
    }

    private function analyzePixels(UploadedFile $photo): array
    {
        if (! function_exists('imagecreatefromstring')) {
            return $this->fallbackWithoutGd($photo);
        }

        $data = @file_get_contents($photo->getRealPath());
        $image = $data ? @imagecreatefromstring($data) : false;

        if (! $image) {
            return $this->fallbackWithoutGd($photo);
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $gridCols = 12;
        $gridRows = 12;
        $blockW = max(1, (int) floor($width / $gridCols));
        $blockH = max(1, (int) floor($height / $gridRows));

        $blocks = [];
        $totalLum = 0;
        $totalR = 0;
        $totalG = 0;
        $totalB = 0;
        $n = $gridCols * $gridRows;

        for ($row = 0; $row < $gridRows; $row++) {
            for ($col = 0; $col < $gridCols; $col++) {
                $sample = $this->sampleBlock($image, $col * $blockW, $row * $blockH, $blockW, $blockH, $width, $height);
                $blocks[$row][$col] = $sample;
                $totalLum += $sample['lum'];
                $totalR += $sample['r'];
                $totalG += $sample['g'];
                $totalB += $sample['b'];
            }
        }

        imagedestroy($image);

        $avgLum = $totalLum / $n;
        $avgR = $totalR / $n;
        $avgG = $totalG / $n;
        $avgB = $totalB / $n;

        $variance = 0;
        foreach ($blocks as $row) {
            foreach ($row as $b) {
                $variance += ($b['lum'] - $avgLum) ** 2;
            }
        }
        $variance /= $n;
        $stdDev = sqrt($variance);

        $seed = crc32($photo->getClientOriginalName() . $width . 'x' . $height);

        if ($stdDev < 4) {
            return $this->notGarmentResult($seed);
        }

        $best = null;
        foreach ($blocks as $row => $cols) {
            foreach ($cols as $col => $b) {
                $colorDist = sqrt(($b['r'] - $avgR) ** 2 + ($b['g'] - $avgG) ** 2 + ($b['b'] - $avgB) ** 2);
                $darkness = max(0, $avgLum - $b['lum']);
                $score = $colorDist + $darkness * 1.6;

                $isEdge = $row === 0 || $col === 0 || $row === $gridRows - 1 || $col === $gridCols - 1;
                if ($isEdge) {
                    $score *= 0.45;
                }

                if (! $best || $score > $best['score']) {
                    $best = $b + ['row' => $row, 'col' => $col, 'score' => $score];
                }
            }
        }

        $anomalyThreshold = 20;
        if ($best['score'] < $anomalyThreshold) {
            return $this->noDefectResult($seed);
        }

        $confidence = round(min(0.97, max(0.62, $best['score'] / 90)), 2);
        $isDark = $best['lum'] < $avgLum - 14;
        $type = $isDark ? 'Trou' : 'Tache';
        $cost = self::COST_TABLE[$type];

        $locations = ['Genou', 'Manche', 'Col', 'Poche', 'Dos', 'Épaule', 'Ourlet'];
        $locSeed = crc32($photo->getClientOriginalName() . $best['row'] . $best['col']);
        $location = $locations[$locSeed % count($locations)];

        return [
            'verdict'            => 'defaut',
            'defect_type'        => $type,
            'location'           => $location,
            'severity'           => $cost['severity'],
            'suggested_repairs'  => self::REPAIR_SUGGESTIONS[$type],
            'estimated_cost_min' => $cost['min'],
            'estimated_cost_max' => $cost['max'],
            'confidence'         => $confidence,
            'bbox'               => [
                'x' => round($best['col'] / $gridCols * 100, 1),
                'y' => round($best['row'] / $gridRows * 100, 1),
                'width' => round(100 / $gridCols * 1.8, 1),
                'height' => round(100 / $gridRows * 1.8, 1),
            ],
        ];
    }

    private function sampleBlock($image, int $startX, int $startY, int $blockW, int $blockH, int $imgW, int $imgH): array
    {
        $step = max(1, (int) floor(min($blockW, $blockH) / 4));
        $sumR = $sumG = $sumB = $count = 0;

        for ($x = $startX; $x < min($startX + $blockW, $imgW); $x += $step) {
            for ($y = $startY; $y < min($startY + $blockH, $imgH); $y += $step) {
                $rgb = imagecolorat($image, $x, $y);
                $sumR += ($rgb >> 16) & 0xFF;
                $sumG += ($rgb >> 8) & 0xFF;
                $sumB += $rgb & 0xFF;
                $count++;
            }
        }

        $count = max(1, $count);
        $r = $sumR / $count;
        $g = $sumG / $count;
        $b = $sumB / $count;
        $lum = 0.299 * $r + 0.587 * $g + 0.114 * $b;

        return compact('r', 'g', 'b', 'lum');
    }

    private function fallbackWithoutGd(UploadedFile $photo): array
    {
        $seed = crc32($photo->getClientOriginalName() . $photo->getSize());
        mt_srand($seed);
        $bucket = $seed % 100;

        if ($bucket < 12) {
            return $this->notGarmentResult($seed);
        }
        if ($bucket < 40) {
            return $this->noDefectResult($seed);
        }

        $types = array_keys(self::COST_TABLE);
        $type = $types[$seed % count($types)];
        $cost = self::COST_TABLE[$type];

        return [
            'verdict'            => 'defaut',
            'defect_type'        => $type,
            'location'           => 'Zone visible',
            'severity'           => $cost['severity'],
            'suggested_repairs'  => self::REPAIR_SUGGESTIONS[$type],
            'estimated_cost_min' => $cost['min'],
            'estimated_cost_max' => $cost['max'],
            'confidence'         => 0.7,
            'bbox'               => null,
        ];
    }

    private function noDefectResult(int $seed): array
    {
        mt_srand($seed);

        return [
            'verdict'            => 'conforme',
            'defect_type'        => null,
            'location'           => null,
            'severity'           => null,
            'suggested_repairs'  => [],
            'estimated_cost_min' => 0,
            'estimated_cost_max' => 0,
            'confidence'         => round(mt_rand(80, 95) / 100, 2),
            'bbox'               => null,
        ];
    }

    private function notGarmentResult(int $seed): array
    {
        mt_srand($seed);

        return [
            'verdict'            => 'non_vetement',
            'defect_type'        => null,
            'location'           => null,
            'severity'           => null,
            'suggested_repairs'  => [],
            'estimated_cost_min' => null,
            'estimated_cost_max' => null,
            'confidence'         => round(mt_rand(70, 92) / 100, 2),
            'bbox'               => null,
        ];
    }

    // ------------------------------------------------------------------
    // Flask : le modèle Flask classifie, Gemini localise le défaut
    // ------------------------------------------------------------------

    private function analyzeWithFlask(UploadedFile $photo): array
    {
        $url = config('services.flask_ai.url', env('AI_SERVICE_URL', env('FLASK_AI_URL', 'http://127.0.0.1:5000')));

        try {
            $response = Http::timeout(30)
                ->attach('photo', file_get_contents($photo->getRealPath()), $photo->getClientOriginalName())
                ->post("{$url}/analyze");

            if (! $response->successful()) {
                throw new \RuntimeException('Réponse Flask invalide : ' . $response->status());
            }

            $data = $response->json();
            $label = $data['label'] ?? null;
            $confidence = round($data['confidence'] ?? 0.5, 2);

            if (! $label) {
                throw new \RuntimeException('Pas de label renvoyé par le service Flask.');
            }

            if ($label === 'non_vetement') {
                return $this->notGarmentResult(crc32($photo->getClientOriginalName()));
            }

            if ($label === 'conforme') {
                return [
                    'verdict'            => 'conforme',
                    'defect_type'        => null,
                    'location'           => null,
                    'severity'           => null,
                    'suggested_repairs'  => [],
                    'estimated_cost_min' => 0,
                    'estimated_cost_max' => 0,
                    'confidence'         => $confidence,
                    'bbox'               => null,
                ];
            }

            $type = $label;
            $cost = self::COST_TABLE[$type] ?? self::COST_TABLE['Usure'];

            // Vraie localisation par Gemini (au lieu de l'ancienne analyse de pixels,
            // qui plaçait le cadre sur la zone la plus "différente" de l'image).
            $localisation = $this->localizeWithGemini($photo, $type);
            $bbox = $localisation['bbox'] ?? null;
            $location = $localisation['location'] ?? 'Zone visible';

            return [
                'verdict'            => 'defaut',
                'defect_type'        => $type,
                'location'           => $location,
                'severity'           => $cost['severity'],
                'suggested_repairs'  => self::REPAIR_SUGGESTIONS[$type] ?? ['Couture'],
                'estimated_cost_min' => $cost['min'],
                'estimated_cost_max' => $cost['max'],
                'confidence'         => $confidence,
                'bbox'               => $bbox,
            ];
        } catch (\Throwable $e) {
            Log::error('DefectDetectionService (flask) a échoué, repli sur l\'analyse de pixels.', [
                'error' => $e->getMessage(),
            ]);

            return $this->analyzePixels($photo);
        }
    }

    // ------------------------------------------------------------------
    // Gemini
    // ------------------------------------------------------------------

    /**
     * Driver complet : Gemini détecte le défaut ET sa position.
     */
    private function analyzeWithGemini(UploadedFile $photo): array
    {
        $defects = implode(', ', array_keys(self::COST_TABLE));
        $locations = implode(', ', self::LOCATIONS);

        $prompt = <<<PROMPT
        Tu es un expert en réparation textile. Analyse la photo.

        1. Si aucun vêtement n'est visible : verdict = "non_vetement".
        2. Si le vêtement est visible et sans défaut : verdict = "conforme".
        3. Sinon : verdict = "defaut". Choisis le défaut principal parmi : {$defects}.
           - "location" : la zone du vêtement où se trouve le défaut, parmi : {$locations}.
           - "confidence" : ta confiance entre 0 et 1.
           - "box_2d" : cadre SERRÉ autour du défaut lui-même (la tache, le trou, la déchirure...),
             PAS autour du vêtement entier. Format [ymin, xmin, ymax, xmax], entiers de 0 à 1000,
             par rapport à l'image complète.

        Réponds uniquement en JSON.
        PROMPT;

        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'verdict'     => ['type' => 'STRING'],
                'defect_type' => ['type' => 'STRING', 'nullable' => true],
                'location'    => ['type' => 'STRING', 'nullable' => true],
                'confidence'  => ['type' => 'NUMBER', 'nullable' => true],
                'box_2d'      => ['type' => 'ARRAY', 'items' => ['type' => 'INTEGER'], 'nullable' => true],
            ],
            'required' => ['verdict'],
        ];

        try {
            $r = $this->callGemini($photo, $prompt, $schema);
            $verdict = $r['verdict'] ?? 'conforme';
            $confidence = isset($r['confidence'])
                ? round(max(0, min(1, (float) $r['confidence'])), 2)
                : 0.8;

            if ($verdict === 'non_vetement') {
                return [
                    'verdict'            => 'non_vetement',
                    'defect_type'        => null,
                    'location'           => null,
                    'severity'           => null,
                    'suggested_repairs'  => [],
                    'estimated_cost_min' => null,
                    'estimated_cost_max' => null,
                    'confidence'         => $confidence,
                    'bbox'               => null,
                ];
            }

            if ($verdict !== 'defaut') {
                return [
                    'verdict'            => 'conforme',
                    'defect_type'        => null,
                    'location'           => null,
                    'severity'           => null,
                    'suggested_repairs'  => [],
                    'estimated_cost_min' => 0,
                    'estimated_cost_max' => 0,
                    'confidence'         => $confidence,
                    'bbox'               => null,
                ];
            }

            $type = $r['defect_type'] ?? '';
            if (! isset(self::COST_TABLE[$type])) {
                $type = 'Usure';
            }
            $cost = self::COST_TABLE[$type];

            $location = $r['location'] ?? '';
            if (! in_array($location, self::LOCATIONS, true)) {
                $location = 'Zone visible';
            }

            return [
                'verdict'            => 'defaut',
                'defect_type'        => $type,
                'location'           => $location,
                'severity'           => $cost['severity'],
                'suggested_repairs'  => self::REPAIR_SUGGESTIONS[$type],
                'estimated_cost_min' => $cost['min'],
                'estimated_cost_max' => $cost['max'],
                'confidence'         => $confidence,
                'bbox'               => $this->toPercentBbox($r['box_2d'] ?? null),
            ];
        } catch (\Throwable $e) {
            Log::error('DefectDetectionService (gemini) a échoué, repli sur l\'analyse de pixels.', [
                'error' => $e->getMessage(),
            ]);

            return $this->analyzePixels($photo);
        }
    }

    /**
     * Localise un défaut déjà connu (ex: "Tache" détecté par Flask).
     * Renvoie ['bbox' => {x,y,width,height} en %, 'location' => 'Poitrine'] ou [] en cas d'échec.
     * En cas d'échec, on préfère ne PAS afficher de cadre plutôt qu'un cadre faux.
     */
    private function localizeWithGemini(UploadedFile $photo, string $type): array
    {
        $locations = implode(', ', self::LOCATIONS);

        $prompt = <<<PROMPT
        Tu es un expert en réparation textile. Regarde la photo d'un vêtement.
        Un premier modèle a détecté le défaut suivant : « {$type} ».

        Localise ce défaut précisément :
        - "location" : la zone du vêtement concernée, parmi : {$locations}.
        - "box_2d" : cadre SERRÉ autour du défaut lui-même (la tache, le trou, la déchirure...),
          PAS autour du vêtement entier. Format [ymin, xmin, ymax, xmax], entiers de 0 à 1000,
          par rapport à l'image complète.
        Si le défaut n'est pas visible, mets "box_2d" à null.

        Réponds uniquement en JSON.
        PROMPT;

        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'location' => ['type' => 'STRING', 'nullable' => true],
                'box_2d'   => ['type' => 'ARRAY', 'items' => ['type' => 'INTEGER'], 'nullable' => true],
            ],
        ];

        try {
            $r = $this->callGemini($photo, $prompt, $schema);

            $location = $r['location'] ?? '';
            if (! in_array($location, self::LOCATIONS, true)) {
                $location = 'Zone visible';
            }

            return [
                'bbox'     => $this->toPercentBbox($r['box_2d'] ?? null),
                'location' => $location,
            ];
        } catch (\Throwable $e) {
            Log::error('DefectDetectionService (gemini localisation) a échoué.', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Appel générique à Gemini avec réponse JSON structurée.
     */
    private function callGemini(UploadedFile $photo, string $prompt, array $schema): array
    {
        $key = config('services.gemini.key');
        $model = config('services.gemini.model');

        if (! $key || ! $model) {
            throw new \RuntimeException('GEMINI_API_KEY ou GEMINI_MODEL manquant dans .env.');
        }

        $mime = $photo->getMimeType() ?: 'image/jpeg';
        $base64 = base64_encode(file_get_contents($photo->getRealPath()));

        $response = Http::withHeaders(['x-goog-api-key' => $key])
            ->timeout(60)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt],
                        ['inline_data' => ['mime_type' => $mime, 'data' => $base64]],
                    ],
                ]],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $schema,
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Réponse Gemini invalide : ' . $response->status() . ' ' . $response->body());
        }

        // On concatène les parties texte (on ignore les éventuelles parties "réflexion").
        $text = '';
        foreach ($response->json('candidates.0.content.parts', []) as $part) {
            if (isset($part['text']) && empty($part['thought'])) {
                $text .= $part['text'];
            }
        }

        $data = json_decode($text, true);

        if (! is_array($data)) {
            throw new \RuntimeException('Réponse Gemini non JSON : ' . $text);
        }

        return $data;
    }

    /**
     * Gemini renvoie [ymin, xmin, ymax, xmax] sur une échelle 0-1000.
     * Le front attend {x, y, width, height} en % (coin haut-gauche).
     */
    private function toPercentBbox(?array $box): ?array
    {
        if (! $box || count($box) !== 4) {
            return null;
        }

        [$ymin, $xmin, $ymax, $xmax] = array_map('floatval', $box);

        $ymin = max(0, min(1000, $ymin));
        $xmin = max(0, min(1000, $xmin));
        $ymax = max(0, min(1000, $ymax));
        $xmax = max(0, min(1000, $xmax));

        if ($xmax <= $xmin || $ymax <= $ymin) {
            return null;
        }

        return [
            'x'      => round($xmin / 10, 1),
            'y'      => round($ymin / 10, 1),
            'width'  => round(($xmax - $xmin) / 10, 1),
            'height' => round(($ymax - $ymin) / 10, 1),
        ];
    }

    // ------------------------------------------------------------------
    // Hugging Face
    // ------------------------------------------------------------------

    private function analyzeWithHuggingFace(UploadedFile $photo): array
    {
        $apiKey = config('services.huggingface.key', env('HUGGINGFACE_API_KEY'));

        if (! $apiKey) {
            Log::warning('HUGGINGFACE_API_KEY manquante, repli sur l\'analyse de pixels.');
            return $this->analyzePixels($photo);
        }

        try {
            $imageData = file_get_contents($photo->getRealPath());

            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->withBody($imageData, $photo->getMimeType() ?: 'image/jpeg')
                ->post('https://api-inference.huggingface.co/models/Salesforce/blip-image-captioning-large');

            if (! $response->successful()) {
                throw new \RuntimeException('Réponse Hugging Face invalide : ' . $response->status());
            }

            $body = $response->json();
            $caption = strtolower($body[0]['generated_text'] ?? '');

            if ($caption === '') {
                throw new \RuntimeException('Description vide renvoyée par le modèle.');
            }

            return $this->interpretCaption($caption, $photo);
        } catch (\Throwable $e) {
            Log::error('DefectDetectionService (huggingface) a échoué, repli sur l\'analyse de pixels.', [
                'error' => $e->getMessage(),
            ]);

            return $this->analyzePixels($photo);
        }
    }

    private function interpretCaption(string $caption, UploadedFile $photo): array
    {
        $garmentWords = ['shirt', 'sweater', 'jumper', 'jean', 'jeans', 'pants', 'trousers',
            'dress', 'jacket', 'coat', 'skirt', 'sock', 'clothes', 'clothing', 'fabric',
            'sleeve', 'shorts', 'hoodie', 'blouse', 'garment', 'sweatshirt', 'cardigan'];

        $isGarment = false;
        foreach ($garmentWords as $word) {
            if (str_contains($caption, $word)) {
                $isGarment = true;
                break;
            }
        }

        $seed = crc32($caption);

        if (! $isGarment) {
            return $this->notGarmentResult($seed);
        }

        $keywordMap = [
            'Trou'             => ['hole', 'ripped', 'torn hole'],
            'Déchirure'        => ['tear', 'torn', 'rip'],
            'Tache'            => ['stain', 'dirty', 'spot', 'mark'],
            'Fermeture cassée' => ['zipper', 'broken zip'],
            'Bouton manquant'  => ['missing button', 'button'],
            'Usure'            => ['worn', 'frayed', 'old', 'faded', 'damaged'],
        ];

        foreach ($keywordMap as $type => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($caption, $kw)) {
                    $cost = self::COST_TABLE[$type];
                    $locations = ['Genou', 'Manche', 'Col', 'Poche', 'Dos', 'Épaule', 'Ourlet'];
                    $location = $locations[$seed % count($locations)];

                    return [
                        'verdict'            => 'defaut',
                        'defect_type'        => $type,
                        'location'           => $location,
                        'severity'           => $cost['severity'],
                        'suggested_repairs'  => self::REPAIR_SUGGESTIONS[$type],
                        'estimated_cost_min' => $cost['min'],
                        'estimated_cost_max' => $cost['max'],
                        'confidence'         => 0.65,
                        'bbox'               => null,
                    ];
                }
            }
        }

        $pixelResult = $this->analyzePixels($photo);

        if ($pixelResult['verdict'] === 'defaut') {
            $pixelResult['confidence'] = min($pixelResult['confidence'], 0.6);
        }

        return $pixelResult;
    }

    // ------------------------------------------------------------------
    // OpenAI
    // ------------------------------------------------------------------

    private function analyzeWithOpenAi(UploadedFile $photo): array
    {
        $base64 = base64_encode(file_get_contents($photo->getRealPath()));
        $mime = $photo->getMimeType() ?: 'image/jpeg';

        $prompt = <<<PROMPT
        Tu es un expert en couture et réparation textile. Regarde attentivement la photo
        et réponds UNIQUEMENT avec un objet JSON (sans texte autour, sans markdown).

        D'abord détermine "verdict" :
        - "non_vetement" si la photo ne montre pas un vêtement ou accessoire textile.
        - "conforme" si c'est bien un vêtement mais SANS défaut visible.
        - "defaut" si un défaut réel est visible sur le vêtement.

        Format exact :
        {
          "verdict": "defaut|conforme|non_vetement",
          "defect_type": "Trou|Déchirure|Tache|Fermeture cassée|Bouton manquant|Usure|null",
          "location": "zone concernée (ex: Genou, Manche, Col) ou null",
          "severity": "faible|moyenne|elevee|null",
          "suggested_repairs": ["liste de réparations"] (tableau vide si pas de défaut),
          "estimated_cost_min": nombre en dinars tunisiens ou null,
          "estimated_cost_max": nombre en dinars tunisiens ou null,
          "confidence": nombre entre 0 et 1,
          "bbox": {"x": 0-100, "y": 0-100, "width": 0-100, "height": 0-100} ou null
        }

        "bbox" est la zone approximative du défaut, en pourcentage de la largeur/hauteur.
        Ne mets "defect_type", "location", "severity", les coûts que si verdict = "defaut".
        PROMPT;

        try {
            $response = Http::withToken(config('services.openai.key'))
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                ['type' => 'image_url', 'image_url' => [
                                    'url' => "data:{$mime};base64,{$base64}",
                                ]],
                            ],
                        ],
                    ],
                ]);

            $json = $response->json('choices.0.message.content');
            $result = json_decode($json, true);

            if (! is_array($result) || ! isset($result['verdict'])) {
                throw new \RuntimeException('Réponse IA invalide.');
            }

            $result += [
                'defect_type' => null,
                'location' => null,
                'severity' => null,
                'suggested_repairs' => [],
                'estimated_cost_min' => null,
                'estimated_cost_max' => null,
                'bbox' => null,
            ];

            return $result;
        } catch (\Throwable $e) {
            Log::error('DefectDetectionService (openai) a échoué, repli sur l\'analyse de pixels.', [
                'error' => $e->getMessage(),
            ]);

            return $this->analyzePixels($photo);
        }
    }
}