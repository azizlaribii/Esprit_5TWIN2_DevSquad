@extends('layouts.app')

@section('title', 'Rapport d\'analyse — ' . ($repair->defect_type ?? 'Vêtement'))
@section('meta_description', 'Résultat détaillé de l\'analyse intelligente pour cette pièce textile')
@section('breadcrumb', 'Gestion › Réparations › Diagnostic #' . $repair->id)

@php
    $c = $repair->confidence ?? 0.85;
    $mainConf = round($c * 100);

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

@section('styles')
<style>
.defect-frame {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    border: 1px solid var(--border);
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.defect-box-highlight {
    position: absolute;
    border: 2.5px solid var(--accent-red);
    box-shadow: 0 0 14px rgba(255,101,132,0.65);
    border-radius: 6px;
    pointer-events: none;
    animation: pulse-border 2s infinite ease-in-out;
}
@keyframes pulse-border {
    0%, 100% { border-color: var(--accent-red); box-shadow: 0 0 12px rgba(255,101,132,0.6); }
    50% { border-color: #ff3366; box-shadow: 0 0 20px rgba(255,51,102,0.85); }
}
.defect-badge-tag {
    position: absolute;
    top: -26px;
    left: 0;
    background: var(--accent-red);
    color: white;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 4px;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.stat-pill-box {
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 0.9rem;
}
</style>
@endsection

@section('content')
<div class="animate-fade-in-up" style="max-width:1100px; margin:0 auto">

    {{-- Top navigation bar --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
        <div style="display:flex; align-items:center; gap:0.75rem">
            <a href="{{ route('reparations.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">arrow_back</span> Suivi des réparations
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
        <div class="card" style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; align-items:center; padding:2.5rem">
            <div>
                <img src="{{ $repair->photo_url }}" alt="Photo envoyée" style="width:100%; border-radius:12px; border:1px solid var(--border); object-fit:cover" />
            </div>
            <div>
                <div style="font-size:2.5rem; margin-bottom:0.5rem">⚠️</div>
                <h2 style="font-size:1.5rem; color:var(--accent-orange); margin-bottom:0.75rem">Aucun vêtement détecté</h2>
                <p style="color:var(--text-secondary); line-height:1.6; margin-bottom:1.5rem">
                    Le modèle IA n'a pas pu identifier clairement un vêtement ou une pièce textile sur cette image. Veuillez reprendre une photo bien cadrée et éclairée de votre pièce textile.
                </p>
                <a href="{{ route('reparations.create') }}" class="btn btn-primary">
                    <span class="material-icons-round">refresh</span> Réessayer avec une autre photo
                </a>
            </div>
        </div>
    @elseif ($repair->ai_verdict === 'conforme')
        <div class="card" style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; align-items:center; padding:2.5rem">
            <div>
                <img src="{{ $repair->photo_url }}" alt="Photo du vêtement" style="width:100%; border-radius:12px; border:1px solid var(--border); object-fit:cover" />
            </div>
            <div>
                <div style="font-size:2.5rem; margin-bottom:0.5rem">✨</div>
                <h2 style="font-size:1.5rem; color:var(--secondary); margin-bottom:0.75rem">Vêtement en excellent état</h2>
                <p style="color:var(--text-secondary); line-height:1.6; margin-bottom:1.5rem">
                    Aucun trou, déchirure ou dégradation visible n'a été détecté par l'IA. Cette pièce peut être directement proposée sur la marketplace circulaire ou transmise pour un don !
                </p>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap">
                    <a href="{{ route('marketplace.create') }}" class="btn btn-primary">
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
        <div style="display:grid; grid-template-columns:1.1fr 1fr; gap:1.75rem; align-items:start">
            
            <!-- Colonne gauche : Photo avec zone Bounding-Box -->
            <div class="card" style="padding:1.5rem">
                <div class="defect-frame">
                    <img id="defectImg" src="{{ $repair->photo_url }}" alt="Photo du vêtement" style="width:100%; display:block; max-height:480px; object-fit:contain; background:#f8fafc" />

                    @if ($bbox && isset($bbox['x'], $bbox['y'], $bbox['width'], $bbox['height']))
                        <div class="defect-box-highlight" style="left:{{ $bbox['x'] }}%; top:{{ $bbox['y'] }}%; width:{{ $bbox['width'] }}%; height:{{ $bbox['height'] }}%;">
                            <span class="defect-badge-tag">
                                🔍 {{ $repair->defect_type ?? 'Défaut détecté' }}
                            </span>
                        </div>
                    @endif

                    <div style="position:absolute; bottom:14px; right:14px; background:rgba(255,255,255,0.95); backdrop-filter:blur(6px); border:1px solid var(--border); border-radius:10px; padding:0.45rem 0.85rem; text-align:right; box-shadow:0 4px 15px rgba(0,0,0,0.08)">
                        <div style="font-size:0.65rem; color:var(--text-muted); text-transform:uppercase; font-weight:700">Indice de Confiance IA</div>
                        <div style="font-size:1.15rem; font-weight:800; color:var(--secondary)">{{ round($repair->confidence * 100) }}%</div>
                    </div>
                </div>

                <div style="margin-top:1.25rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem">
                    <span style="font-size:0.85rem; color:var(--text-muted)">
                        Analysé le {{ $repair->created_at ? $repair->created_at->format('d/m/Y à H:i') : 'Récemment' }}
                    </span>
                    <span class="badge {{ $repair->status === 'terminee' ? 'badge-success' : 'badge-primary' }}">
                        {{ ucfirst($repair->status) }}
                    </span>
                </div>
            </div>

            <!-- Colonne droite : Diagnostic & Intervention -->
            <div style="display:flex; flex-direction:column; gap:1.25rem">
                <div class="card" style="padding:1.75rem">
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem">
                        <div style="width:44px; height:44px; border-radius:12px; background:rgba(108,99,255,0.12); display:flex; align-items:center; justify-content:center; color:var(--primary)">
                            <span class="material-icons-round" style="font-size:1.4rem">construction</span>
                        </div>
                        <div>
                            <div style="font-size:0.72rem; text-transform:uppercase; color:var(--text-muted); font-weight:700">Défaut Identifié par Vision IA</div>
                            <h2 style="font-size:1.5rem; font-family:'Outfit',sans-serif; margin:0; color:var(--text-primary)">{{ $repair->defect_type }}</h2>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1.25rem">
                        <div class="stat-pill-box">
                            <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:0.2rem">Niveau de Sévérité</span>
                            <strong style="font-size:0.95rem">{{ $sInfo['label'] }}</strong>
                        </div>
                        <div class="stat-pill-box">
                            <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:0.2rem">Zone Affectée</span>
                            <strong style="font-size:0.95rem; color:var(--primary)">{{ $repair->location ?? 'Zone visible' }}</strong>
                        </div>
                    </div>

                    <div style="background:linear-gradient(135deg,rgba(67,217,173,0.12),rgba(108,99,255,0.06)); border:1px solid rgba(67,217,173,0.3); border-radius:14px; padding:1.25rem; margin-bottom:1.25rem; display:flex; justify-content:space-between; align-items:center">
                        <div>
                            <span style="font-size:0.8rem; color:var(--text-secondary); display:block">Coût estimé de l'intervention</span>
                            <div style="font-size:1.6rem; font-weight:800; color:var(--secondary); font-family:'Outfit',sans-serif">
                                {{ number_format($repair->estimated_cost_min, 0) }} DT – {{ number_format($repair->estimated_cost_max, 0) }} DT
                            </div>
                        </div>
                        <div style="width:48px; height:48px; border-radius:50%; background:rgba(67,217,173,0.15); display:flex; align-items:center; justify-content:center">
                            <span class="material-icons-round" style="color:var(--secondary); font-size:1.8rem">savings</span>
                        </div>
                    </div>

                    <div style="margin-bottom:1.5rem">
                        <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.75rem">
                            Techniques de réparation suggérées :
                        </div>
                        <ul style="display:flex; flex-direction:column; gap:0.5rem; list-style:none; padding:0; margin:0">
                            @foreach ($suggestedRepairs as $step)
                                <li style="display:flex; align-items:center; gap:0.6rem; padding:0.65rem 0.85rem; background:#f8fafc; border:1px solid var(--border); border-radius:8px; font-size:0.88rem; color:var(--text-primary)">
                                    <span class="material-icons-round" style="font-size:1.1rem; color:var(--primary)">check_circle</span>
                                    <span>{{ $step }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Atelier sélectionné ou bouton pour en choisir un -->
                    @if ($repair->workshop)
                        <div style="background:rgba(108,99,255,0.08); border:1px solid rgba(108,99,255,0.25); border-radius:12px; padding:1rem; margin-bottom:1.25rem">
                            <span style="font-size:0.72rem; color:var(--primary); text-transform:uppercase; font-weight:700; display:block">Atelier Partenaire Attribué</span>
                            <strong style="font-size:1.1rem; color:var(--text-primary)">{{ $repair->workshop->name }}</strong>
                            <p style="font-size:0.85rem; color:var(--text-secondary); margin:0.25rem 0 0">{{ $repair->workshop->address }} ({{ $repair->workshop->city }})</p>
                        </div>
                    @endif

                    <a href="{{ route('reparations.workshops', $repair->id) }}" class="btn btn-primary" style="width:100%; justify-content:center; padding:0.9rem; font-size:0.95rem">
                        <span class="material-icons-round">store</span>
                        {{ $repair->workshop ? 'Changer d\'atelier partenaire' : 'Confier la réparation à un atelier partenaire' }}
                    </a>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
window.onWorkshopSaved = function(newWorkshop) {
    const name = newWorkshop?.name ?? 'L\'atelier';
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;bottom:28px;right:28px;z-index:99999;padding:.9rem 1.4rem;border-radius:12px;background:rgba(67,217,173,.15);border:1px solid rgba(67,217,173,.4);color:#43D9AD;font-size:.9rem;font-weight:600;display:flex;align-items:center;gap:.6rem;box-shadow:0 8px 24px rgba(0,0,0,.1);';
    toast.innerHTML = `<span class="material-icons-round">check_circle</span> <span>${name} ajouté avec succès !</span>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
};
</script>
@endpush
