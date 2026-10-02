@extends('layouts.app')

@section('title', 'Tableau de bord intelligent')
@section('meta_description', 'Tableau de bord et indicateurs de performance de la plateforme circulaire TexTileCycle')
@section('breadcrumb', 'Tableau de bord')

@section('styles')
<style>
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:1.5rem}
.kpi-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem;position:relative;overflow:hidden;transition:var(--transition)}
.kpi-card:hover{transform:translateY(-2px);border-color:var(--border-active);box-shadow:var(--shadow-glow)}
.kpi-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:.85rem}
.kpi-label{font-size:.8rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.kpi-valeur{font-family:'Outfit',sans-serif;font-size:1.85rem;font-weight:800;color:var(--text-primary);margin:.25rem 0}
.kpi-trend{font-size:.78rem;font-weight:600;display:flex;align-items:center;gap:.25rem}

.dashboard-cols{display:grid;grid-template-columns:2fr 1.2fr;gap:1.5rem;align-items:start}
@media(max-width:1100px){.kpi-grid{grid-template-columns:repeat(2,1fr)}.dashboard-cols{grid-template-columns:1fr}}
@media(max-width:600px){.kpi-grid{grid-template-columns:1fr}}
</style>
@endsection

@section('content')
<div class="animate-fade-in-up">

    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Tableau de Bord Circulaire</h1>
            <p class="page-subtitle">Suivi en direct des dépôts, réparations, dons et transactions de la marketplace</p>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
            <a href="{{ route('marketplace.index') }}" class="btn btn-primary">
                <span class="material-icons-round">storefront</span> Marketplace Circulaire
            </a>
            <a href="{{ route('marketplace.create') }}" class="btn btn-secondary">
                <span class="material-icons-round">add_circle</span> Publier un article
            </a>
        </div>
    </div>

    {{-- KPIs Grid --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(108,99,255,.15);color:var(--primary-light)">
                <span class="material-icons-round">storefront</span>
            </div>
            <div class="kpi-label">Marketplace Active</div>
            <div class="kpi-valeur">{{ $kpis['marketplace_count'] }} <span style="font-size:1rem;color:var(--text-muted)">articles</span></div>
            <div class="kpi-trend" style="color:var(--secondary)">
                <span class="material-icons-round" style="font-size:.9rem">trending_up</span> +18% ce mois
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(255,167,38,.15);color:var(--accent-orange)">
                <span class="material-icons-round">inventory_2</span>
            </div>
            <div class="kpi-label">Dépôts Enregistrés</div>
            <div class="kpi-valeur">{{ $kpis['depots_count'] }} <span style="font-size:1rem;color:var(--text-muted)">lots</span></div>
            <div class="kpi-trend" style="color:var(--secondary)">
                <span class="material-icons-round" style="font-size:.9rem">trending_up</span> +14.8% en attente
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(255,101,132,.15);color:var(--accent-red)">
                <span class="material-icons-round">build</span>
            </div>
            <div class="kpi-label">Réparations Ateliers</div>
            <div class="kpi-valeur">{{ $kpis['reparations_count'] }} <span style="font-size:1rem;color:var(--text-muted)">en cours</span></div>
            <div class="kpi-trend" style="color:var(--accent-red)">
                <span class="material-icons-round" style="font-size:.9rem">schedule</span> 100% prises en charge
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(67,217,173,.15);color:var(--secondary)">
                <span class="material-icons-round">volunteer_activism</span>
            </div>
            <div class="kpi-label">Dons Caritatifs</div>
            <div class="kpi-valeur">{{ $kpis['dons_count'] }} <span style="font-size:1rem;color:var(--text-muted)">lots</span></div>
            <div class="kpi-trend" style="color:var(--secondary)">
                <span class="material-icons-round" style="font-size:.9rem">favorite</span> Solidarité active
            </div>
        </div>
    </div>

    {{-- Main Sections Grid --}}
    <div class="dashboard-cols">

        {{-- Left: Marketplace & Activity --}}
        <div>
            {{-- Nouveautés Marketplace --}}
            <div class="card section">
                <div class="card-header">
                    <div class="card-title">
                        <span class="material-icons-round" style="color:var(--primary);font-size:1.2rem">shopping_bag</span>
                        Dernières pièces sur la Marketplace
                    </div>
                    <a href="{{ route('marketplace.index') }}" style="font-size:.85rem;font-weight:600">Voir tout →</a>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:1rem">
                    @forelse($recentMarketplace as $art)
                        <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:var(--radius-md);padding:.75rem;transition:var(--transition)">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
                                <span class="badge badge-primary" style="font-size:.7rem">{{ ucfirst($art->type) }}</span>
                                <span class="ai-badge" style="font-size:.65rem">IA {{ $art->ai_score }}%</span>
                            </div>
                            <a href="{{ route('marketplace.show', $art) }}" style="font-weight:700;color:var(--text-primary);font-size:.9rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $art->titre }}
                            </a>
                            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.25rem">
                                {{ $art->taille }} • {{ $art->categorie }}
                            </div>
                            <div style="font-family:'Outfit',sans-serif;font-size:1.1rem;font-weight:800;color:var(--primary-light);margin-top:.5rem">
                                {{ $art->type === 'don' ? 'Gratuit' : ($art->prix ? number_format($art->prix, 2) . ' DT' : 'Échange') }}
                            </div>
                        </div>
                    @empty
                        <div style="color:var(--text-muted)">Aucun article récent.</div>
                    @endforelse
                </div>
            </div>

            {{-- Dépôts & Réparations --}}
            <div class="card section">
                <div class="card-header">
                    <div class="card-title">
                        <span class="material-icons-round" style="color:var(--secondary);font-size:1.2rem">history</span>
                        Activité récente du circuit textile
                    </div>
                    <a href="{{ route('depots.index') }}" style="font-size:.85rem;font-weight:600">Tous les dépôts →</a>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Catégorie</th>
                                <th>Usager</th>
                                <th>Quantité</th>
                                <th>État</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentDepots as $depot)
                                <tr>
                                    <td><strong>{{ $depot->categorie }}</strong></td>
                                    <td>{{ $depot->user->name ?? 'Utilisateur' }}</td>
                                    <td>{{ $depot->quantite }} pcs</td>
                                    <td><span class="badge badge-info">{{ $depot->etat }}</span></td>
                                    <td>
                                        <span class="badge badge-success">{{ $depot->statut }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: Eco-Impact & Shortcuts --}}
        <div>
            <div class="card section" style="background:linear-gradient(135deg,rgba(67,217,173,.08),rgba(41,182,246,.04));border-color:rgba(67,217,173,.25)">
                <div class="card-header">
                    <div class="card-title" style="color:var(--secondary)">
                        <span class="material-icons-round">eco</span>
                        Bilan Écologique Global
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:1.25rem">
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.35rem">
                            <span style="color:var(--text-secondary)">Textiles sauvés de l'enfouissement</span>
                            <strong style="color:var(--secondary)">{{ $kpis['kg_sauves'] }} kg</strong>
                        </div>
                        <div class="progress-bar"><div class="progress-fill" style="width:85%;background:var(--gradient-green)"></div></div>
                    </div>

                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.35rem">
                            <span style="color:var(--text-secondary)">Émissions CO₂ évitées</span>
                            <strong style="color:var(--accent-blue)">{{ $kpis['co2_evite'] }} kg</strong>
                        </div>
                        <div class="progress-bar"><div class="progress-fill" style="width:74%;background:linear-gradient(90deg,#29B6F6,#6C63FF)"></div></div>
                    </div>

                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.35rem">
                            <span style="color:var(--text-secondary)">Eau potable préservée</span>
                            <strong style="color:var(--primary-light)">{{ number_format($kpis['eau_economisee'], 0, ',', ' ') }} L</strong>
                        </div>
                        <div class="progress-bar"><div class="progress-fill" style="width:68%"></div></div>
                    </div>
                </div>

                <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,.05);display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:.85rem;color:var(--text-muted)">Score Éco-Plateforme :</span>
                    <span style="font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:var(--secondary)">78/100</span>
                </div>
            </div>

            {{-- Quick navigation cards --}}
            <div class="card section">
                <div class="card-header">
                    <div class="card-title">
                        <span class="material-icons-round" style="color:var(--primary-light)">explore</span>
                        Accès Rapides
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:.75rem">
                    <a href="{{ route('statistiques.index') }}" class="btn btn-secondary" style="justify-content:space-between">
                        <span><span class="material-icons-round" style="font-size:1.1rem;vertical-align:middle">bar_chart</span> Statistiques Détaillées</span>
                        <span class="material-icons-round">arrow_forward</span>
                    </a>
                    <a href="{{ route('predictions.index') }}" class="btn btn-secondary" style="justify-content:space-between">
                        <span><span class="material-icons-round" style="font-size:1.1rem;vertical-align:middle">auto_awesome</span> Moteur Prédictif IA</span>
                        <span class="material-icons-round">arrow_forward</span>
                    </a>
                    <a href="{{ route('ateliers.index') }}" class="btn btn-secondary" style="justify-content:space-between">
                        <span><span class="material-icons-round" style="font-size:1.1rem;vertical-align:middle">precision_manufacturing</span> Ateliers Partenaires</span>
                        <span class="material-icons-round">arrow_forward</span>
                    </a>
                    <a href="{{ route('associations.index') }}" class="btn btn-secondary" style="justify-content:space-between">
                        <span><span class="material-icons-round" style="font-size:1.1rem;vertical-align:middle">groups</span> Associations Solidaires</span>
                        <span class="material-icons-round">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
