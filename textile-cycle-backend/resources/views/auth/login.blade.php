<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | TexTileCycle</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        :root {
            --primary: #6C63FF;
            --primary-light: #8B85FF;
            --secondary: #43D9AD;
            --accent-red: #FF6584;
            --accent-orange: #FFA726;
            --bg-main: #0A0E1A;
            --bg-surface: #111827;
            --bg-card: #1A2235;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
            --text-muted: #64748B;
            --border: rgba(255,255,255,0.08);
            --gradient-primary: linear-gradient(135deg, #6C63FF 0%, #FF6584 100%);
            --gradient-green: linear-gradient(135deg, #43D9AD 0%, #29B6F6 100%);
            --radius-md: 12px;
            --radius-lg: 18px;
            --shadow-glow: 0 0 35px rgba(108,99,255,0.25);
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:'Inter',sans-serif;
            background:var(--bg-main);
            color:var(--text-primary);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:1.5rem;
            position:relative;
            overflow-x:hidden;
        }
        .glow-orb{
            position:absolute;
            border-radius:50%;
            filter:blur(90px);
            pointer-events:none;
            opacity:0.35;
        }
        .orb-1{top:-10%;left:10%;width:350px;height:350px;background:var(--primary)}
        .orb-2{bottom:-10%;right:10%;width:350px;height:350px;background:var(--secondary)}

        .auth-container{
            width:100%;
            max-width:960px;
            display:grid;
            grid-template-columns:1fr 1fr;
            background:var(--bg-surface);
            border:1px solid var(--border);
            border-radius:var(--radius-lg);
            overflow:hidden;
            box-shadow:0 20px 50px rgba(0,0,0,0.5);
            position:relative;
            z-index:10;
        }

        /* Banner Left */
        .auth-banner{
            background:linear-gradient(135deg, rgba(108,99,255,0.15), rgba(67,217,173,0.08));
            padding:3rem 2.5rem;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            border-right:1px solid var(--border);
        }
        .brand-badge{
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            padding:.4rem .85rem;
            background:rgba(67,217,173,0.15);
            border:1px solid rgba(67,217,173,0.3);
            border-radius:100px;
            color:var(--secondary);
            font-size:.78rem;
            font-weight:700;
            width:fit-content;
        }
        .banner-title{
            font-family:'Outfit',sans-serif;
            font-size:2rem;
            font-weight:800;
            line-height:1.2;
            margin:1.5rem 0 1rem;
        }
        .gradient-text{
            background:var(--gradient-primary);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
        }
        .banner-desc{
            font-size:.9rem;
            color:var(--text-secondary);
            line-height:1.6;
        }
        .features-list{
            margin-top:2rem;
            display:flex;
            flex-direction:column;
            gap:1rem;
        }
        .feat-item{
            display:flex;
            align-items:center;
            gap:.75rem;
            font-size:.85rem;
            color:var(--text-secondary);
        }
        .feat-icon{
            width:32px;
            height:32px;
            border-radius:8px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:1.1rem;
            flex-shrink:0;
        }

        /* Card Right */
        .auth-card{
            padding:3rem 2.5rem;
            display:flex;
            flex-direction:column;
            justify-content:center;
        }
        .logo-wrap{
            display:flex;
            align-items:center;
            gap:.75rem;
            margin-bottom:1.5rem;
        }
        .logo-box{
            width:42px;
            height:42px;
            border-radius:10px;
            background:var(--gradient-primary);
            display:flex;
            align-items:center;
            justify-content:center;
            color:white;
        }
        .logo-title{
            font-family:'Outfit',sans-serif;
            font-size:1.25rem;
            font-weight:800;
            color:var(--text-primary);
        }
        .auth-heading{
            font-family:'Outfit',sans-serif;
            font-size:1.5rem;
            font-weight:700;
            margin-bottom:.35rem;
        }
        .auth-sub{
            font-size:.85rem;
            color:var(--text-muted);
            margin-bottom:1.5rem;
        }

        .auth-tabs{
            display:flex;
            background:rgba(255,255,255,0.04);
            border-radius:var(--radius-md);
            padding:4px;
            margin-bottom:1.5rem;
            border:1px solid var(--border);
        }
        .tab-btn{
            flex:1;
            padding:.6rem 0;
            text-align:center;
            font-size:.85rem;
            font-weight:600;
            color:var(--text-muted);
            border-radius:8px;
            text-decoration:none;
            transition:all .2s ease;
        }
        .tab-btn.active{
            background:var(--primary);
            color:white;
            box-shadow:0 2px 10px rgba(108,99,255,0.4);
        }

        .form-group{margin-bottom:1.25rem}
        .form-label{display:block;font-size:.825rem;font-weight:600;color:var(--text-secondary);margin-bottom:.4rem}
        .input-wrap{position:relative}
        .input-wrap .material-icons-round{
            position:absolute;
            left:1rem;
            top:50%;
            transform:translateY(-50%);
            color:var(--text-muted);
            font-size:1.2rem;
        }
        .form-control{
            width:100%;
            padding:.75rem 1rem .75rem 2.75rem;
            background:rgba(255,255,255,0.04);
            border:1px solid var(--border);
            border-radius:var(--radius-md);
            color:var(--text-primary);
            font-size:.9rem;
            font-family:'Inter',sans-serif;
            transition:all .2s ease;
        }
        .form-control:focus{
            outline:none;
            border-color:var(--primary);
            background:rgba(108,99,255,0.06);
            box-shadow:0 0 0 3px rgba(108,99,255,0.15);
        }
        .toggle-pwd{
            position:absolute;
            right:1rem;
            top:50%;
            transform:translateY(-50%);
            background:none;
            border:none;
            color:var(--text-muted);
            cursor:pointer;
            font-size:1.1rem;
        }

        .btn-submit{
            width:100%;
            padding:.85rem;
            background:var(--gradient-primary);
            color:white;
            border:none;
            border-radius:var(--radius-md);
            font-size:.95rem;
            font-weight:700;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:.5rem;
            box-shadow:0 4px 15px rgba(108,99,255,0.35);
            transition:all .25s ease;
            font-family:'Inter',sans-serif;
            margin-top:1.5rem;
        }
        .btn-submit:hover{
            transform:translateY(-2px);
            box-shadow:0 6px 25px rgba(108,99,255,0.5);
        }

        .alert{
            padding:.75rem 1rem;
            border-radius:var(--radius-md);
            font-size:.85rem;
            margin-bottom:1.25rem;
            display:flex;
            align-items:center;
            gap:.5rem;
        }
        .alert-danger{
            background:rgba(255,101,132,0.12);
            border:1px solid rgba(255,101,132,0.3);
            color:var(--accent-red);
        }
        .alert-success{
            background:rgba(67,217,173,0.12);
            border:1px solid rgba(67,217,173,0.3);
            color:var(--secondary);
        }

        /* Demo accounts helper */
        .demo-helpers{
            margin-top:1.75rem;
            padding-top:1.25rem;
            border-top:1px solid var(--border);
        }
        .demo-title{
            font-size:.75rem;
            font-weight:700;
            color:var(--text-muted);
            text-transform:uppercase;
            letter-spacing:.5px;
            margin-bottom:.65rem;
        }
        .demo-chips{
            display:flex;
            flex-wrap:wrap;
            gap:.5rem;
        }
        .demo-chip{
            padding:.35rem .65rem;
            background:rgba(255,255,255,0.04);
            border:1px solid var(--border);
            border-radius:8px;
            font-size:.75rem;
            color:var(--text-secondary);
            cursor:pointer;
            transition:all .2s ease;
        }
        .demo-chip:hover{
            background:rgba(108,99,255,0.15);
            border-color:var(--primary);
            color:var(--primary-light);
        }

        @media(max-width:768px){
            .auth-container{grid-template-columns:1fr}
            .auth-banner{display:none}
            .auth-card{padding:2rem 1.5rem}
        }
    </style>
</head>
<body>

<div class="glow-orb orb-1"></div>
<div class="glow-orb orb-2"></div>

<div class="auth-container">

    {{-- Banner Gauche --}}
    <div class="auth-banner">
        <div>
            <div class="brand-badge">
                <span class="material-icons-round" style="font-size:1rem">eco</span>
                Économie Circulaire Textile
            </div>
            <h1 class="banner-title">
                Donnez une <span class="gradient-text">seconde vie</span> à vos vêtements.
            </h1>
            <p class="banner-desc">
                Rejoignez la plateforme intelligente connectant citoyens, artisans réparateurs et associations pour un impact écologique maximal.
            </p>

            <div class="features-list">
                <div class="feat-item">
                    <div class="feat-icon" style="background:rgba(108,99,255,.2);color:#6C63FF">
                        <span class="material-icons-round">storefront</span>
                    </div>
                    <div>
                        <strong style="color:var(--text-primary)">Marketplace Circulaire</strong>
                        <div style="font-size:.78rem">Vente, échange et don de vêtements avec IA</div>
                    </div>
                </div>

                <div class="feat-item">
                    <div class="feat-icon" style="background:rgba(255,101,132,.2);color:#FF6584">
                        <span class="material-icons-round">build</span>
                    </div>
                    <div>
                        <strong style="color:var(--text-primary)">Ateliers & Réparations</strong>
                        <div style="font-size:.78rem">Artisans certifiés et retouches de qualité</div>
                    </div>
                </div>

                <div class="feat-item">
                    <div class="feat-icon" style="background:rgba(67,217,173,.2);color:#43D9AD">
                        <span class="material-icons-round">volunteer_activism</span>
                    </div>
                    <div>
                        <strong style="color:var(--text-primary)">Dons Solidaires</strong>
                        <div style="font-size:.78rem">Redistribution aux associations caritatives</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="font-size:.8rem;color:var(--text-muted);display:flex;align-items:center;gap:.5rem">
            <span class="material-icons-round" style="color:var(--secondary);font-size:1.1rem">verified</span>
            Plateforme propulsée par Laravel & IA
        </div>
    </div>

    {{-- Formulaire Droit --}}
    <div class="auth-card">
        <div class="logo-wrap">
            <div class="logo-box">
                <span class="material-icons-round">recycling</span>
            </div>
            <div class="logo-title">TexTile<span style="color:var(--secondary)">Cycle</span></div>
        </div>

        <h2 class="auth-heading">Bienvenue !</h2>
        <p class="auth-sub">Connectez-vous pour accéder à votre espace personnalisé</p>

        {{-- Onglets --}}
        <div class="auth-tabs">
            <a href="{{ route('login') }}" class="tab-btn active">Se connecter</a>
            <a href="{{ route('register') }}" class="tab-btn">Créer un compte</a>
        </div>

        {{-- Flash messages / erreurs --}}
        @if(session('success'))
            <div class="alert alert-success">
                <span class="material-icons-round">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <span class="material-icons-round">error_outline</span>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Formulaire de connexion --}}
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Adresse E-mail</label>
                <div class="input-wrap">
                    <span class="material-icons-round">email</span>
                    <input type="email" name="email" id="email" class="form-control"
                           placeholder="nom@exemple.com" value="{{ old('email', 'marie@example.com') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem">
                    <label class="form-label" for="password" style="margin-bottom:0">Mot de passe</label>
                    <span style="font-size:.78rem;color:var(--primary-light)">Mot de passe par défaut : password</span>
                </div>
                <div class="input-wrap">
                    <span class="material-icons-round">lock</span>
                    <input type="password" name="password" id="password" class="form-control"
                           placeholder="••••••••" value="password" required>
                    <button type="button" class="toggle-pwd" onclick="togglePassword()">
                        <span class="material-icons-round" id="eye-icon">visibility</span>
                    </button>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;font-size:.825rem">
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;color:var(--text-secondary)">
                    <input type="checkbox" name="remember" value="1" checked>
                    Se souvenir de moi
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <span>Se connecter</span>
                <span class="material-icons-round">arrow_forward</span>
            </button>
        </form>

        {{-- Comptes de démonstration pré-remplis --}}
        <div class="demo-helpers">
            <div class="demo-title">⚡ Remplissage rapide (Comptes Démo)</div>
            <div class="demo-chips">
                <button type="button" class="demo-chip" onclick="fillDemo('marie@example.com', 'password')">
                    👤 Marie L. (Particulier)
                </button>
                <button type="button" class="demo-chip" onclick="fillDemo('ahmed@example.com', 'password')">
                    🧵 Ahmed K. (Atelier)
                </button>
                <button type="button" class="demo-chip" onclick="fillDemo('sophie@example.com', 'password')">
                    🤝 Sophie M. (Association)
                </button>
            </div>
        </div>

    </div>

</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility';
    }
}

function fillDemo(email, pwd) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pwd;
}
</script>

</body>
</html>
