@extends('layouts.app')

@section('title', 'Nouveau dépôt')
@section('breadcrumb', 'Gestion › Dépôts › Nouveau')

@section('content')
<div class="animate-fade-in-up">
    <h1 class="page-title">Déposer des vêtements</h1>
    <p class="page-subtitle">Renseignez les informations du dépôt</p>

    <div class="card" >
        <form method="POST" action="{{ route('depots.store') }}" enctype="multipart/form-data">
            @csrf
            @include('depots._form')
            <div style="display:flex;gap:.75rem">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('depots.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection