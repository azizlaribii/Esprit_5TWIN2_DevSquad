@extends('layouts.app')

@section('title', 'Rapport d\'analyse — ' . ($repair->defect_type ?? 'Vêtement'))

@php
    $c = $repair->confidence ?? 0.85;
    $mainConf = round($c * 100);
    $rest = 100 - $mainConf;
    $secConf = round($rest * 0.65);
    $resConf = max($rest - $secConf, 0);

    $severityIcons = ['faible' => '🟡', 'moyenne' => '🟠', 'elevee' => '🔴'];
    $severityLabels = ['faible' => 'Faible', 'moyenne' => 'Moyenne', 'elevee' => 'Élevée'];
    $icon = $severityIcons[$repair->severity] ?? '⚪';
    $label = $severityLabels[$repair->severity] ?? 'Non évaluée';

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
<div class="page-wide">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem">
        <a class="back-link" href="{{ route('reparations.create') }}" style="flex-shrink:0; white-space:nowrap">← Retour</a>
        <button type="button" onclick="openWorkshopModal()"
                style="width:auto; padding:0.55rem 1.1rem; font-size:0.85rem; font-weight:700;
                       border:none; border-radius:0.6rem; cursor:pointer;
                       background:#22c55e; color:#fff; white-space:nowrap">
            + Ajouter un atelier
        </button>
    </div>

    @if ($repair->ai_verdict === 'non_vetement')
        <div class="analyze-grid">
            <div>
                <div class="analyze-photo-wrap">
                    <img src="{{ $repair->photo_url }}" alt="Photo envoyée" />
                </div>
            </div>
            <div class="panel">
                <div class="ai-header">
                    <div class="ai-avatar">🤖</div>
                    <div>
                        <div class="title">Analyse IA</div>
                        <div class="subtitle">Modèle textile v2.4</div>
                    </div>
                </div>
                <div class="defect-title" style="color:var(--orange)">⚠️ Aucun vêtement détecté</div>
                <p style="color:var(--text-muted); font-size:0.92rem; line-height:1.5">
                    Nous n'avons pas réussi à identifier un vêtement sur cette photo.
                    Merci de réessayer avec une photo nette, montrant bien le vêtement.
                </p>
                <a class="btn-cta" href="{{ route('reparations.create') }}">Réessayer avec une autre photo</a>
            </div>
        </div>
    @elseif ($repair->ai_verdict === 'conforme')
        <div class="analyze-grid">
            <div>
                <div class="analyze-photo-wrap">
                    <img src="{{ $repair->photo_url }}" alt="Photo du vêtement" />
                    @if ($repair->confidence)
                        <div class="confidence-badge">
                            <div class="label">Confiance</div>
                            <div class="value">{{ round($repair->confidence * 100) }}%</div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="panel">
                <div class="ai-header">
                    <div class="ai-avatar">🤖</div>
                    <div>
                        <div class="title">Analyse IA</div>
                        <div class="subtitle">Modèle textile v2.4</div>
                    </div>
                </div>
                <div class="defect-title" style="color:var(--green)">✅ Vêtement en bon état</div>
                <p style="color:var(--text-muted); font-size:0.92rem; line-height:1.5">
                    Aucun défaut visible n'a été détecté — aucune réparation n'est
                    nécessaire pour le moment.
                </p>
                <a class="btn-secondary" href="{{ route('reparations.create') }}">Analyser un autre vêtement</a>
            </div>
        </div>
    @else
        <div class="analyze-grid">
            <div>
                <div class="analyze-photo-wrap" style="position:relative">
                    <img src="{{ $repair->photo_url }}" alt="Photo du vêtement" />

                    @if ($bbox && isset($bbox['x'], $bbox['y'], $bbox['width'], $bbox['height']))
                        <div class="bbox-frame"
                             style="left: {{ $bbox['x'] }}%; top: {{ $bbox['y'] }}%; width: {{ $bbox['width'] }}%; height: {{ $bbox['height'] }}%;">
                            <span class="bbox-label">{{ $repair->defect_type }} — {{ $repair->location }}</span>
                        </div>
                    @endif

                    @if ($repair->confidence)
                        <div class="confidence-badge">
                            <div class="label">Confiance</div>
                            <div class="value">{{ round($repair->confidence * 100) }}%</div>
                        </div>
                    @endif
                </div>

                <div class="panel">
                    <div class="certainty-title">Probabilités</div>

                    <div class="certainty-row">
                        <div class="top">
                            <span>{{ $repair->defect_type }}</span>
                            <span>{{ $mainConf }}%</span>
                        </div>
                        <div class="certainty-track">
                            <div class="certainty-fill" style="background:var(--red); width: {{ $mainConf }}%"></div>
                        </div>
                    </div>

                    <div class="certainty-row">
                        <div class="top">
                            <span>Usure normale</span>
                            <span>{{ $secConf }}%</span>
                        </div>
                        <div class="certainty-track">
                            <div class="certainty-fill" style="background:var(--orange); width: {{ $secConf }}%"></div>
                        </div>
                    </div>

                    <div class="certainty-row" style="margin-bottom:0">
                        <div class="top">
                            <span>Autre</span>
                            <span>{{ $resConf }}%</span>
                        </div>
                        <div class="certainty-track">
                            <div class="certainty-fill" style="background:var(--text-dim); width: {{ $resConf }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="panel" style="margin-bottom:1.25rem">
                    <div class="section-label">Défaut détecté</div>

                    <div class="defect-icon-row">
                        <div class="defect-icon-box">{{ $icon }}</div>
                        <div>
                            <div class="defect-name">{{ $repair->defect_type }}</div>
                            <div class="defect-loc">Localisation : <strong>{{ $repair->location }}</strong></div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="mini-card-row" style="margin-bottom:0">
                        <div class="mini-card">
                            <div class="section-label" style="margin-bottom:0">Gravité</div>
                            <div class="val">{{ $icon }} {{ $label }}</div>
                        </div>
                        <div class="mini-card">
                            <div class="section-label" style="margin-bottom:0">Zone</div>
                            <div class="val">📍 {{ $repair->location }}</div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="section-label" style="margin-bottom:0.75rem">💡 Réparation recommandée</div>
                    <ul class="repair-list">
                        @forelse ($suggestedRepairs as $repairOption)
                            <li>{{ $repairOption }}</li>
                        @empty
                            <li>Réparation générale recommandée par un atelier partenaire.</li>
                        @endforelse
                    </ul>

                    <div class="cost-row">
                        <span class="label">Coût estimé</span>
                        <span class="value">{{ $repair->estimated_cost_min }} – {{ $repair->estimated_cost_max }} DT</span>
                    </div>

                    @if ($repair->workshop)
                        <div class="workshop-selected">
                            ✓ Atelier sélectionné : <strong>{{ $repair->workshop->name }}</strong>
                        </div>
                    @else
                        <a class="btn-cta" href="{{ route('reparations.workshops', $repair->id) }}">
                            🧵 Trouver un atelier →
                        </a>
                    @endif

                    <a class="btn-secondary" href="{{ route('reparations.create') }}">
                        Analyser un autre vêtement
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
