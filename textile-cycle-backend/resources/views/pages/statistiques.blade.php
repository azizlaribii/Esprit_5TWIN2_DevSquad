@extends('layouts.app')

@section('title', 'Statistiques Avancées')
@section('meta_description', 'Analyses détaillées des flux textiles, catégories, réparations et impact écologique')
@section('breadcrumb', 'Statistiques')

@section('content')
<div class="animate-fade-in-up">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Statistiques & Analyses</h1>
            <p class="page-subtitle">Suivi quantitatif et écologique du cycle de vie des vêtements</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('predictions.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">auto_awesome</span> Prédictions IA
            </a>
            <a href="{{ route('marketplace.index') }}" class="btn btn-primary">
                <span class="material-icons-round">storefront</span> Marketplace
            </a>
        </div>
    </div>

    {{-- Impact Summary Grid --}}
    <div class="grid-4 section">
        <div class="card">
            <div style="font-size:.78rem;color:var(--text-secondary);font-weight:700;text-transform:uppercase">CO₂ Total Évité</div>
            <div style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:800;color:var(--accent-blue);margin:.35rem 0">{{ $stats['impact']['co2'] }}</div>
            <div style="font-size:.78rem;color:var(--secondary)">Équivalent à 62 000 km en voiture</div>
        </div>

        <div class="card">
            <div style="font-size:.78rem;color:var(--text-secondary);font-weight:700;text-transform:uppercase">Eau Douce Épargnée</div>
            <div style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:800;color:var(--primary-light);margin:.35rem 0">{{ $stats['impact']['eau'] }}</div>
            <div style="font-size:.78rem;color:var(--text-muted)">Culture du coton contournée</div>
        </div>

        <div class="card">
            <div style="font-size:.78rem;color:var(--text-secondary);font-weight:700;text-transform:uppercase">Pesticides Évités</div>
            <div style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:800;color:var(--accent-orange);margin:.35rem 0">{{ $stats['impact']['pesticides'] }}</div>
            <div style="font-size:.78rem;color:var(--secondary)">Grâce au réemploi direct</div>
        </div>

        <div class="card">
            <div style="font-size:.78rem;color:var(--text-secondary);font-weight:700;text-transform:uppercase">Taux de Revalorisation</div>
            <div style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:800;color:var(--secondary);margin:.35rem 0">{{ $stats['impact']['textile_revalorise'] }}</div>
            <div style="font-size:.78rem;color:var(--secondary)">+6.2% vs année passée</div>
        </div>
    </div>

    {{-- 2 Columns: Categories Breakdown & Monthly Evolution --}}
    <div class="grid-2 section">

        {{-- Categories distribution --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span class="material-icons-round" style="color:var(--primary)">pie_chart</span>
                    Répartition par Catégorie de Vêtement
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:1.25rem">
                @foreach($stats['categories'] as $cat)
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.35rem">
                            <span style="font-weight:600;color:var(--text-primary)">{{ $cat['nom'] }}</span>
                            <span style="color:var(--text-secondary)">{{ $cat['quantite'] }} articles (<strong>{{ $cat['pourcentage'] }}%</strong>)</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:{{ $cat['pourcentage'] }}%;background:{{ $cat['couleur'] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Monthly evolution table/chart --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span class="material-icons-round" style="color:var(--secondary)">show_chart</span>
                    Évolution Mensuelle des Flux
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mois</th>
                            <th>Dépôts</th>
                            <th>Réparations</th>
                            <th>Dons</th>
                            <th>Tendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['evolution'] as $row)
                            <tr>
                                <td><strong>{{ $row['mois'] }}</strong></td>
                                <td><span class="badge badge-warning">{{ $row['depots'] }}</span></td>
                                <td><span class="badge badge-danger">{{ $row['reparations'] }}</span></td>
                                <td><span class="badge badge-success">{{ $row['dons'] }}</span></td>
                                <td style="color:var(--secondary);font-size:.8rem;font-weight:600">
                                    <span class="material-icons-round" style="font-size:1rem;vertical-align:middle">trending_up</span> Hausse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Top Partners --}}
    <div class="grid-2 section">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span class="material-icons-round" style="color:var(--accent-orange)">precision_manufacturing</span>
                    Top Ateliers de Couture & Réparation
                </div>
                <a href="{{ route('ateliers.index') }}" style="font-size:.85rem">Voir tout →</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Atelier</th>
                            <th>Spécialité</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['top_ateliers'] as $atelier)
                            <tr>
                                <td><strong>{{ $atelier->nom }}</strong></td>
                                <td>{{ $atelier->specialite }}</td>
                                <td><span class="stars" style="color:#FFA726">★ {{ number_format($atelier->note, 1) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center">Aucun atelier enregistré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span class="material-icons-round" style="color:var(--secondary)">groups</span>
                    Top Associations Partenaires
                </div>
                <a href="{{ route('associations.index') }}" style="font-size:.85rem">Voir tout →</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Association</th>
                            <th>Adresse</th>
                            <th>Bénéficiaires Aidés</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['top_associations'] as $assoc)
                            <tr>
                                <td><strong>{{ $assoc->nom }}</strong></td>
                                <td>{{ $assoc->adresse }}</td>
                                <td><span class="badge badge-success">{{ $assoc->beneficiaires_aides }} personnes</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center">Aucune association enregistrée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
