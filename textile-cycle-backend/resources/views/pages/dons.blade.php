@extends('layouts.app')

@section('title', 'Dons Textiles & Solidarité')
@section('meta_description', 'Historique des dons de vêtements redistribués aux associations caritatives')
@section('breadcrumb', 'Gestion › Dons')

@section('content')
<div class="animate-fade-in-up">

    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Dons Textiles Solidaires</h1>
            <p class="page-subtitle">Redistribution des invendus et pièces données aux personnes dans le besoin</p>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
            <a href="{{ route('associations.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">groups</span> Associations Partenaires
            </a>
            @auth
                <a href="{{ route('donations.create') }}" class="btn btn-primary">
                    <span class="material-icons-round">volunteer_activism</span> Faire un don intelligent
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <span class="material-icons-round">volunteer_activism</span> Faire un don
                </a>
            @endauth
        </div>
    </div>


    {{-- ── SECTION 1 : Dons Intelligents (Donation model) ─────────────────── --}}
    <div class="card section" style="margin-bottom:2rem">
        <div class="card-header">
            <div class="card-title">
                <span class="material-icons-round" style="color:var(--primary)">psychology</span>
                Dons intelligents (avec matching IA)
            </div>
            <span class="badge badge-info">{{ $donations->total() }} dons</span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th>
                        <th>Donateur</th>
                        <th>Titre / Catégorie</th>
                        <th>Quantité</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Détail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($donations as $donation)
                        @php
                            $statusColors = [
                                'pending_analysis' => 'badge-warning',
                                'needs_review'     => 'badge-warning',
                                'matched'          => 'badge-info',
                                'no_match'         => 'badge-secondary',
                                'requested'        => 'badge-info',
                                'accepted'         => 'badge-success',
                                'completed'        => 'badge-success',
                                'cancelled'        => 'badge-danger',
                            ];
                            $badgeClass = $statusColors[$donation->status] ?? 'badge-secondary';
                            $statusLabels = [
                                'pending_analysis' => 'Analyse en cours',
                                'needs_review'     => 'À compléter',
                                'matched'          => 'Suggestions prêtes',
                                'no_match'         => 'Aucune association',
                                'requested'        => 'En attente',
                                'accepted'         => 'Accepté',
                                'completed'        => 'Remis ✓',
                                'cancelled'        => 'Annulé',
                            ];
                        @endphp
                        <tr>
                            <td>#{{ $donation->id }}</td>
                            <td><strong>{{ $donation->user->name ?? 'Donateur #' . $donation->user_id }}</strong></td>
                            <td>
                                <strong>{{ $donation->title }}</strong>
                                @if($donation->category)
                                    <br><span style="color:var(--text-muted);font-size:.8rem">{{ $donation->categoryLabel() }}</span>
                                @endif
                            </td>
                            <td><strong style="color:var(--primary)">{{ $donation->quantity }} pièce(s)</strong></td>
                            <td>{{ $donation->city ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ $statusLabels[$donation->status] ?? $donation->status }}
                                </span>
                            </td>
                            <td>{{ $donation->created_at->format('d/m/Y') }}</td>
                            <td>
                                @auth
                                    @if(auth()->id() === $donation->user_id)
                                        <a href="{{ route('donations.show', $donation) }}"
                                           style="color:var(--primary);font-size:.85rem;text-decoration:underline">
                                            Voir
                                        </a>
                                    @else
                                        <span style="color:var(--text-muted);font-size:.85rem">—</span>
                                    @endif
                                @endauth
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">
                                Aucun don intelligent enregistré.
                                @auth
                                    <a href="{{ route('donations.create') }}" style="color:var(--primary);text-decoration:underline;margin-left:.5rem">
                                        Soyez le premier !
                                    </a>
                                @endauth
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($donations->hasPages())
            <div style="margin-top:1.5rem;display:flex;justify-content:center">
                {{ $donations->links() }}
            </div>
        @endif
    </div>

    {{-- ── SECTION 2 : Historique des dons solidaires (Don model legacy) ──── --}}
    <div class="card section">
        <div class="card-header">
            <div class="card-title">
                <span class="material-icons-round" style="color:var(--secondary)">volunteer_activism</span>
                Historique des dons solidaires
            </div>
            <span class="badge badge-success">{{ $dons->total() }} dons distribués</span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th>
                        <th>Donateur</th>
                        <th>Association Bénéficiaire</th>
                        <th>Poids Récolté</th>
                        <th>Statut</th>
                        <th>Date du Don</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dons as $don)
                        <tr>
                            <td>#{{ $don->id }}</td>
                            <td><strong>{{ $don->user->name ?? 'Donateur #' . $don->user_id }}</strong></td>
                            <td>{{ $don->association->nom ?? 'Association solidaire' }}</td>
                            <td><strong style="color:var(--secondary)">{{ number_format($don->quantite_kg, 1) }} kg</strong></td>
                            <td>
                                <span class="badge {{ $don->statut === 'distribue' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $don->statut)) }}
                                </span>
                            </td>
                            <td>{{ $don->created_at ? $don->created_at->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">
                                Aucun don enregistré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dons->hasPages())
            <div style="margin-top:1.5rem;display:flex;justify-content:center">
                {{ $dons->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
