@extends('layouts.app')

@section('title', 'Réinitialiser le mot de passe')

@section('content')
<div style="min-height:80vh; display:flex; align-items:center; justify-content:center; padding:1.5rem">
    <div style="width:100%; max-width:460px">
        <div class="card" style="padding:2.5rem; border-radius:18px; box-shadow:0 10px 30px rgba(15,23,42,0.06)">
            <div style="text-align:center; margin-bottom:1.75rem">
                <div style="width:60px; height:60px; border-radius:50%; background:#EEF2FF; color:var(--primary); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem">
                    <span class="material-icons-round" style="font-size:2rem">key</span>
                </div>
                <h1 style="font-size:1.6rem; font-weight:800; color:var(--text-primary); margin-bottom:.35rem">Nouveau mot de passe</h1>
                <p style="color:var(--text-secondary); font-size:.875rem">Choisissez un mot de passe sécurisé d'au moins 8 caractères</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom:1.25rem">
                    <span class="material-icons-round">error_outline</span>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" style="display:flex; flex-direction:column; gap:1.15rem">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}" required
                           class="form-control" readonly style="background:#F8FAFC">
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="password">Nouveau mot de passe</label>
                    <input type="password" name="password" id="password" required minlength="8"
                           class="form-control" placeholder="Minimum 8 caractères" autofocus>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="form-control" placeholder="Retapez le mot de passe">
                </div>

                <button type="submit" class="btn btn-primary" style="justify-content:center; padding:.8rem; margin-top:.5rem">
                    <span class="material-icons-round">check_circle</span>
                    <span>Enregistrer le nouveau mot de passe</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
