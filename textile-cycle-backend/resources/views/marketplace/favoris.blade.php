@extends('layouts.app')
@section('title', 'Mes Favoris')
@section('breadcrumb', 'Marketplace › Mes Favoris')

@section('content')
<div class="animate-fade-in-up">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
        <div>
            <h1 class="page-title">Mes Favoris</h1>
            <p class="page-subtitle">{{ $favoris->count() }} article{{ $favoris->count() > 1 ? 's' : '' }} sauvegardé{{ $favoris->count() > 1 ? 's' : '' }}</p>
        </div>
        <a href="{{ route('marketplace.index') }}" class="btn btn-secondary">
            <span class="material-icons-round">storefront</span> Marketplace
        </a>
    </div>

    @if($favoris->isEmpty())
        <div class="card" style="text-align:center;padding:3rem">
            <span class="material-icons-round" style="font-size:3rem;color:var(--text-muted)">favorite_border</span>
            <p style="color:var(--text-muted);margin-top:.75rem">Vous n'avez pas encore de favoris.</p>
            <a href="{{ route('marketplace.index') }}" class="btn btn-primary" style="margin-top:1rem;display:inline-flex">Explorer le marketplace</a>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.25rem">
            @foreach($favoris as $f)
                <div class="card" style="overflow:hidden;padding:0">
                    <div style="height:160px;background:rgba(108,99,255,.1);display:flex;align-items:center;justify-content:center;font-size:3rem">
                        @if($f->article->image_url)
                            <img src="{{ asset('storage/'.$f->article->image_url) }}" style="width:100%;height:100%;object-fit:cover" alt="{{ $f->article->titre }}">
                        @else 👕 @endif
                    </div>
                    <div style="padding:1rem">
                        <div style="font-weight:700;color:var(--text-primary);margin-bottom:.25rem">{{ $f->article->titre }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem">{{ $f->article->taille }} • {{ $f->article->categorie }}</div>
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <span style="font-family:'Outfit',sans-serif;font-weight:800;font-size:1.2rem;color:var(--primary-light)">
                                {{ $f->article->type == 'don' ? 'Gratuit' : ($f->article->type == 'echange' ? 'Échange' : number_format($f->article->prix,2).' DT') }}
                            </span>
                            <a href="{{ route('marketplace.show', $f->article) }}" class="btn btn-secondary btn-sm">Voir</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
