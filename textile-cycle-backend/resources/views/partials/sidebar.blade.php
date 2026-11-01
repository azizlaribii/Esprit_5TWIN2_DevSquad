{{-- Partial: sidebar --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">
            <span class="material-icons-round">recycling</span>
        </div>
        <div>
            <div class="logo-name">TexTile<span class="logo-accent">Cycle</span></div>
            <div class="logo-tagline">{{ auth()->check() && auth()->user()->isAdmin() ? 'Panneau Admin' : 'Économie Circulaire' }}</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        @if(auth()->check() && auth()->user()->isAdmin())
            {{-- Vue EXCLUSIVE pour l'ADMINISTRATEUR : Uniquement la partie Administration --}}
            <div class="nav-section-label" style="color:var(--primary); font-weight:800">Administration</div>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">manage_accounts</span>
                <span class="nav-label">Gestion Utilisateurs</span>
                <span class="nav-badge" style="background:var(--primary)">Admin</span>
            </a>


            <a href="{{ route('admin.depots.index') }}" class="nav-item {{ request()->is('admin/depots*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">inventory_2</span>
                <span class="nav-label">Modération Dépôts</span>
            </a>
            <a href="{{ route('admin.valorisations.index') }}" class="nav-item {{ request()->is('admin/valorisations*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">autorenew</span>
                <span class="nav-label">Modération Valorisations</span>
            </a>



            <div class="nav-section-label">Mon Compte</div>
            <a href="/profile" class="nav-item {{ request()->is('profile*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">person</span>
                <span class="nav-label">Mon Profil</span>
            </a>
            <a href="/two-factor" class="nav-item {{ request()->is('two-factor*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">security</span>
                <span class="nav-label">Sécurité & 2FA</span>
            </a>
        @else
            {{-- Vue pour les UTILISATEURS NORMAUX (particuliers, ateliers, associations) : AUCUNE partie Admin --}}
            <div class="nav-section-label">Principal</div>
            <a href="/dashboard" class="nav-item {{ request()->is('dashboard*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">dashboard</span>
                <span class="nav-label">Tableau de bord</span>
            </a>
            <a href="/marketplace" class="nav-item {{ request()->is('marketplace') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">storefront</span>
                <span class="nav-label">Marketplace</span>
                <span class="nav-badge" style="background:#10B981">Nouveau</span>
            </a>
            <a href="/marketplace/mes-articles" class="nav-item {{ request()->is('marketplace/mes-articles*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">inventory_2</span>
                <span class="nav-label">Mes Articles</span>
            </a>
            <a href="/armoire" class="nav-item {{ request()->is('armoire*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">checkroom</span>
                <span class="nav-label">Mon Armoire</span>
                <span class="nav-badge" style="background:#EF4444">Dressing</span>
            </a>
            <a href="/statistiques" class="nav-item {{ request()->is('statistiques*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">bar_chart</span>
                <span class="nav-label">Statistiques</span>
            </a>
            <a href="/predictions" class="nav-item {{ request()->is('predictions*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">auto_awesome</span>
                <span class="nav-label">Prédictions IA</span>
            </a>

            <div class="nav-section-label">Gestion</div>
            <a href="/depots" class="nav-item {{ request()->is('depots*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">inventory_2</span>
                <span class="nav-label">Dépôts</span>
            </a>

                        <a href="{{ route('valorisations.index') }}" class="nav-item {{ request()->is('valorisations*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">autorenew</span>
                <span class="nav-label">Valorisations</span>
            </a>


            <a href="/reparations" class="nav-item {{ request()->is('reparations*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">build</span>
                <span class="nav-label">Réparations</span>
            </a>
            <a href="/dons" class="nav-item {{ request()->is('dons*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">volunteer_activism</span>
                <span class="nav-label">Dons</span>
            </a>
            <a href="/upcycling/projets" class="nav-item {{ request()->is('upcycling*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">recycling</span>
                <span class="nav-label">Upcycling</span>
                <span class="nav-badge" style="background:#5B52E0">IA</span>
            </a>

            <div class="nav-section-label">Partenaires</div>
            <a href="/ateliers" class="nav-item {{ request()->is('ateliers*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">precision_manufacturing</span>
                <span class="nav-label">Ateliers</span>
            </a>
            <a href="/associations" class="nav-item {{ request()->is('associations*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">groups</span>
                <span class="nav-label">Associations</span>
            </a>

            <div class="nav-section-label">Mon Compte</div>
            <a href="/profile" class="nav-item {{ request()->is('profile*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">person</span>
                <span class="nav-label">Mon Profil</span>
            </a>
            <a href="/two-factor" class="nav-item {{ request()->is('two-factor*') ? 'active' : '' }}">
                <span class="nav-icon material-icons-round">security</span>
                <span class="nav-label">Sécurité & 2FA</span>
            </a>
        @endif
    </nav>
    <div class="sidebar-footer">
        @if(auth()->check() && auth()->user()->isAdmin())
            <div style="background:#FEF3C7; border:1px solid #FCD34D; border-radius:var(--radius-md); padding:.875rem">
                <div style="display:flex; align-items:center; gap:.5rem">
                    <span class="material-icons-round" style="color:#92400E; font-size:1.1rem">admin_panel_settings</span>
                    <span style="font-size:.78rem; font-weight:700; color:#92400E">Espace Admin</span>
                </div>
                <div style="font-size:.72rem; color:#B45309; margin-top:.35rem">Privilèges d'administration système</div>
            </div>
        @else
            <div class="eco-score">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                    <span class="material-icons-round" style="color:var(--secondary);font-size:1rem">eco</span>
                    <span style="font-size:.75rem;color:var(--text-secondary);font-weight:500">Score Éco-Impact</span>
                </div>
                <div style="font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:var(--secondary);margin-bottom:.5rem">
                    78<span style="font-size:.875rem;color:var(--text-muted)">/100</span>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width:78%"></div></div>
                <div style="font-size:.7rem;color:var(--secondary);font-weight:600;margin-top:.35rem">+12% ce mois</div>
            </div>
        @endif
    </div>
</aside>
