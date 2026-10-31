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
            --primary: #5B52E0;
            --primary-light: #7367F0;
            --secondary: #10B981;
            --accent-red: #EF4444;
            --accent-orange: #F59E0B;
            --bg-main: #F8FAFC;
            --bg-surface: #FFFFFF;
            --bg-card: #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --border: #E2E8F0;
            --gradient-primary: linear-gradient(135deg, #5B52E0 0%, #EC4899 100%);
            --gradient-green: linear-gradient(135deg, #10B981 0%, #0EA5E9 100%);
            --radius-md: 12px;
            --radius-lg: 18px;
            --shadow-glow: 0 4px 25px rgba(91,82,224,0.15);
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
            filter:blur(110px);
            pointer-events:none;
            opacity:0.25;
        }
        .orb-1{top:-10%;left:10%;width:350px;height:350px;background:#C7D2FE}
        .orb-2{bottom:-10%;right:10%;width:350px;height:350px;background:#A7F3D0}

        .auth-container{
            width:100%;
            max-width:960px;
            display:grid;
            grid-template-columns:1fr 1fr;
            background:var(--bg-surface);
            border:1px solid var(--border);
            border-radius:var(--radius-lg);
            overflow:hidden;
            box-shadow:0 12px 40px rgba(15,23,42,0.06);
            position:relative;
            z-index:10;
        }

        /* Banner Left */
        .auth-banner{
            background:linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 100%);
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
            background:#ECFDF5;
            border:1px solid #A7F3D0;
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
            color:var(--text-primary);
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
            background:#FFFFFF;
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
            box-shadow:0 4px 12px rgba(91,82,224,0.25);
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
            color:var(--text-primary);
        }
        .auth-sub{
            font-size:.85rem;
            color:var(--text-muted);
            margin-bottom:1.5rem;
        }

        .auth-tabs{
            display:flex;
            background:#F1F5F9;
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
            background:#FFFFFF;
            color:var(--primary);
            box-shadow:0 2px 8px rgba(0,0,0,0.06);
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
            background:#FFFFFF;
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
            background:#FFFFFF;
            box-shadow:0 0 0 3px rgba(91,82,224,0.12);
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
            box-shadow:0 4px 15px rgba(91,82,224,0.25);
            transition:all .25s ease;
            font-family:'Inter',sans-serif;
            margin-top:1.5rem;
        }
        .btn-submit:hover{
            transform:translateY(-2px);
            box-shadow:0 6px 20px rgba(91,82,224,0.4);
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
            background:#FEF2F2;
            border:1px solid #FECACA;
            color:var(--accent-red);
        }
        .alert-success{
            background:#ECFDF5;
            border:1px solid #A7F3D0;
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
            background:#F8FAFC;
            border:1px solid var(--border);
            border-radius:8px;
            font-size:.75rem;
            color:var(--text-secondary);
            cursor:pointer;
            transition:all .2s ease;
        }
        .demo-chip:hover{
            background:#EEF2FF;
            border-color:#C7D2FE;
            color:var(--primary);
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
                    <div class="feat-icon" style="background:#EEF2FF;color:#5B52E0">
                        <span class="material-icons-round">storefront</span>
                    </div>
                    <div>
                        <strong style="color:var(--text-primary)">Marketplace Circulaire</strong>
                        <div style="font-size:.78rem">Vente, échange et don de vêtements avec IA</div>
                    </div>
                </div>
                <div class="feat-item">
                    <div class="feat-icon" style="background:#ECFDF5;color:#10B981">
                        <span class="material-icons-round">build</span>
                    </div>
                    <div>
                        <strong style="color:var(--text-primary)">Diagnostic & Réparation</strong>
                        <div style="font-size:.78rem">Détection IA des défauts et mise en relation ateliers</div>
                    </div>
                </div>
                <div class="feat-item">
                    <div class="feat-icon" style="background:#FEF3C7;color:#F59E0B">
                        <span class="material-icons-round">volunteer_activism</span>
                    </div>
                    <div>
                        <strong style="color:var(--text-primary)">Dons Intelligents</strong>
                        <div style="font-size:.78rem">Matching automatisé avec les besoins associatifs</div>
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
        <form method="POST" action="{{ route('login') }}">
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
                    <span style="font-size:.78rem;color:var(--primary)">Mot de passe : password</span>
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
                <a href="{{ route('password.request') }}" style="color:var(--primary);text-decoration:none;font-weight:500;">Mot de passe oublié ?</a>
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

// Auto-dismiss alerts after 6.5s
document.querySelectorAll('.alert').forEach(function(alertEl) {
    setTimeout(function() {
        alertEl.style.transition = 'opacity 0.4s ease, transform 0.4s ease, max-height 0.4s ease, margin 0.4s ease';
        alertEl.style.opacity = '0';
        alertEl.style.transform = 'translateY(-10px)';
        alertEl.style.maxHeight = '0';
        alertEl.style.marginBottom = '0';
        alertEl.style.paddingTop = '0';
        alertEl.style.paddingBottom = '0';
        alertEl.style.overflow = 'hidden';
        setTimeout(function() { if (alertEl.parentNode) alertEl.parentNode.removeChild(alertEl); }, 450);
    }, 6500);
});
</script>

</body>
</html>
