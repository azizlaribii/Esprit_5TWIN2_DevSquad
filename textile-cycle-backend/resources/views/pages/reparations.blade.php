@extends('layouts.app')

@section('title', 'Suivi des Réparations Textiles')
@section('meta_description', 'Historique des réparations confiées aux ateliers partenaires et diagnostics par IA')
@section('breadcrumb', 'Gestion › Réparations')

@section('content')
<div class="animate-fade-in-up">

    <!-- Top Header -->
    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
        <div>
            <h1 class="page-title">Réparations & Diagnostics Intelligents</h1>
            <p class="page-subtitle">Allonger la durée de vie des vêtements grâce à l'IA textile et notre réseau d'artisans</p>
        </div>
        <div style="display:flex; gap:.75rem; flex-wrap:wrap">
            <a href="{{ route('ateliers.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">precision_manufacturing</span> Voir les Ateliers
            </a>
            <a href="{{ route('reparations.create') }}" class="btn btn-primary" style="box-shadow:0 4px 15px rgba(108,99,255,0.4)">
                <span class="material-icons-round">auto_awesome</span> Nouvelle Analyse Intelligente
            </a>
        </div>
    </div>

    <!-- AI Diagnostic Hero Banner -->
    <div class="card" style="background:linear-gradient(135deg, rgba(108,99,255,0.15) 0%, rgba(67,217,173,0.1) 100%); border:1px solid rgba(108,99,255,0.3); margin-bottom:2rem; padding:1.5rem">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem">
            <div style="max-width:650px">
                <div style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.25rem 0.65rem; border-radius:999px; background:rgba(67,217,173,0.2); color:var(--secondary); font-size:0.75rem; font-weight:700; text-transform:uppercase; margin-bottom:0.75rem">
                    <span class="material-icons-round" style="font-size:0.9rem">psychology</span> Module Réparation Intelligente (IA)
                </div>
                <h2 style="font-size:1.35rem; font-family:'Outfit',sans-serif; margin-bottom:0.4rem; color:var(--text-primary)">
                    Un vêtement endommagé ? Scannez-le en quelques secondes !
                </h2>
                <p style="color:var(--text-secondary); font-size:0.9rem; line-height:1.5">
                    Téléchargez une photo pour identifier le type d'altération (trous, déchirures, fermetures, taches, boutons manquants), estimer le coût d'intervention et trouver instantanément les ateliers retoucheurs les plus proches.
                </p>
            </div>
            <div>
                <a href="{{ route('reparations.create') }}" class="btn btn-primary" style="padding:0.75rem 1.5rem; font-size:0.95rem">
                    <span class="material-icons-round">photo_camera</span> Scanner un vêtement
                </a>
            </div>
        </div>
    </div>

    <!-- Section 1 : Analyses Intelligentes Récentes (Modèle RepairRequest) -->
    <div class="card section" style="margin-bottom:2rem">
        <div class="card-header">
            <div class="card-title" style="display:flex; align-items:center; gap:0.5rem">
                <span class="material-icons-round" style="color:var(--primary-light)">document_scanner</span>
                Demandes d'analyses intelligentes (Photos & Diagnostic IA)
            </div>
            <span class="badge badge-primary">{{ $repairRequests->total() }} analyses</span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th>
                        <th>Aperçu Photo</th>
                        <th>Défaut Détecté</th>
                        <th>Sévérité</th>
                        <th>Estimation Coût</th>
                        <th>Atelier Recommandé</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repairRequests as $req)
                        @php
                            $sevColors = [
                                'faible' => ['badge' => 'badge-success', 'text' => 'Faible'],
                                'moyenne' => ['badge' => 'badge-warning', 'text' => 'Moyenne'],
                                'elevee' => ['badge' => 'badge-danger', 'text' => 'Élevée'],
                            ];
                            $sev = $sevColors[$req->severity] ?? ['badge' => 'badge-primary', 'text' => $req->severity ?? 'N/A'];
                        @endphp
                        <tr>
                            <td><strong>#{{ $req->id }}</strong></td>
                            <td>
                                @if($req->photo_url)
                                    <a href="{{ route('reparations.show', $req->id) }}">
                                        <img src="{{ $req->photo_url }}" alt="Photo" style="width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid var(--border)" />
                                    </a>
                                @else
                                    <div style="width:48px; height:48px; border-radius:8px; background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center">
                                        <span class="material-icons-round" style="font-size:1.2rem; color:var(--text-muted)">image</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong style="color:var(--text-primary)">
                                    {{ $req->defect_type ?? ($req->ai_verdict === 'conforme' ? 'En bon état' : 'Non identifié') }}
                                </strong>
                                @if($req->confidence)
                                    <div style="font-size:0.75rem; color:var(--text-muted)">
                                        Confiance {{ round($req->confidence * 100) }}%
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $sev['badge'] }}">
                                    {{ $sev['text'] }}
                                </span>
                            </td>
                            <td>
                                @if($req->estimated_cost_min || $req->estimated_cost_max)
                                    <strong style="color:var(--secondary)">{{ $req->estimated_cost_min }} DT – {{ $req->estimated_cost_max }} DT</strong>
                                @else
                                    <span style="color:var(--text-muted)">0 DT</span>
                                @endif
                            </td>
                            <td>
                                @if($req->workshop)
                                    <span style="font-weight:600">{{ $req->workshop->name }}</span>
                                    <div style="font-size:0.75rem; color:var(--text-muted)">{{ $req->workshop->city ?? '' }}</div>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.85rem">Aucun atelier choisi</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $req->status === 'terminee' ? 'badge-success' : 'badge-primary' }}">
                                    {{ ucfirst($req->status ?? 'En attente') }}
                                </span>
                            </td>
                            <td>{{ $req->created_at ? $req->created_at->format('d/m/Y') : 'N/A' }}</td>
                            <td style="text-align:right">
                                <a href="{{ route('reparations.show', $req->id) }}" class="btn btn-secondary btn-sm">
                                    <span class="material-icons-round" style="font-size:1rem">visibility</span> Diagnostic
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:2.5rem; color:var(--text-muted)">
                                <div style="margin-bottom:0.75rem">
                                    <span class="material-icons-round" style="font-size:2.5rem; color:var(--text-dim)">auto_awesome</span>
                                </div>
                                Aucune analyse intelligente enregistrée pour le moment.<br/>
                                <a href="{{ route('reparations.create') }}" class="btn btn-primary btn-sm" style="margin-top:1rem">
                                    <span class="material-icons-round">add_photo_alternate</span> Lancer la première analyse
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($repairRequests->hasPages())
            <div style="margin-top:1.5rem; display:flex; justify-content:center">
                {{ $repairRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Section 2 : Ordres de Réparation Classiques (Table reparations) -->
    <div class="card section">
        <div class="card-header">
            <div class="card-title">
                <span class="material-icons-round" style="color:var(--accent-red)">build</span>
                Ordres de réparation en atelier (Réseau partenaires)
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
                            <td><strong style="color:var(--primary-light)">{{ number_format($rep->cout, 2) }} DT</strong></td>
                            <td>
                                <span class="badge {{ $rep->statut === 'terminee' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $rep->statut)) }}
                                </span>
                            </td>
                            <td>{{ $rep->created_at ? $rep->created_at->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted)">
                                Aucune réparation enregistrée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reparations->hasPages())
            <div style="margin-top:1.5rem; display:flex; justify-content:center">
                {{ $reparations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
