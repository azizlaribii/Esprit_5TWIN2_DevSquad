@extends('layouts.app')

@section('title', 'Modifier la valorisation')
@section('breadcrumb', 'Gestion › Valorisations › Modifier')

@section('content')
<div class="animate-fade-in-up">
    <h1 class="page-title">Modifier la valorisation #{{ $valorisation->id }}</h1>

    <div class="card">
        <form method="POST" action="{{ route('valorisations.update', $valorisation) }}"
              data-confirm
              data-confirm-type="warning"
              data-confirm-title="Modifier la valorisation #{{ $valorisation->id }} ?"
              data-confirm-message="Enregistrer les modifications de cette valorisation ?"
              data-confirm-ok="Oui, modifier">
            @csrf
            @method('PUT')
            @include('valorisations._form')
            <div style="display:flex;gap:.75rem">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('valorisations.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection