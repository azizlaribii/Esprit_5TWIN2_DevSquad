@extends('layouts.app')

@section('title', 'Suivi des Réparations Textiles')
@section('meta_description', 'Historique des réparations confiées aux ateliers partenaires et artisans retoucheurs')
@section('breadcrumb', 'Gestion › Réparations')

@section('content')
<div class="animate-fade-in-up">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Réparations & Retouches</h1>
            <p class="page-subtitle">Allonger la durée de vie des vêtements grâce à notre réseau d'artisans</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('ateliers.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">precision_manufacturing</span> Voir les Ateliers
            </a>
        </div>
    </div>

    <div class="card section">
        <div class="card-header">
            <div class="card-title">
                <span class="material-icons-round" style="color:var(--accent-red)">build</span>
                Ordres de réparation en cours et finalisés
            </div>
            <span class="badge badge-primary">{{ $reparations->total() }} réparations</span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th>
                        <th>Client</th>
                        <th>Atelier Partenaire</th>
                        <th>Description de l'intervention</th>
                        <th>Coût Estimé</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reparations as $rep)
                        <tr>
                            <td>#{{ $rep->id }}</td>
                            <td><strong>{{ $rep->user->name ?? 'Client #' . $rep->user_id }}</strong></td>
                            <td>{{ $rep->atelier->nom ?? 'Atelier assigné' }}</td>
                            <td>{{ $rep->description }}</td>
                            <td><strong style="color:var(--primary-light)">{{ number_format($rep->cout, 2) }} €</strong></td>
                            <td>
                                <span class="badge {{ $rep->statut === 'terminee' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $rep->statut)) }}
                                </span>
                            </td>
                            <td>{{ $rep->created_at ? $rep->created_at->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">
                                Aucune réparation enregistrée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reparations->hasPages())
            <div style="margin-top:1.5rem;display:flex;justify-content:center">
                {{ $reparations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
