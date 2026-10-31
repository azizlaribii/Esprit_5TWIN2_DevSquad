@extends('layouts.app')

@section('title', 'Mot de passe oublié')

@section('content')
<div style="min-height:80vh; display:flex; align-items:center; justify-content:center; padding:1.5rem">
    <div style="width:100%; max-width:460px">
        <div class="card" style="padding:2.5rem; border-radius:18px; box-shadow:0 10px 30px rgba(15,23,42,0.06)">
            <div style="text-align:center; margin-bottom:1.75rem">
                <div style="width:60px; height:60px; border-radius:50%; background:#EEF2FF; color:var(--primary); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem">
                    <span class="material-icons-round" style="font-size:2rem">lock_reset</span>
                </div>
                <h1 style="font-size:1.6rem; font-weight:800; color:var(--text-primary); margin-bottom:.35rem">Mot de passe oublié</h1>
                <p style="color:var(--text-secondary); font-size:.875rem">Saisissez votre e-mail pour réinitialiser votre mot de passe</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success" style="margin-bottom:1.25rem">
                    <span class="material-icons-round">check_circle</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('dev_reset_url'))
                <div style="margin-bottom:1.5rem; padding:1.15rem; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:12px">
                    <div style="display:flex; align-items:center; gap:.5rem; color:#15803D; font-weight:700; font-size:.85rem; margin-bottom:.5rem">
                        <span class="material-icons-round" style="font-size:1.2rem">bolt</span>
                        Lien de réinitialisation généré (Prêt pour le test) :
                    </div>
                    <a href="{{ session('dev_reset_url') }}" class="btn btn-success" style="width:100%; justify-content:center; padding:.65rem; font-size:.85rem; text-decoration:none">
                        🔑 Ouvrir le formulaire de réinitialisation
                    </a>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom:1.25rem">
                    <span class="material-icons-round">error_outline</span>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" style="display:flex; flex-direction:column; gap:1.15rem">
                @csrf
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="email">Adresse e-mail du compte</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="form-control"
                           placeholder="marie@example.com" autofocus>
                </div>
                <button type="submit" class="btn btn-primary" style="justify-content:center; padding:.8rem">
                    <span class="material-icons-round">send</span>
                    <span>Générer le lien de réinitialisation</span>
                </button>
            </form>

            <div style="margin-top:1.5rem; text-align:center">
                <a href="{{ route('login') }}" style="color:var(--primary); font-size:.85rem; font-weight:600; text-decoration:none">
                    ← Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
