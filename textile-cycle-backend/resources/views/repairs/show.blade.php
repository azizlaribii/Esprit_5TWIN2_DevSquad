@extends('layouts.app')

@section('title', 'Rapport d\'analyse — ' . ($repair->defect_type ?? 'Vêtement'))
@section('meta_description', 'Résultat détaillé de l\'analyse intelligente pour cette pièce textile')
@section('breadcrumb', 'Gestion › Réparations › Diagnostic #' . $repair->id)

@php
    $c = $repair->confidence ?? 0.85;
    $mainConf = round($c * 100);
    $rest = 100 - $mainConf;
    $secConf = round($rest * 0.65);
    $resConf = max($rest - $secConf, 0);

    $severityBadges = [
        'faible' => ['badge' => 'badge-success', 'label' => '🟡 Faible'],
        'moyenne' => ['badge' => 'badge-warning', 'label' => '🟠 Moyenne'],
        'elevee' => ['badge' => 'badge-danger', 'label' => '🔴 Élevée'],
    ];
    $sInfo = $severityBadges[$repair->severity] ?? ['badge' => 'badge-primary', 'label' => 'Non évaluée'];

    $bbox = $repair->bbox;
    if (is_string($bbox)) {
        $bbox = json_decode($bbox, true);
    }
    $suggestedRepairs = $repair->suggested_repairs;
    if (is_string($suggestedRepairs)) {
        $suggestedRepairs = json_decode($suggestedRepairs, true);
    }
    if (!is_array($suggestedRepairs)) {
        $suggestedRepairs = [];
    }
@endphp

