@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div style="max-width:1200px; margin:0 auto; padding-bottom:3rem">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
        <div>
            <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.25rem">
                <span class="badge badge-primary">Administration</span>
                <span style="color:var(--text-muted); font-size:.85rem">•</span>
                <span style="color:var(--text-secondary); font-size:.85rem">{{ $users->total() }} utilisateur(s)</span>
            </div>
            <h1 class="page-title" style="margin-bottom:.25rem">Gestion des Utilisateurs</h1>
            <p class="page-subtitle" style="margin-bottom:0">Consultez, modifiez et administrez l'ensemble des comptes de la plateforme TexTileCycle.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <span class="material-icons-round">person_add</span>
            <span>Nouvel Utilisateur</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid-4" style="margin-bottom:1.5rem">
        <div class="card" style="padding:1.25rem">
            <div style="display:flex; justify-content:space-between; align-items:center">
                <div>
                    <div style="font-size:.8rem; color:var(--text-muted); font-weight:600; text-transform:uppercase">Total Comptes</div>
                    <div style="font-size:1.6rem; font-weight:800; color:var(--text-primary); margin-top:.25rem">{{ $stats['total'] }}</div>
                </div>
                <div style="width:42px; height:42px; border-radius:10px; background:#EEF2FF; color:var(--primary); display:flex; align-items:center; justify-content:center">
                    <span class="material-icons-round">group</span>
                </div>
            </div>
        </div>

        <div class="card" style="padding:1.25rem">
            <div style="display:flex; justify-content:space-between; align-items:center">
                <div>
                    <div style="font-size:.8rem; color:var(--text-muted); font-weight:600; text-transform:uppercase">Marketplace</div>
                    <div style="font-size:1.6rem; font-weight:800; color:#4F46E5; margin-top:.25rem">{{ $stats['vendeurs'] + $stats['acheteurs'] }}</div>
                    <div style="font-size:.72rem; color:var(--text-muted); margin-top:.15rem">{{ $stats['vendeurs'] }} vendeurs • {{ $stats['acheteurs'] }} acheteurs</div>
                </div>
                <div style="width:42px; height:42px; border-radius:10px; background:#EEF2FF; color:#4F46E5; display:flex; align-items:center; justify-content:center">
                    <span class="material-icons-round">storefront</span>
                </div>
            </div>
        </div>

        <div class="card" style="padding:1.25rem">
            <div style="display:flex; justify-content:space-between; align-items:center">
                <div>
                    <div style="font-size:.8rem; color:var(--text-muted); font-weight:600; text-transform:uppercase">Particuliers</div>
                    <div style="font-size:1.6rem; font-weight:800; color:#10B981; margin-top:.25rem">{{ $stats['particuliers'] }}</div>
                    <div style="font-size:.72rem; color:var(--text-muted); margin-top:.15rem">Comptes donateurs / standards</div>
                </div>
                <div style="width:42px; height:42px; border-radius:10px; background:#ECFDF5; color:#10B981; display:flex; align-items:center; justify-content:center">
                    <span class="material-icons-round">person</span>
                </div>
            </div>
        </div>

        <div class="card" style="padding:1.25rem">
            <div style="display:flex; justify-content:space-between; align-items:center">
                <div>
                    <div style="font-size:.8rem; color:var(--text-muted); font-weight:600; text-transform:uppercase">Administrateurs</div>
                    <div style="font-size:1.6rem; font-weight:800; color:#D97706; margin-top:.25rem">{{ $stats['admins'] }}</div>
                    <div style="font-size:.72rem; color:var(--text-muted); margin-top:.15rem">Gestionnaires système</div>
                </div>
                <div style="width:42px; height:42px; border-radius:10px; background:#FEF3C7; color:#D97706; display:flex; align-items:center; justify-content:center">
                    <span class="material-icons-round">admin_panel_settings</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card" style="margin-bottom:1.5rem; padding:1.25rem">
        <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center">
            
            <div style="flex:1; min-width:240px">
                <div style="position:relative">
                    <span class="material-icons-round" style="position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:1.2rem">search</span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Rechercher par nom ou adresse e-mail..."
                           class="form-control" style="padding-left:2.5rem">
                </div>
            </div>

            <div style="width:230px">
                <select name="role" class="form-control" onchange="this.form.submit()">
                    <option value="">Tous les rôles ({{ $stats['total'] }})</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>👑 Administrateurs ({{ $stats['admins'] }})</option>
                    <option value="vendeur" {{ request('role') === 'vendeur' ? 'selected' : '' }}>🏷️ Vendeurs Marketplace ({{ $stats['vendeurs'] }})</option>
                    <option value="acheteur" {{ request('role') === 'acheteur' ? 'selected' : '' }}>🛍️ Acheteurs Marketplace ({{ $stats['acheteurs'] }})</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>👤 Particuliers ({{ $stats['particuliers'] }})</option>
                    <option value="atelier" {{ request('role') === 'atelier' ? 'selected' : '' }}>✂️ Ateliers ({{ $stats['ateliers'] }})</option>
                    <option value="association" {{ request('role') === 'association' ? 'selected' : '' }}>🤝 Associations ({{ $stats['associations'] }})</option>
                </select>
            </div>

            <div style="width:180px">
                <select name="two_factor" class="form-control" onchange="this.form.submit()">
                    <option value="">Statut 2FA</option>
                    <option value="enabled" {{ request('two_factor') === 'enabled' ? 'selected' : '' }}>🛡️ 2FA Activé</option>
                    <option value="disabled" {{ request('two_factor') === 'disabled' ? 'selected' : '' }}>⚪ 2FA Non configuré</option>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">
                <span class="material-icons-round">filter_list</span>
                <span>Filtrer</span>
            </button>

            @if(request()->hasAny(['search', 'role', 'two_factor']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" style="color:var(--accent-red)">
                    <span class="material-icons-round">clear</span>
                    <span>Effacer</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="card" style="padding:0; overflow:hidden">
        <div class="table-container" style="border:none">
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Sécurité (2FA)</th>
                        <th>Inscription</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:.85rem">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                         style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:1px solid var(--border)">
                                    <div>
                                        <div style="font-weight:700; color:var(--text-primary)">
                                            <a href="{{ route('admin.users.show', $user) }}" style="color:inherit">
                                                {{ $user->name }}
                                            </a>
                                            @if($user->id === auth()->id())
                                                <span class="badge badge-info" style="font-size:.65rem; margin-left:.35rem">Vous</span>
                                            @endif
                                        </div>
                                        <div style="font-size:.8rem; color:var(--text-muted)">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge" style="background:#FEF3C7; color:#92400E; border:1px solid #FCD34D">👑 Administrateur</span>
                                @elseif($user->role === 'vendeur')
                                    <span class="badge" style="background:#F5F3FF; color:#6D28D9; border:1px solid #DDD6FE">🏷️ Vendeur</span>
                                @elseif($user->role === 'acheteur')
                                    <span class="badge" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE">🛍️ Acheteur</span>
                                @elseif($user->role === 'atelier')
                                    <span class="badge" style="background:#E0F2FE; color:#0369A1; border:1px solid #BAE6FD">✂️ Atelier</span>
                                @elseif($user->role === 'association')
                                    <span class="badge" style="background:#FCE7F3; color:#BE185D; border:1px solid #FBCFE8">🤝 Association</span>
                                @else
                                    <span class="badge badge-success">👤 Particulier</span>
                                @endif
                            </td>
                            <td>
                                @if($user->two_factor_enabled)
                                    <span class="badge badge-success" title="2FA activé et vérifié">
                                        <span class="material-icons-round" style="font-size:1rem">verified_user</span>
                                        <span>2FA Actif</span>
                                    </span>
                                @else
                                    <span class="badge" style="background:#F1F5F9; color:var(--text-muted); border:1px solid #E2E8F0">
                                        <span class="material-icons-round" style="font-size:1rem">shield</span>
                                        <span>Inactif</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size:.825rem; color:var(--text-secondary)">
                                    {{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}
                                </span>
                            </td>
                            <td style="text-align:right">
                                <div style="display:inline-flex; align-items:center; gap:.35rem">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary btn-sm" title="Consulter le profil">
                                        <span class="material-icons-round" style="font-size:1.1rem">visibility</span>
                                    </a>

                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm" title="Modifier">
                                        <span class="material-icons-round" style="font-size:1.1rem">edit</span>
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline"
                                              onsubmit="return confirm('Confirmez-vous la suppression du compte « {{ $user->name }} » ? Cette action est irréversible.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                                <span class="material-icons-round" style="font-size:1.1rem">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:3rem 1rem; color:var(--text-muted)">
                                <span class="material-icons-round" style="font-size:2.5rem; display:block; margin-bottom:.5rem">person_off</span>
                                Aucun utilisateur trouvé pour cette recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div style="padding:1.25rem; border-top:1px solid var(--border)">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

