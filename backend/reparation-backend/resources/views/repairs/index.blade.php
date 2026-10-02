@extends('layouts.app')

@section('title', 'Mes demandes de réparation')

@section('content')
<div class="page">
    <div class="page-header">
        <h1>Mes demandes de réparation</h1>
        <div style="display:flex; gap:0.75rem; align-items:center">
            <button type="button" onclick="openWorkshopModal()"
                    style="width:auto; padding:0.55rem 1.1rem; font-size:0.85rem; font-weight:700;
                           border:1px solid #22c55e; border-radius:0.6rem; cursor:pointer;
                           background:transparent; color:#22c55e; white-space:nowrap">
                + Ajouter un atelier
            </button>
            <a class="btn-pick" href="{{ route('reparations.create') }}">+ Nouvelle demande</a>
        </div>
    </div>

    @if($repairs->isEmpty())
        <p class="empty-state">Aucune demande de réparation pour l'instant.</p>
    @else
        <div class="card-list">
            @foreach($repairs as $repair)
                <a class="card-row" href="{{ route('reparations.show', $repair->id) }}">
                    <img src="{{ $repair->photo_url }}" alt="{{ $repair->defect_type }}" />
                    <div style="flex:1">
                        <p style="margin:0; font-weight:600">{{ $repair->defect_type ?? 'Analyse en cours' }}</p>
                        <p style="margin:0; color:var(--text-dim); font-size:0.85rem">
                            {{ $repair->location ?? 'Non précisé' }} · {{ ucfirst($repair->severity ?? 'Non évalué') }}
                        </p>
                    </div>
                    <span class="badge">{{ str_replace('_', ' ', $repair->status) }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
