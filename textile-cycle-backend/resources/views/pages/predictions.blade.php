@extends('layouts.app')

@section('title', 'Moteur de Prédictions IA')
@section('meta_description', 'Modèles prédictifs et détection d\'anomalies textiles propulsés par l\'IA')
@section('breadcrumb', 'Prédictions IA')

@section('content')
<div class="animate-fade-in-up">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Moteur Prédictif IA</h1>
            <p class="page-subtitle">
                Algorithmes d'apprentissage automatique pour anticiper les flux textiles et optimiser la revalorisation
                <span class="ai-badge" style="margin-left:.5rem">Précision Modèle : {{ $predictions['score_ia'] }}%</span>
            </p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('marketplace.index') }}" class="btn btn-primary">
                <span class="material-icons-round">storefront</span> Marketplace
            </a>
        </div>
    </div>

    {{-- Tendances Prévues --}}
    <div class="card section">
        <div class="card-header">
            <div class="card-title">
                <span class="material-icons-round" style="color:var(--primary-light)">insights</span>
                Tendances Textiles Anticipées (Prochains 90 jours)
            </div>
            <span class="badge badge-primary">✦ Détection IA Active</span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem">
            @foreach($predictions['tendances'] as $tendance)
                <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:var(--radius-md);padding:1.25rem">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
                        <span class="badge badge-info">{{ $tendance['delai'] }}</span>
                        <span class="ai-badge">{{ $tendance['fiabilite'] }}% certitude</span>
                    </div>
                    <div style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:.5rem">
                        {{ $tendance['titre'] }}
                    </div>
                    <div style="font-size:.825rem;color:var(--text-secondary);background:rgba(108,99,255,.08);padding:.6rem .75rem;border-radius:var(--radius-sm);border-left:3px solid var(--primary)">
                        <strong>Recommandation :</strong> {{ $tendance['conseil'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Anomalies & Recommandations Stratégiques --}}
    <div class="grid-2 section">

        {{-- Anomalies --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span class="material-icons-round" style="color:var(--accent-orange)">warning</span>
                    Détection d'Anomalies de Flux
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:1rem">
                @foreach($predictions['anomalies'] as $ano)
                    <div class="alert alert-{{ $ano['severite'] === 'warning' ? 'warning' : 'info' }}">
                        <span class="material-icons-round" style="font-size:1.2rem">notifications_active</span>
                        <div>
                            <div style="font-weight:700;margin-bottom:.25rem">{{ $ano['titre'] }}</div>
                            <div style="font-size:.85rem">{{ $ano['description'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Strategic AI Recommendations --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span class="material-icons-round" style="color:var(--secondary)">psychology</span>
                    Recommandations Opérationnelles IA
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:.75rem">
                @foreach($predictions['recommandations'] as $idx => $reco)
                    <div style="display:flex;align-items:flex-start;gap:.75rem;background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:var(--radius-md);padding:.85rem">
                        <span style="width:24px;height:24px;border-radius:50%;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;color:white;flex-shrink:0">
                            {{ $idx + 1 }}
                        </span>
                        <span style="font-size:.875rem;color:var(--text-secondary);line-height:1.4">
                            {{ $reco }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
