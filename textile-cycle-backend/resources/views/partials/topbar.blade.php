{{-- Partial: topbar --}}
<header class="topbar">
    <div class="topbar-left">
        <div class="breadcrumb">
            <span class="material-icons-round" style="font-size:1.1rem">home</span>
            <span>/</span>
            <span class="current">@yield('breadcrumb', 'Accueil')</span>
        </div>
    </div>
    <div class="topbar-right">
        <span class="ai-badge">IA Active</span>

        @auth
            <div style="display:flex;align-items:center;gap:.6rem">
                <div style="display:flex;align-items:center;gap:.5rem;padding:.4rem .85rem;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:var(--radius-sm)">
                    <span class="material-icons-round" style="font-size:1.15rem;color:var(--secondary)">account_circle</span>
                    <div>
                        <div style="font-size:.85rem;font-weight:600;color:var(--text-primary);line-height:1.2">
                            {{ Auth::user()->name }}
                        </div>
                        <div style="font-size:.7rem;color:var(--text-muted);text-transform:capitalize">
                            {{ Auth::user()->role ?? 'Donateur' }}
                        </div>
                    </div>
                </div>

                {{-- Bouton Déconnexion --}}
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm"
                            style="display:inline-flex;align-items:center;gap:.35rem;padding:.45rem .75rem;font-size:.8rem;color:var(--accent-red);border-color:rgba(255,101,132,.3);background:rgba(255,101,132,.08);transition:var(--transition)"
                            onmouseover="this.style.background='rgba(255,101,132,.2)'"
                            onmouseout="this.style.background='rgba(255,101,132,.08)'"
                            title="Se déconnecter">
                        <span class="material-icons-round" style="font-size:1rem">logout</span>
                        <span>Déconnexion</span>
                    </button>
                </form>
            </div>
        @else
            <div style="display:flex;align-items:center;gap:.5rem">
                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm" style="display:inline-flex;align-items:center;gap:.3rem">
                    <span class="material-icons-round" style="font-size:.95rem">login</span>
                    Connexion
                </a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm" style="display:inline-flex;align-items:center;gap:.3rem">
                    <span class="material-icons-round" style="font-size:.95rem">person_add</span>
                    Inscription
                </a>
            </div>
        @endauth
    </div>
</header>
