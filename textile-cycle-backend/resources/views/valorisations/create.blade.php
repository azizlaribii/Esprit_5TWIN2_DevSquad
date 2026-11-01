@extends('layouts.app')

@section('title', 'Nouvelle valorisation')
@section('breadcrumb', 'Gestion › Valorisations › Nouvelle')

@section('content')
<div class="animate-fade-in-up">
    <h1 class="page-title">Nouvelle valorisation</h1>
    <p class="page-subtitle">Choisissez la filière de revalorisation d'un dépôt</p>

    <div class="card">
        <form method="POST" action="{{ route('valorisations.store') }}">
            @csrf
            @include('valorisations._form')
            <div style="display:flex;gap:.75rem">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('valorisations.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection