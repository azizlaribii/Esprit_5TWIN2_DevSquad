@extends('layouts.app')

@section('title', 'Nouvelle catégorie')
@section('breadcrumb', 'Administration › Upcycling › Nouvelle catégorie')

@section('content')
<div class="animate-fade-in-up" style="max-width:700px; margin:0 auto">
    <h1 class="page-title">Nouvelle catégorie</h1>
    <p class="page-subtitle">Elle sera proposée aux utilisateurs lors de la création d'un projet.</p>

    @include('admin.categories._form', ['action' => route('admin.categories.store'), 'categorie' => null])
</div>
@endsection
