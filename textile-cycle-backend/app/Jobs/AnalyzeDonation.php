<?php

namespace App\Jobs;

use App\Models\Donation;
use App\Services\DonationAnalyzer;
use App\Services\MatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * 1. L'IA complète les champs manquants du don (si une clé API est configurée).
 * 2. Si catégorie + état sont connus → matching + explications IA.
 *    Sinon → statut « à compléter » : le donateur remplit lui-même (repli manuel).
 *
 * Aucune erreur de l'IA ne bloque le donateur.
 */
class AnalyzeDonation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(public Donation $donation)
    {
    }

    public function handle(DonationAnalyzer $analyzer, MatchingService $matching): void
    {
        $donation = $this->donation->fresh(['photos']);

        if (! $donation || $donation->status !== Donation::PENDING_ANALYSIS) {
            return;
        }

        try {
            $analyzer->analyze($donation);
        } catch (Throwable $e) {
            report($e); // repli manuel ci-dessous
        }

        $donation->refresh();

        if (! $donation->isReadyForMatching()) {
            $donation->update(['status' => Donation::NEEDS_REVIEW]);

            return;
        }

        $matching->run($donation);

        try {
            $analyzer->explainMatches($donation);
        } catch (Throwable $e) {
            report($e); // l'affichage retombe sur les raisons de l'algorithme
        }
    }

    public function failed(Throwable $e): void
    {
        $this->donation->update(['status' => Donation::NEEDS_REVIEW]);
    }
}