@section('content')
<div class="animate-fade-in-up" style="max-width:1100px; margin:0 auto">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
        <div style="display:flex; align-items:center; gap:0.75rem">
            <a href="{{ route('reparations.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">arrow_back</span> Liste des réparations
            </a>
            <span class="badge badge-primary">Demande #{{ $repair->id }}</span>
        </div>
        <div style="display:flex; gap:0.75rem">
            <a href="{{ route('reparations.create') }}" class="btn btn-secondary">
                <span class="material-icons-round">add_photo_alternate</span> Nouvelle analyse
            </a>
            <button type="button" onclick="openWorkshopModal()" class="btn btn-primary">
                <span class="material-icons-round">add_business</span> Ajouter atelier
            </button>
        </div>
    </div>

    @if ($repair->ai_verdict === 'non_vetement')
        <div class="card" style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; align-items:center">
            <div>
                <img src="{{ $repair->photo_url }}" alt="Photo envoyée" style="width:100%; border-radius:12px; border:1px solid var(--border)" />
            </div>
            <div>
                <div style="font-size:2rem; margin-bottom:0.5rem">⚠️</div>
                <h2 style="font-size:1.5rem; color:var(--accent-orange); margin-bottom:0.75rem">Aucun vêtement détecté</h2>
                <p style="color:var(--text-secondary); line-height:1.6; margin-bottom:1.5rem">
                    Le modèle n'a pas pu identifier clairement un vêtement ou une pièce textile sur cette image. Veuillez reprendre une photo bien cadrée et éclairée.
                </p>
                <a href="{{ route('reparations.create') }}" class="btn btn-primary">
                    <span class="material-icons-round">refresh</span> Réessayer avec une autre photo
                </a>
            </div>
        </div>
    @elseif ($repair->ai_verdict === 'conforme')
        <div class="card" style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; align-items:center">
            <div>
                <img src="{{ $repair->photo_url }}" alt="Photo du vêtement" style="width:100%; border-radius:12px; border:1px solid var(--border)" />
            </div>
            <div>
                <div style="font-size:2rem; margin-bottom:0.5rem">✨</div>
                <h2 style="font-size:1.5rem; color:var(--secondary); margin-bottom:0.75rem">Vêtement en excellent état</h2>
                <p style="color:var(--text-secondary); line-height:1.6; margin-bottom:1.5rem">
                    Aucun trou, déchirure ou dégradation visible n'a été détecté par l'IA. Cette pièce peut être directement proposée sur la marketplace ou transmise pour un don !
                </p>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap">
                    <a href="/marketplace/create" class="btn btn-primary">
                        <span class="material-icons-round">storefront</span> Publier sur la Marketplace
                    </a>
                    <a href="{{ route('reparations.create') }}" class="btn btn-secondary">
                        <span class="material-icons-round">photo_camera</span> Analyser un autre vêtement
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Rapport complet défaut identifié -->
        <div style="display:grid; grid-template-columns:1.1fr 1fr; gap:1.5rem; align-items:start">
            
            <!-- Colonne gauche : Photo avec cadre bounding-box -->
            <div class="card" style="padding:1.25rem">
                <div style="position:relative; border-radius:12px; overflow:hidden; border:1px solid var(--border); background:#000">
                    <img id="defectImg" src="{{ $repair->photo_url }}" alt="Photo du vêtement" style="width:100%; display:block" />

                    @if ($bbox && isset($bbox['x'], $bbox['y'], $bbox['width'], $bbox['height']))
                        <div style="position:absolute;
                                    left:{{ $bbox['x'] }}%;
                                    top:{{ $bbox['y'] }}%;
                                    width:{{ $bbox['width'] }}%;
                                    height:{{ $bbox['height'] }}%;
                                    border:2px solid var(--accent-red);
                                    box-shadow:0 0 12px rgba(255,101,132,0.6);
                                    border-radius:4px;
                                    pointer-events:none">
                            <span style="position:absolute; top:-24px; left:0; background:var(--accent-red); color:white; font-size:0.7rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:4px; white-space:nowrap">
                                {{ $repair->defect_type ?? 'Défaut' }}
                            </span>
                        </div>
                    @endif

                    <div style="position:absolute; bottom:12px; right:12px; background:rgba(10,14,26,0.85); border:1px solid var(--border); border-radius:8px; padding:0.4rem 0.75rem; text-align:right">
                        <div style="font-size:0.65rem; color:var(--text-muted); text-transform:uppercase">Confiance IA</div>
                        <div style="font-size:1.1rem; font-weight:800; color:var(--secondary)">{{ round($repair->confidence * 100) }}%</div>
                    </div>
                </div>

                <div style="margin-top:1.25rem; display:flex; justify-content:space-between; align-items:center">
                    <span style="font-size:0.85rem; color:var(--text-muted)">
                        Analysé le {{ $repair->created_at ? $repair->created_at->format('d/m/Y à H:i') : 'Récemment' }}
                    </span>
                    <span class="badge {{ $repair->status === 'terminee' ? 'badge-success' : 'badge-primary' }}">
                        {{ ucfirst($repair->status) }}
                    </span>
                </div>
            </div>

            <!-- Colonne droite : Synthèse du diagnostic & Recommandations -->
            <div style="display:flex; flex-direction:column; gap:1.25rem">
                <div class="card">
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem">
                        <div style="width:42px; height:42px; border-radius:10px; background:rgba(108,99,255,0.15); display:flex; align-items:center; justify-content:center; color:var(--primary-light)">
                            <span class="material-icons-round">construction</span>
                        </div>
                        <div>
                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); font-weight:700">Défaut Identifié</div>
                            <h2 style="font-size:1.4rem; font-family:'Outfit',sans-serif; margin:0">{{ $repair->defect_type }}</h2>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1.25rem">
                        <div style="background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:10px; padding:0.85rem">
                            <span style="font-size:0.75rem; color:var(--text-muted); display:block">Sévérité</span>
                            <strong style="font-size:0.95rem">{{ $sInfo['label'] }}</strong>
                        </div>
                        <div style="background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:10px; padding:0.85rem">
                            <span style="font-size:0.75rem; color:var(--text-muted); display:block">Localisation</span>
                            <strong style="font-size:0.95rem">{{ $repair->location ?? 'Zone textile' }}</strong>
                        </div>
                    </div>

                    <div style="background:linear-gradient(135deg,rgba(67,217,173,0.1),rgba(41,182,246,0.05)); border:1px solid rgba(67,217,173,0.25); border-radius:12px; padding:1.2rem; margin-bottom:1.25rem; display:flex; justify-content:space-between; align-items:center">
                        <div>
                            <span style="font-size:0.8rem; color:var(--text-secondary); display:block">Coût estimé de réparation</span>
                            <div style="font-size:1.5rem; font-weight:800; color:var(--secondary); font-family:'Outfit',sans-serif">
                                {{ $repair->estimated_cost_min }} DT – {{ $repair->estimated_cost_max }} DT
                            </div>
                        </div>
                        <span class="material-icons-round" style="color:var(--secondary); font-size:2rem">savings</span>
                    </div>

                    <div style="margin-bottom:1.5rem">
                        <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.75rem">
                            Interventions recommandées :
                        </div>
                        <ul style="display:flex; flex-direction:column; gap:0.5rem; list-style:none; padding:0">
                            @foreach ($suggestedRepairs as $step)
                                <li style="display:flex; align-items:center; gap:0.6rem; padding:0.6rem 0.85rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:8px; font-size:0.88rem">
                                    <span class="material-icons-round" style="font-size:1.1rem; color:var(--primary-light)">check_circle</span>
                                    {{ $step }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Atelier sélectionné ou bouton pour en choisir un -->
                    @if ($repair->workshop)
                        <div style="background:rgba(108,99,255,0.1); border:1px solid rgba(108,99,255,0.3); border-radius:12px; padding:1rem; margin-bottom:1rem">
                            <span style="font-size:0.75rem; color:var(--primary-light); text-transform:uppercase; font-weight:700; display:block">Atelier Partenaire Attribué</span>
                            <strong style="font-size:1.1rem">{{ $repair->workshop->name }}</strong>
                            <p style="font-size:0.85rem; color:var(--text-secondary); margin:0.25rem 0 0">{{ $repair->workshop->address }} ({{ $repair->workshop->city }})</p>
                        </div>
                    @endif

                    <a href="{{ route('reparations.workshops', $repair->id) }}" class="btn btn-primary" style="width:100%; justify-content:center; padding:0.85rem; font-size:0.95rem">
                        <span class="material-icons-round">store</span>
                        {{ $repair->workshop ? 'Changer d\'atelier partenaire' : 'Sélectionner un atelier partenaire' }}
                    </a>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
