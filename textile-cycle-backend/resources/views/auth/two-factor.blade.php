@extends('layouts.app')

@section('title', 'Authentification à deux facteurs')

@section('content')
<div style="max-width:720px; margin:0 auto; padding:1.5rem 0">
    <div class="card" style="padding:0; overflow:hidden; border-radius:18px">
        <!-- Header -->
        <div style="background:var(--gradient-primary); padding:2rem 2.5rem; color:white">
            <h1 style="font-size:1.6rem; font-weight:800; color:white; margin-bottom:.35rem">🔐 Authentification à deux facteurs (2FA)</h1>
            <p style="color:rgba(255,255,255,.85); font-size:.9rem; margin:0">Renforcez la sécurité de votre compte avec une application TOTP (Google Authenticator, Authy)</p>
        </div>

        <div style="padding:2.5rem">
            @if (session('success'))
                <div class="alert alert-success" style="margin-bottom:1.5rem">
                    <span class="material-icons-round">check_circle</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom:1.5rem">
                    <span class="material-icons-round">error_outline</span>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            @if (session('recovery_codes'))
                <div style="margin-bottom:2rem; padding:1.5rem; background:#FFFBEB; border:1px solid #FDE68A; border-radius:14px">
                    <h3 style="font-size:1rem; font-weight:700; color:#92400E; margin-bottom:.5rem">⚠️ Codes de récupération d'urgence</h3>
                    <p style="color:#B45309; font-size:.85rem; margin-bottom:1rem">Conservez ces codes à usage unique dans un endroit sûr. Ils ne seront plus affichés.</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:.5rem">
                        @foreach (session('recovery_codes') as $code)
                            <code style="background:#FFFFFF; border:1px solid #FCD34D; border-radius:8px; padding:.45rem .75rem; font-family:monospace; font-size:.85rem; text-align:center; color:#78350F">{{ $code }}</code>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (auth()->user()->two_factor_enabled)
                <!-- Statut Activé -->
                <div style="display:flex; align-items:center; gap:1rem; padding:1.25rem; background:#ECFDF5; border:1px solid #A7F3D0; border-radius:14px; margin-bottom:2rem">
                    <div style="width:42px; height:42px; border-radius:50%; background:#10B981; color:white; display:flex; align-items:center; justify-content:center; flex-shrink:0">
                        <span class="material-icons-round" style="font-size:1.5rem">shield</span>
                    </div>
                    <div>
                        <div style="font-weight:700; color:#065F46; font-size:.95rem">La protection 2FA est activée</div>
                        <div style="font-size:.825rem; color:#047857">Votre compte est sécurisé par un code à 6 chiffres lors de chaque nouvelle connexion.</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('2fa.disable') }}"
                      onsubmit="return confirm('Désactiver le 2FA réduira la sécurité de votre compte. Confirmer ?')">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="password">Confirmez votre mot de passe pour désactiver</label>
                        <input type="password" name="password" id="password" required class="form-control" placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn btn-danger" style="padding:.75rem 1.25rem">
                        <span class="material-icons-round">no_encryption</span>
                        <span>Désactiver le 2FA</span>
                    </button>
                </form>

            @elseif (auth()->user()->two_factor_secret && (!empty($qrCodeSvg) || !empty($qrCode)))
                <!-- Étape 2 : Scanner le QR code -->
                <div style="text-align:center; margin-bottom:2rem">
                    <h2 style="font-size:1.25rem; font-weight:700; color:var(--text-primary); margin-bottom:.5rem">Étape 2 : Scannez le QR Code</h2>
                    <p style="color:var(--text-secondary); font-size:.875rem; margin-bottom:1.25rem">
                        Ouvrez votre application d'authentification (Google Authenticator, Microsoft Authenticator ou Authy) et scannez le code ci-dessous :
                    </p>
                    <div style="display:inline-block; padding:1rem; background:#FFFFFF; border:2px solid var(--border); border-radius:16px; box-shadow:0 4px 16px rgba(0,0,0,0.04)">
                        @if(!empty($qrCodeSvg))
                            <div style="display:flex; justify-content:center; align-items:center">
                                {!! $qrCodeSvg !!}
                            </div>
                        @elseif(!empty($qrCode))
                            <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code 2FA" style="width:200px; height:200px; display:block">
                        @endif
                    </div>

                    @if(!empty($secret))
                        <div style="margin-top:1.25rem">
                            <span style="font-size:.8rem; color:var(--text-secondary)">Ou saisissez manuellement la clé secrète :</span>
                            <div style="margin-top:.35rem">
                                <code style="background:#F3F4F6; border:1px solid #E5E7EB; border-radius:8px; padding:.35rem .75rem; font-family:monospace; font-size:.85rem; letter-spacing:1px; color:#1F2937; word-break:break-all">{{ $secret }}</code>
                            </div>
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('2fa.confirm') }}">
                    @csrf
                    <div class="form-group" style="text-align:center">
                        <label class="form-label" for="code">Saisissez le code à 6 chiffres affiché sur votre application</label>
                        <input type="text" name="code" id="code" required maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                               class="form-control" style="max-width:240px; margin:0 auto; text-align:center; font-size:1.5rem; letter-spacing:4px; font-weight:700"
                               placeholder="123456" autofocus>
                    </div>
                    <div style="display:flex; justify-content:center; gap:1rem; margin-top:1.5rem">
                        <button type="submit" class="btn btn-primary" style="padding:.8rem 2rem">
                            <span class="material-icons-round">check_circle</span>
                            <span>Confirmer et activer le 2FA</span>
                        </button>
                    </div>
                </form>

            @else
                <!-- Étape 1 : Activer -->
                <div style="text-align:center; padding:1.5rem 0">
                    <div style="width:72px; height:72px; border-radius:50%; background:#EEF2FF; color:var(--primary); display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem">
                        <span class="material-icons-round" style="font-size:2.5rem">security</span>
                    </div>
                    <h2 style="font-size:1.3rem; font-weight:700; color:var(--text-primary); margin-bottom:.5rem">Sécurisez votre compte avec la double authentification</h2>
                    <p style="color:var(--text-secondary); font-size:.9rem; max-width:480px; margin:0 auto 1.75rem; line-height:1.6">
                        Lors de votre connexion, un code temporaire généré par votre smartphone vous sera demandé en plus de votre mot de passe habituel.
                    </p>

                    <form method="POST" action="{{ route('2fa.enable') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="padding:.85rem 2rem; font-size:.95rem">
                            <span class="material-icons-round">qr_code</span>
                            <span>Configurer l'authentification 2FA</span>
                        </button>
                    </form>
                </div>
            @endif

            <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid var(--border); text-align:center">
                <a href="{{ route('profile.show') }}" style="color:var(--text-secondary); text-decoration:none; font-size:.875rem">
                    ← Retour à mon profil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
