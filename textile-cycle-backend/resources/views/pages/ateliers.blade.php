@extends('layouts.app')

@section('title', 'Ateliers Partenaires & Artisans')
@section('meta_description', 'Réseau d\'ateliers de couture, retouche et upcycling textile éco-responsables')
@section('breadcrumb', 'Partenaires › Ateliers')

@section('content')
<div class="animate-fade-in-up">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Ateliers de Réparation & Upcycling</h1>
            <p class="page-subtitle">Nos maîtres artisans et ateliers textiles partenaires qualifiés</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('reparations.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">build</span> Réparations en cours
            </a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:1.5rem">
        @forelse($ateliers as $atelier)
            <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem">
                        <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,167,38,.15);color:var(--accent-orange);display:flex;align-items:center;justify-content:center">
                            <span class="material-icons-round" style="font-size:1.5rem">precision_manufacturing</span>
                        </div>
                        <span class="stars" style="color:#FFA726;font-weight:700;font-size:1.05rem">
                            ★ {{ number_format($atelier->note, 1) }}
                        </span>
                    </div>

                    <h3 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);margin-bottom:.35rem">{{ $atelier->nom }}</h3>
                    <div style="font-size:.85rem;color:var(--secondary);font-weight:600;margin-bottom:.75rem">
                        <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle">auto_awesome</span> {{ $atelier->specialite }}
                    </div>

                    <p style="font-size:.85rem;color:var(--text-secondary);display:flex;align-items:center;gap:.35rem">
                        <span class="material-icons-round" style="font-size:1rem;color:var(--text-muted)">location_on</span>
                        {{ $atelier->adresse }}
                    </p>
                </div>

                <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
                    <span class="badge badge-success">Atelier certifié</span>
                    <a href="{{ route('reparations.index') }}" class="btn btn-secondary btn-sm">Confier un vêtement</a>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column:1/-1;text-align:center;padding:3rem">
                <p style="color:var(--text-muted)">Aucun atelier enregistré.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
