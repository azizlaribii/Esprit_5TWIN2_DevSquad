@extends('layouts.app')

@section('title', 'Associations Solidaires Partenaires')
@section('meta_description', 'Nos partenaires caritatifs qui redistribuent les vêtements collectés aux bénéficiaires')
@section('breadcrumb', 'Partenaires › Associations')

@section('content')
<div class="animate-fade-in-up">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Associations Solidaires</h1>
            <p class="page-subtitle">Réseau caritatif pour l'inclusion sociale et le don de vêtements</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('dons.index') }}" class="btn btn-primary">
                <span class="material-icons-round">volunteer_activism</span> Voir les Dons
            </a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:1.5rem">
        @forelse($associations as $assoc)
            <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem">
                        <div style="width:48px;height:48px;border-radius:12px;background:rgba(67,217,173,.15);color:var(--secondary);display:flex;align-items:center;justify-content:center">
                            <span class="material-icons-round" style="font-size:1.5rem">groups</span>
                        </div>
                        <span class="badge badge-success" style="font-size:.8rem">
                            {{ $assoc->beneficiaires_aides }} personnes aidées
                        </span>
                    </div>

                    <h3 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);margin-bottom:.35rem">{{ $assoc->nom }}</h3>

                    <p style="font-size:.85rem;color:var(--text-secondary);display:flex;align-items:center;gap:.35rem;margin-top:.75rem">
                        <span class="material-icons-round" style="font-size:1rem;color:var(--text-muted)">location_on</span>
                        {{ $assoc->adresse }}
                    </p>
                </div>

                <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:.8rem;color:var(--text-muted)">Partenaire officiel</span>
                    <a href="{{ route('marketplace.create') }}" class="btn btn-secondary btn-sm">Donner un vêtement</a>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column:1/-1;text-align:center;padding:3rem">
                <p style="color:var(--text-muted)">Aucune association enregistrée.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
