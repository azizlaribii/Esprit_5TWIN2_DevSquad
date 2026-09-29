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
        <div style="display:flex;align-items:center;gap:.5rem;padding:.4rem .75rem;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:var(--radius-sm)">
            <span class="material-icons-round" style="font-size:1.1rem;color:var(--secondary)">account_circle</span>
            <span style="font-size:.85rem;color:var(--text-secondary)">Utilisateur</span>
        </div>
    </div>
</header>
