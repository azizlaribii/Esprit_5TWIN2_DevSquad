<?php

namespace App\Http\Controllers\Association;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationMatch;
use App\Support\Textile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Backoffice CRUD des dons pour les associations.
 *
 * Une association peut :
 *  - Lister tous les dons qui lui ont été proposés (toutes suggestions).
 *  - Voir le détail d'un don.
 *  - Modifier le statut d'un match (accepter / refuser / marquer remis).
 *  - Supprimer (rejeter) définitivement un match suggéré.
 */
class DonationCrudController extends Controller
{
    /** Liste paginée de tous les dons liés à l'association. */
    public function index(Request $request): View
    {
        $association = $request->user()->association;

        if (! $association) {
            return view('don.association.donations.index', [
                'association' => null,
                'matches'     => collect(),
                'stats'       => [],
                'statusFilter' => null,
            ]);
        }

        $statusFilter = $request->get('statut');

        $query = $association->matches()
            ->with(['donation.photos', 'donation.user', 'need'])
            ->latest('updated_at');

        if ($statusFilter && array_key_exists($statusFilter, DonationMatch::STATUS_LABELS)) {
            $query->where('status', $statusFilter);
        }

        $matches = $query->paginate(12)->withQueryString();

        // Stats globales pour les cartes résumé
        $all = $association->matches();
        $stats = [
            'total'     => (clone $all)->count(),
            'pending'   => (clone $all)->where('status', DonationMatch::REQUESTED)->count(),
            'accepted'  => (clone $all)->where('status', DonationMatch::ACCEPTED)->count(),
            'completed' => (clone $all)->where('status', DonationMatch::COMPLETED)->count(),
            'pieces'    => (int) (clone $all)->where('status', DonationMatch::COMPLETED)->sum('quantity'),
        ];

        return view('don.association.donations.index', compact('association', 'matches', 'stats', 'statusFilter'));
    }

    /** Détail d'un don (lecture seule pour l'association). */
    public function show(Request $request, DonationMatch $match): View
    {
        $association = $request->user()->association;
        abort_unless($association && $match->association_id === $association->id, 403);

        $match->load(['donation.photos', 'donation.user', 'need', 'association']);

        return view('don.association.donations.show', compact('match'));
    }

    /** Formulaire d'édition du statut d'un match. */
    public function edit(Request $request, DonationMatch $match): View
    {
        $association = $request->user()->association;
        abort_unless($association && $match->association_id === $association->id, 403);
        abort_unless(in_array($match->status, [DonationMatch::SUGGESTED, DonationMatch::REQUESTED, DonationMatch::ACCEPTED], true), 403);

        $match->load(['donation.photos', 'need']);

        return view('don.association.donations.edit', compact('match'));
    }

    /** Met à jour le statut d'un match. */
    public function update(Request $request, DonationMatch $match): RedirectResponse
    {
        $association = $request->user()->association;
        abort_unless($association && $match->association_id === $association->id, 403);

        $data = $request->validate([
            'status'       => ['required', 'in:' . implode(',', [
                DonationMatch::ACCEPTED,
                DonationMatch::REJECTED,
                DonationMatch::COMPLETED,
            ])],
            'meeting_type' => ['nullable', 'in:' . implode(',', array_keys(Textile::MEETING_TYPES))],
            'meeting_at'   => ['nullable', 'date'],
            'meeting_note' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = $data['status'];

        $match->update($data);

        // Synchronise le statut du don parent
        $donationStatus = match ($newStatus) {
            DonationMatch::ACCEPTED  => Donation::ACCEPTED,
            DonationMatch::REJECTED  => Donation::PENDING_ANALYSIS,
            DonationMatch::COMPLETED => Donation::COMPLETED,
            default                  => null,
        };

        if ($donationStatus) {
            $match->donation->update(['status' => $donationStatus]);
        }

        // Décrémente le besoin si don remis
        if ($newStatus === DonationMatch::COMPLETED && $need = $match->need) {
            $need->update([
                'quantity_needed' => max(0, $need->quantity_needed - $match->quantity),
            ]);
        }

        return redirect()
            ->route('association.donations.index')
            ->with('success', 'Statut du don mis à jour avec succès.');
    }

    /** Supprime (rejette définitivement) un match suggéré ou refusé. */
    public function destroy(Request $request, DonationMatch $match): RedirectResponse
    {
        $association = $request->user()->association;
        abort_unless($association && $match->association_id === $association->id, 403);
        abort_unless(in_array($match->status, [DonationMatch::SUGGESTED, DonationMatch::REJECTED], true), 403,
            'Seuls les matches suggérés ou refusés peuvent être supprimés.');

        $match->delete();

        return redirect()
            ->route('association.donations.index')
            ->with('success', 'Le don a été retiré de votre liste.');
    }
}
