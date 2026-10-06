@extends('layouts.app')

@section('title', 'Modifier le dépôt')
@section('breadcrumb', 'Gestion › Dépôts › Modifier')

@section('content')
<div class="animate-fade-in-up">
    <h1 class="page-title">Modifier le dépôt #{{ $depot->id }}</h1>

    <div class="card" >
        <form method="POST" action="{{ route('depots.update', $depot) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('depots._form')
            <div style="display:flex;gap:.75rem">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('depots.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection