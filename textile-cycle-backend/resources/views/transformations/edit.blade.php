@extends('layouts.app')

@section('title', 'Modifier le projet')
@section('breadcrumb', 'Gestion › Upcycling › Modifier')

@section('content')
<div class="animate-fade-in-up" style="max-width:900px; margin:0 auto">
    <h1 class="page-title">Modifier le projet</h1>
    <p class="page-subtitle">{{ $transformation->titre }}</p>

    @include('transformations._form', ['action' => route('transformations.update', $transformation)])
</div>
@endsection
