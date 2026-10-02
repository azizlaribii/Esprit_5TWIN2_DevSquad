<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte | TexTileCycle</title>
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
            --radius-md: 12px;
            --radius-lg: 18px;
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
            max-width:560px;
            background:var(--bg-surface);
            border:1px solid var(--border);
            border-radius:var(--radius-lg);
            padding:2.5rem;
            box-shadow:0 20px 50px rgba(0,0,0,0.5);
            position:relative;
            z-index:10;
        }

        .logo-wrap{display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem}
        .logo-box{width:40px;height:40px;border-radius:10px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;color:white}
        .logo-title{font-family:'Outfit',sans-serif;font-size:1.25rem;font-weight:800}

        .auth-heading{font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:700;margin-bottom:.35rem}
        .auth-sub{font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem}

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

        .form-group{margin-bottom:1.15rem}
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
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 6px 25px rgba(108,99,255,0.5)}

        .alert{
            padding:.75rem 1rem;
            border-radius:var(--radius-md);
            font-size:.85rem;
            margin-bottom:1.25rem;
            display:flex;
            align-items:center;
            gap:.5rem;
            background:rgba(255,101,132,0.12);
            border:1px solid rgba(255,101,132,0.3);
            color:var(--accent-red);
        }
    </style>
</head>
<body>

<div class="glow-orb orb-1"></div>
<div class="glow-orb orb-2"></div>

<div class="auth-container">
    <div class="logo-wrap">
        <div class="logo-box">
            <span class="material-icons-round">recycling</span>
        </div>
        <div class="logo-title">TexTile<span style="color:var(--secondary)">Cycle</span></div>
    </div>

    <h2 class="auth-heading">Créer un compte</h2>
    <p class="auth-sub">Rejoignez la communauté de l'économie circulaire</p>

    <div class="auth-tabs">
        <a href="{{ route('login') }}" class="tab-btn">Se connecter</a>
        <a href="{{ route('register') }}" class="tab-btn active">Créer un compte</a>
    </div>

    @if($errors->any())
        <div class="alert">
            <span class="material-icons-round">error_outline</span>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.post') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="name">Nom complet ou Entité</label>
            <div class="input-wrap">
                <span class="material-icons-round">person</span>
                <input type="text" name="name" id="name" class="form-control"
                       placeholder="Ex: Camille Dupont" value="{{ old('name') }}" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Adresse E-mail</label>
            <div class="input-wrap">
                <span class="material-icons-round">email</span>
                <input type="email" name="email" id="email" class="form-control"
                       placeholder="nom@exemple.com" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="role">Vous êtes :</label>
            <select name="role" id="role" class="form-control" style="padding-left:1rem">
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Particulier (Vente, Échange, Don)</option>
                <option value="atelier" {{ old('role') === 'atelier' ? 'selected' : '' }}>Atelier de Couture & Retouche</option>
                <option value="association" {{ old('role') === 'association' ? 'selected' : '' }}>Association Caritative</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Mot de passe</label>
            <div class="input-wrap">
                <span class="material-icons-round">lock</span>
                <input type="password" name="password" id="password" class="form-control"
                       placeholder="Minimum 6 caractères" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
            <div class="input-wrap">
                <span class="material-icons-round">lock_outline</span>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                       placeholder="Retapez le mot de passe" required>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <span>Créer mon compte</span>
            <span class="material-icons-round">how_to_reg</span>
        </button>
    </form>
</div>

</body>
</html>
