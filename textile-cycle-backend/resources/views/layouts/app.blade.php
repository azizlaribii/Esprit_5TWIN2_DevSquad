<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'TexTileCycle – Plateforme de mode circulaire')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TexTileCycle') | Marketplace Circulaire</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        :root {
            --primary: #6C63FF; --primary-light: #8B85FF; --primary-dark: #4A42D6;
            --primary-glow: rgba(108,99,255,0.3); --secondary: #43D9AD;
            --accent-red: #FF6584; --accent-orange: #FFA726; --accent-blue: #29B6F6;
            --bg-main: #0A0E1A; --bg-surface: #111827; --bg-card: #1A2235; --bg-card-hover: #1E2940;
            --text-primary: #F1F5F9; --text-secondary: #94A3B8; --text-muted: #475569;
            --border: rgba(255,255,255,0.06); --border-active: rgba(108,99,255,0.5);
            --sidebar-width: 260px; --sidebar-bg: #0D1526; --sidebar-border: rgba(255,255,255,0.05);
            --topbar-height: 64px; --shadow-md: 0 4px 16px rgba(0,0,0,0.4);
            --shadow-glow: 0 0 24px rgba(108,99,255,0.2); --radius-sm: 8px; --radius-md: 12px; --radius-lg: 16px;
            --transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
            --gradient-primary: linear-gradient(135deg,#6C63FF 0%,#FF6584 100%);
            --gradient-green: linear-gradient(135deg,#43D9AD 0%,#29B6F6 100%);
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{font-size:16px;scroll-behavior:smooth;-webkit-font-smoothing:antialiased}
        body{font-family:'Inter',sans-serif;background:var(--bg-main);color:var(--text-primary);overflow-x:hidden;min-height:100vh}
        a{color:var(--primary);text-decoration:none;transition:var(--transition)} a:hover{color:var(--primary-light)}
        ::-webkit-scrollbar{width:6px} ::-webkit-scrollbar-track{background:var(--bg-surface)}
        ::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px} ::-webkit-scrollbar-thumb:hover{background:var(--primary)}
        h1,h2,h3,h4,h5,h6{font-family:'Outfit',sans-serif;font-weight:700;line-height:1.2;color:var(--text-primary)}
        .app-shell{display:flex;min-height:100vh}
        .main-content{flex:1;margin-left:var(--sidebar-width);display:flex;flex-direction:column;min-height:100vh}
        .page-content{flex:1;padding:24px}
        .sidebar{position:fixed;left:0;top:0;height:100vh;width:var(--sidebar-width);background:var(--sidebar-bg);border-right:1px solid var(--sidebar-border);display:flex;flex-direction:column;z-index:100;overflow:hidden}
        .sidebar-logo{display:flex;align-items:center;gap:.75rem;padding:1.25rem 1rem;border-bottom:1px solid var(--sidebar-border);min-height:72px}
        .logo-icon{width:40px;height:40px;border-radius:10px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 15px rgba(108,99,255,.4)}
        .logo-icon .material-icons-round{color:white;font-size:1.375rem}
        .logo-name{font-family:'Outfit',sans-serif;font-size:1.125rem;font-weight:800;color:var(--text-primary)}
        .logo-accent{color:var(--secondary)} .logo-tagline{font-size:.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px}
        .sidebar-nav{flex:1;padding:.75rem 0;overflow-y:auto}
        .nav-section-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);padding:.75rem 1.25rem .35rem}
        .nav-item{display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;margin:.125rem .5rem;border-radius:var(--radius-sm);color:var(--text-secondary);font-size:.875rem;font-weight:500;transition:var(--transition);position:relative}
        .nav-item:hover{background:rgba(255,255,255,.05);color:var(--text-primary)}
        .nav-item.active{background:rgba(108,99,255,.15);color:var(--primary-light);border:1px solid rgba(108,99,255,.2)}
        .nav-item.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--gradient-primary);border-radius:0 3px 3px 0}
        .nav-icon{font-size:1.25rem;flex-shrink:0} .nav-label{flex:1}
        .nav-badge{min-width:20px;height:20px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:white;padding:0 5px;margin-left:auto}
        .sidebar-footer{padding:1rem;border-top:1px solid var(--sidebar-border)}
        .eco-score{background:linear-gradient(135deg,rgba(67,217,173,.1),rgba(41,182,246,.05));border:1px solid rgba(67,217,173,.2);border-radius:var(--radius-md);padding:.875rem}
        .topbar{height:var(--topbar-height);background:rgba(17,24,39,.95);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;backdrop-filter:blur(12px);position:sticky;top:0;z-index:50}
        .breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.875rem;color:var(--text-secondary)}
        .breadcrumb .current{color:var(--text-primary);font-weight:600}
        .topbar-right{display:flex;align-items:center;gap:.75rem}
        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;transition:var(--transition);position:relative;overflow:hidden}
        .card:hover{background:var(--bg-card-hover);border-color:var(--border-active);transform:translateY(-2px);box-shadow:var(--shadow-glow)}
        .card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem}
        .card-title{font-size:.9375rem;font-weight:600;color:var(--text-primary)}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:var(--radius-sm);font-size:.875rem;font-weight:600;cursor:pointer;border:none;transition:var(--transition);font-family:'Inter',sans-serif;white-space:nowrap}
        .btn-primary{background:var(--gradient-primary);color:white;box-shadow:0 4px 15px rgba(108,99,255,.3)}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(108,99,255,.5);color:white}
        .btn-secondary{background:rgba(255,255,255,.05);color:var(--text-primary);border:1px solid var(--border)}
        .btn-secondary:hover{background:rgba(255,255,255,.1);border-color:var(--primary)}
        .btn-danger{background:rgba(255,101,132,.15);color:var(--accent-red);border:1px solid rgba(255,101,132,.3)}
        .btn-danger:hover{background:rgba(255,101,132,.25)}
        .btn-success{background:rgba(67,217,173,.15);color:var(--secondary);border:1px solid rgba(67,217,173,.3)}
        .btn-success:hover{background:rgba(67,217,173,.25)} .btn-sm{padding:.4rem .875rem;font-size:.8rem}
        .badge{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .6rem;border-radius:100px;font-size:.75rem;font-weight:600}
        .badge-primary{background:rgba(108,99,255,.15);color:var(--primary-light);border:1px solid rgba(108,99,255,.3)}
        .badge-success{background:rgba(67,217,173,.15);color:var(--secondary);border:1px solid rgba(67,217,173,.3)}
        .badge-danger{background:rgba(255,101,132,.15);color:var(--accent-red);border:1px solid rgba(255,101,132,.3)}
        .badge-warning{background:rgba(255,167,38,.15);color:var(--accent-orange);border:1px solid rgba(255,167,38,.3)}
        .badge-info{background:rgba(41,182,246,.15);color:var(--accent-blue);border:1px solid rgba(41,182,246,.3)}
        .form-group{margin-bottom:1.25rem} .form-label{display:block;font-size:.85rem;font-weight:600;color:var(--text-secondary);margin-bottom:.5rem}
        .form-control{width:100%;padding:.65rem 1rem;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-primary);font-size:.875rem;font-family:'Inter',sans-serif;transition:var(--transition)}
        .form-control:focus{outline:none;border-color:var(--primary);background:rgba(108,99,255,.05);box-shadow:0 0 0 3px rgba(108,99,255,.1)}
        .form-control::placeholder{color:var(--text-muted)} select.form-control option{background:var(--bg-card);color:var(--text-primary)}
        .form-error{font-size:.8rem;color:var(--accent-red);margin-top:.35rem} .is-invalid{border-color:var(--accent-red)!important}
        textarea.form-control{resize:vertical;min-height:100px}
        .table-container{overflow-x:auto;border-radius:var(--radius-md)}
        table{width:100%;border-collapse:collapse;font-size:.875rem}
        thead th{padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);background:rgba(255,255,255,.02);border-bottom:1px solid var(--border)}
        tbody td{padding:.875rem 1rem;border-bottom:1px solid rgba(255,255,255,.03);color:var(--text-secondary)}
        tbody tr:hover td{background:rgba(255,255,255,.02);color:var(--text-primary)}
        .alert{display:flex;align-items:flex-start;gap:.75rem;padding:.875rem 1rem;border-radius:var(--radius-md);border:1px solid;font-size:.875rem}
        .alert-success{background:rgba(67,217,173,.1);border-color:rgba(67,217,173,.3);color:var(--secondary)}
        .alert-danger{background:rgba(255,101,132,.1);border-color:rgba(255,101,132,.3);color:var(--accent-red)}
        .alert-warning{background:rgba(255,167,38,.1);border-color:rgba(255,167,38,.3);color:var(--accent-orange)}
        .alert-info{background:rgba(108,99,255,.1);border-color:rgba(108,99,255,.3);color:var(--primary-light)}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}
        @keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
        @keyframes glow-pulse{0%,100%{box-shadow:0 0 12px var(--primary-glow)}50%{box-shadow:0 0 24px var(--primary-glow),0 0 48px rgba(108,99,255,.1)}}
        .animate-fade-in-up{animation:fadeInUp .5s ease forwards} .animate-fade-in{animation:fadeIn .4s ease forwards}
        .ai-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .625rem;background:linear-gradient(135deg,rgba(108,99,255,.2),rgba(67,217,173,.1));border:1px solid rgba(108,99,255,.4);border-radius:100px;font-size:.7rem;font-weight:700;color:var(--primary-light);text-transform:uppercase;letter-spacing:.5px;animation:glow-pulse 2s ease infinite}
        .ai-badge::before{content:'✦';font-size:.6rem}
        .page-title{font-size:1.75rem;font-weight:800;background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:.25rem}
        .page-subtitle{color:var(--text-secondary);font-size:.9rem;margin-bottom:1.5rem}
        .section{margin-bottom:1.5rem} .grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
        .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem} .grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem}
        .progress-bar{height:6px;background:rgba(255,255,255,.08);border-radius:3px;overflow:hidden}
        .progress-fill{height:100%;border-radius:3px;background:var(--gradient-primary)}
        .material-icons-round{font-family:'Material Icons Round','Material Icons';font-weight:normal;font-style:normal;font-size:24px;display:inline-block;line-height:1;text-transform:none;letter-spacing:normal;word-wrap:normal;white-space:nowrap;direction:ltr;-webkit-font-smoothing:antialiased;user-select:none}
        @media(max-width:1200px){.grid-4{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.grid-4,.grid-3,.grid-2{grid-template-columns:1fr}.main-content{margin-left:0}.sidebar{display:none}}
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body>
<div class="app-shell">
    @include('partials.sidebar')
    <div class="main-content">
        @include('partials.topbar')
        <div class="page-content">
            @include('partials.flash-messages')
            @yield('content')
        </div>
    </div>
</div>
@include('partials.workshop-modal')
@yield('scripts')
@stack('scripts')
</body>
</html>
