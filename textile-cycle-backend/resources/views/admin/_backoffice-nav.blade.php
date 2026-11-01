<div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;padding:.5rem .75rem;background:rgba(108,99,255,.06);border:1px solid var(--border-active);border-radius:var(--radius-md)">
    <span class="ai-badge" style="animation:none">Administration</span>

    <a href="{{ route('admin.depots.index') }}"
       class="btn btn-sm {{ request()->routeIs('admin.depots.*') ? 'btn-primary' : 'btn-secondary' }}">
        <span class="material-icons-round" style="font-size:1.1rem">inventory_2</span> Dépôts
    </a>

    <a href="{{ route('admin.valorisations.index') }}"
       class="btn btn-sm {{ request()->routeIs('admin.valorisations.*') ? 'btn-primary' : 'btn-secondary' }}">
        <span class="material-icons-round" style="font-size:1.1rem">recycling</span> Valorisations
    </a>

    <a href="{{ route('admin.users.index') }}"
       class="btn btn-sm {{ request()->routeIs('admin.users.*') ? 'btn-primary' : 'btn-secondary' }}">
        <span class="material-icons-round" style="font-size:1.1rem">group</span> Utilisateurs
    </a>
</div>
