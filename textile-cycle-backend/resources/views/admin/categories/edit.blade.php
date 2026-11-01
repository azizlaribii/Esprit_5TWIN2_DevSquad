@extends('layouts.app')

@section('title', 'Modifier la catégorie')
@section('breadcrumb', 'Administration › Upcycling › Modifier')

@section('content')
<div class="animate-fade-in-up" style="max-width:700px; margin:0 auto">
    <h1 class="page-title">Modifier la catégorie</h1>
    <p class="page-subtitle">{{ $categorie->nom }}</p>

    @include('admin.categories._form', ['action' => route('admin.categories.update', $categorie)])
</div>
@endsection
