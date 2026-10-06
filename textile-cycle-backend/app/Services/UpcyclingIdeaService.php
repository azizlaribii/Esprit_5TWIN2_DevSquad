<?php

namespace App\Services;

use App\Models\Transformation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * IA générative du module Upcycling : propose des idées de transformation
 * pour un vêtement (jean -> sac, chemise -> coussin...).
 *
 * Utilise la même config que DonationAnalyzer : config('textilecycle.ai').
 * Sans clé API (ou en cas d'erreur), on bascule sur un catalogue local :
 * la démo marche toujours.
 */
class UpcyclingIdeaService
{
    public function enabled(): bool
    {
        return filled(config('textilecycle.ai.key'));
    }

    /** @return array{source:string, ideas:array<int,array<string,mixed>>} */
    public function suggest(string $garment, ?string $etat = null): array
    {
        if ($this->enabled()) {
            try {
                return ['source' => 'ia', 'ideas' => $this->askModel($garment, $etat)];
            } catch (Throwable $e) {
                Log::warning('Upcycling IA indisponible, repli catalogue : ' . $e->getMessage());
            }
        }

        return ['source' => 'catalogue', 'ideas' => $this->fallback($garment)];
    }

    /** @return array<int,array<string,mixed>> */
    protected function askModel(string $garment, ?string $etat): array
    {
        $ai = config('textilecycle.ai');

        $system = 'Tu es un expert en upcycling textile. Tu réponds UNIQUEMENT par un objet JSON, sans texte autour, '
            . 'de la forme {"ideas":[{"titre":"","type_projet":"","description":"","difficulte":"","duree_estimee":"","materiaux":[""]}]}. '
            . 'Donne exactement 3 idées réalistes, en français. '
            . 'type_projet doit être parmi : ' . implode(', ', Transformation::TYPES) . '. '
            . 'difficulte doit être parmi : facile, moyen, avance. '
            . 'description : 2 phrases maximum.';

        $prompt = "Vêtement à transformer : {$garment}." . ($etat ? " État : {$etat}." : '');

        $response = Http::withHeaders([
            'x-api-key'         => $ai['key'],
            'anthropic-version' => $ai['version'],
        ])
            ->acceptJson()
            ->timeout($ai['timeout'])
            ->retry(2, 500, throw: false)
            ->post($ai['url'], [
                'model'      => $ai['model'],
                'max_tokens' => 1200,
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => [['type' => 'text', 'text' => $prompt]]]],
            ]);

        $response->throw();

        $text = $response->json('content.0.text');
        if (! is_string($text) || $text === '') {
            throw new RuntimeException('Réponse IA vide.');
        }

        $start = strpos($text, '{');
        $end   = strrpos($text, '}');
        if ($start === false || $end === false || $end < $start) {
            throw new RuntimeException('Réponse IA sans JSON.');
        }

        $data = json_decode(substr($text, $start, $end - $start + 1), true);
        if (! is_array($data) || empty($data['ideas']) || ! is_array($data['ideas'])) {
            throw new RuntimeException('JSON IA invalide.');
        }

        return $this->normalize($data['ideas']);
    }

    /** @param array<int,mixed> $ideas */
    protected function normalize(array $ideas): array
    {
        return collect($ideas)
            ->filter(fn ($i) => is_array($i) && filled($i['titre'] ?? null))
            ->take(3)
            ->map(fn (array $i) => [
                'titre'         => mb_substr((string) $i['titre'], 0, 120),
                'type_projet'   => in_array($i['type_projet'] ?? null, Transformation::TYPES, true) ? $i['type_projet'] : 'Autre',
                'description'   => mb_substr((string) ($i['description'] ?? ''), 0, 600),
                'difficulte'    => array_key_exists($i['difficulte'] ?? '', Transformation::DIFFICULTES) ? $i['difficulte'] : 'moyen',
                'duree_estimee' => mb_substr((string) ($i['duree_estimee'] ?? ''), 0, 50),
                'materiaux'     => collect((array) ($i['materiaux'] ?? []))->map(fn ($m) => trim((string) $m))->filter()->take(8)->values()->all(),
            ])
            ->values()
            ->all();
    }

    /** @return array<int,array<string,mixed>> */
    protected function fallback(string $garment): array
    {
        $text = mb_strtolower($garment);

        $catalog = [
            ['keys' => ['jean', 'pantalon', 'denim'], 'ideas' => [
                ['Sac tote en jean', 'Sac', 'Découpez les jambes, cousez le bas et utilisez la ceinture comme anse.', 'facile', '2 h', ['Fil épais', 'Machine à coudre', 'Ciseaux']],
                ['Pochette zippée', 'Accessoire', 'Récupérez une poche et son rabat pour créer une trousse.', 'moyen', '1 h 30', ['Fermeture éclair', 'Doublure']],
                ['Tapis en bandes tressées', 'Décoration', 'Coupez des bandes de tissu et tressez-les pour un tapis solide.', 'avance', '6 h', ['Bandes de jean', 'Fil solide', 'Aiguille']],
            ]],
            ['keys' => ['t-shirt', 'tshirt', 'top', 'polo', 'débardeur'], 'ideas' => [
                ['Tote bag sans couture', 'Sac', 'Découpez le col et les manches, nouez le bas en franges.', 'facile', '30 min', ['Ciseaux']],
                ['Housse de coussin', 'Coussin', 'Cousez les deux faces pour une housse de coussin décorative.', 'facile', '1 h', ['Fil', 'Rembourrage']],
                ['Bandeaux et chouchous', 'Accessoire', 'Découpez des bandes de jersey pour des accessoires cheveux.', 'facile', '45 min', ['Élastique', 'Fil']],
            ]],
            ['keys' => ['chemise', 'blouse'], 'ideas' => [
                ['Coussin en chemise', 'Coussin', 'Gardez les boutons comme fermeture de la housse.', 'moyen', '2 h', ['Rembourrage', 'Fil']],
                ['Tablier de cuisine', 'Vêtement', 'Transformez le devant de la chemise en tablier.', 'moyen', '3 h', ['Sangle', 'Fil']],
                ['Sac à pain réutilisable', 'Sac', 'Cousez un sac à cordon avec le tissu des manches.', 'facile', '1 h', ['Cordon']],
            ]],
            ['keys' => ['veste', 'manteau', 'parka', 'blazer'], 'ideas' => [
                ['Sac bandoulière', 'Sac', 'Utilisez la doublure et les poches pour un sac robuste.', 'moyen', '3 h', ['Sangle', 'Fermeture']],
                ['Pouf en tissu', 'Décoration', 'Remplissez des chutes et fermez-les dans une housse solide.', 'avance', '5 h', ['Chutes de tissu', 'Fil épais']],
                ['Porte-clés en tissu', 'Accessoire', 'Découpez le tissu des manches pour de petits accessoires.', 'facile', '45 min', ['Anneau métallique']],
            ]],
            ['keys' => ['robe', 'jupe'], 'ideas' => [
                ['Jupe courte ou top', 'Vêtement', 'Raccourcissez la robe pour obtenir un nouveau vêtement.', 'moyen', '2 h 30', ['Fil', 'Élastique']],
                ['Rideau décoratif', 'Décoration', 'Assemblez de larges bandes pour un rideau léger.', 'moyen', '3 h', ['Anneaux', 'Fil']],
                ['Pochettes en tissu', 'Accessoire', 'Créez plusieurs pochettes à partir des chutes.', 'facile', '1 h', ['Fermeture éclair']],
            ]],
        ];

        $default = [
            ['Housse de coussin', 'Coussin', 'Une housse simple à partir de deux pièces de tissu.', 'facile', '1 h', ['Fil', 'Rembourrage']],
            ['Sac en tissu', 'Sac', 'Un tote bag solide avec anses récupérées.', 'facile', '2 h', ['Fil', 'Ciseaux']],
            ['Lingettes réutilisables', 'Accessoire', 'Des carrés de tissu ourlés pour remplacer les lingettes jetables.', 'facile', '45 min', ['Fil']],
        ];

        $ideas = $default;
        foreach ($catalog as $entry) {
            foreach ($entry['keys'] as $k) {
                if (str_contains($text, $k)) {
                    $ideas = $entry['ideas'];
                    break 2;
                }
            }
        }

        return collect($ideas)->map(fn ($i) => [
            'titre' => $i[0], 'type_projet' => $i[1], 'description' => $i[2],
            'difficulte' => $i[3], 'duree_estimee' => $i[4], 'materiaux' => $i[5],
        ])->all();
    }
}
