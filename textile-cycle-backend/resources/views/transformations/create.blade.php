@extends('layouts.app')

@section('title', 'Nouveau projet d\'upcycling')
@section('breadcrumb', 'Gestion › Upcycling › Nouveau projet')

@section('content')
<div class="animate-fade-in-up" style="max-width:900px; margin:0 auto">
    <h1 class="page-title">Nouveau projet d'upcycling</h1>
    <p class="page-subtitle">Donnez une seconde vie à un vêtement : laissez l'IA vous inspirer.</p>

    @include('transformations._form', ['action' => route('transformations.store'), 'transformation' => null])
</div>
@endsection
