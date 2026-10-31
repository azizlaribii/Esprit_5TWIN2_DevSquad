@extends('layouts.app')

@section('title', 'Vérification en deux étapes')

@section('content')
<div style="min-height:80vh; display:flex; align-items:center; justify-content:center; padding:1.5rem">
    <div style="width:100%; max-width:440px">
        <div class="card" style="padding:2.5rem; border-radius:18px; box-shadow:0 10px 30px rgba(15,23,42,0.06); text-align:center">
            
            <div style="width:68px; height:68px; border-radius:50%; background:#EEF2FF; color:var(--primary); display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem">
                <span class="material-icons-round" style="font-size:2.2rem">phonelink_lock</span>
            </div>

            <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-bottom:.35rem">Double authentification</h1>
            <p style="color:var(--text-secondary); font-size:.875rem; margin-bottom:1.5rem">
                Saisissez le code à 6 chiffres de votre application TOTP ou un code de récupération.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom:1.25rem; text-align:left">
                    <span class="material-icons-round">error_outline</span>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.login') }}" id="two-factor-form" style="display:flex; flex-direction:column; gap:1.25rem">
                @csrf
                <div id="totp-container" class="form-group" style="margin-bottom:0">
                    <label class="form-label" style="font-size:.85rem; color:var(--text-secondary)">Code à 6 chiffres</label>
                    <input type="text" name="code" id="code-input" autofocus
                           inputmode="numeric" maxlength="6" autocomplete="one-time-code"
                           class="form-control" style="text-align:center; font-size:1.4rem; letter-spacing:4px; font-weight:700; padding:.75rem"
                           placeholder="000000">
                </div>

                <div id="recovery-container" class="form-group" style="margin-bottom:0; display:none">
                    <label class="form-label" style="font-size:.85rem; color:var(--text-secondary)">Code de secours</label>
                    <input type="text" name="recovery_code" id="recovery-input"
                           class="form-control" style="text-align:center; font-family:monospace; font-size:1.1rem; letter-spacing:2px; font-weight:600; padding:.75rem"
                           placeholder="xxxxx-xxxxx">
                </div>

                <button type="submit" class="btn btn-primary" style="justify-content:center; padding:.8rem">
                    <span>Valider et accéder</span>
                    <span class="material-icons-round">arrow_forward</span>
                </button>

                <button type="button" id="toggle-recovery-btn" onclick="toggleRecovery()" style="background:none; border:none; color:var(--primary); font-size:.85rem; cursor:pointer; text-decoration:underline; padding:.25rem 0">
                    Utiliser un code de récupération d'urgence
                </button>
            </form>

            <script>
            let usingRecovery = false;
            function toggleRecovery() {
                usingRecovery = !usingRecovery;
                document.getElementById('totp-container').style.display = usingRecovery ? 'none' : 'block';
                document.getElementById('recovery-container').style.display = usingRecovery ? 'block' : 'none';
                document.getElementById('toggle-recovery-btn').innerText = usingRecovery ? 'Utiliser un code d\'authentification (TOTP)' : 'Utiliser un code de récupération d\'urgence';
                if (usingRecovery) {
                    document.getElementById('code-input').value = '';
                    document.getElementById('recovery-input').focus();
                } else {
                    document.getElementById('recovery-input').value = '';
                    document.getElementById('code-input').focus();
                }
            }
            </script>

            <div style="margin-top:1.5rem; padding-top:1.25rem; border-top:1px solid var(--border)">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:var(--text-muted); font-size:.85rem; cursor:pointer; text-decoration:underline">
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
