@extends('layouts.app')

@section('title', 'Vérification de l\'e-mail')

@section('content')
<div style="min-height:80vh; display:flex; align-items:center; justify-content:center; padding:1.5rem">
    <div style="width:100%; max-width:480px">
        <div class="card" style="padding:2.5rem; border-radius:18px; box-shadow:0 10px 30px rgba(15,23,42,0.06); text-align:center">
            
            <div style="width:68px; height:68px; border-radius:50%; background:#EEF2FF; color:var(--primary); display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem">
                <span class="material-icons-round" style="font-size:2.4rem">mark_email_unread</span>
            </div>

            <h1 style="font-size:1.6rem; font-weight:800; color:var(--text-primary); margin-bottom:.5rem">Vérifiez votre adresse e-mail</h1>
            
            <p style="color:var(--text-secondary); font-size:.9rem; line-height:1.6; margin-bottom:1.5rem">
                Un e-mail de confirmation a été envoyé à <strong>{{ auth()->user()->email ?? 'votre adresse' }}</strong>. Veuillez cliquer sur le lien de confirmation pour débloquer l'accès complet à votre espace.
            </p>

            @if (session('success'))
                <div class="alert alert-success" style="margin-bottom:1.25rem; text-align:left">
                    <span class="material-icons-round">check_circle</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            {{-- Validation directe en local / évaluation --}}
            <div style="margin-bottom:1.5rem; padding:1.25rem; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:12px; text-align:left">
                <div style="display:flex; align-items:center; gap:.5rem; color:#15803D; font-weight:700; font-size:.85rem; margin-bottom:.5rem">
                    <span class="material-icons-round" style="font-size:1.2rem">verified</span>
                    Validation immédiate (Mode Test & Démo) :
                </div>
                <p style="font-size:.78rem; color:#166534; margin-bottom:.75rem">
                    Permet de valider votre adresse e-mail sans attendre la réception du courrier externe.
                </p>
                <form method="POST" action="{{ route('verification.instant') }}">
                    @csrf
                    <button type="submit" class="btn btn-success" style="width:100%; justify-content:center; padding:.65rem">
                        ✅ Valider mon e-mail maintenant
                    </button>
                </form>

                @if(isset($verifyUrl) && $verifyUrl)
                    <div style="margin-top:.75rem; text-align:center">
                        <a href="{{ $verifyUrl }}" style="font-size:.78rem; color:var(--primary); text-decoration:underline">
                            Ou cliquer sur le lien officiel généré
                        </a>
                    </div>
                @endif
            </div>

            {{-- Formulaire officiel de renvoi --}}
            <form method="POST" action="{{ route('verification.send') }}" style="margin-bottom:1rem">
                @csrf
                <button type="submit" class="btn btn-secondary" style="width:100%; justify-content:center; padding:.75rem">
                    <span class="material-icons-round">outgoing_mail</span>
                    <span>Renvoyer l'e-mail de vérification</span>
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background:none; border:none; color:var(--text-muted); font-size:.85rem; cursor:pointer; text-decoration:underline">
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
