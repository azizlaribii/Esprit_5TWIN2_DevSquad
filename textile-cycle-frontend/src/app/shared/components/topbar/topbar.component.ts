import { Component, Output, EventEmitter } from '@angular/core';
import { RouterLink, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-topbar',
  standalone: true,
  imports: [RouterLink, CommonModule],
  template: `
    <header class="topbar">
      <div class="topbar-left">
        <button class="btn-icon btn-ghost" id="sidebar-toggle" (click)="toggleSidebar.emit()" aria-label="Toggle sidebar">
          <span class="material-icons-round">menu</span>
        </button>

        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
          <span class="bread-home">TexTileCycle</span>
          <span class="bread-sep material-icons-round">chevron_right</span>
          <span class="bread-current">Tableau de bord</span>
        </nav>
      </div>

      <div class="topbar-right">
        <!-- Search -->
        <div class="topbar-search">
          <span class="material-icons-round search-icon">search</span>
          <input type="text" placeholder="Rechercher..." id="global-search" aria-label="Recherche globale" />
        </div>

        <!-- Notifications & User -->
        <div class="topbar-actions">
          <button class="btn-icon btn-ghost notif-btn" id="notifications-btn" aria-label="Notifications" data-tooltip="Notifications">
            <span class="material-icons-round">notifications</span>
            <span class="notif-dot"></span>
          </button>

          <!-- AI Status -->
          <div class="ai-status" data-tooltip="Service IA actif">
            <span class="ai-dot"></span>
            <span class="ai-label">IA Active</span>
          </div>

          <!-- Logged In Profile OR Login Button -->
          <ng-container *ngIf="authService.currentUser() as user; else loginBtn">
            <div class="user-profile-menu">
              <div class="user-btn">
                <div class="user-avatar" [style.background]="getRoleColor(user.role)">
                  <span>{{ user.name.charAt(0).toUpperCase() }}</span>
                </div>
                <div class="user-info">
                  <span class="user-name">{{ user.name }}</span>
                  <span class="user-role">{{ getRoleLabel(user.role) }}</span>
                </div>
              </div>
              <button class="btn-logout" (click)="logout()" title="Déconnexion">
                <span class="material-icons-round">logout</span>
              </button>
            </div>
          </ng-container>

          <ng-template #loginBtn>
            <a routerLink="/login" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.875rem;">
              <span class="material-icons-round" style="font-size: 1rem;">login</span>
              Se connecter
            </a>
          </ng-template>

        </div>
      </div>
    </header>
  `,
  styles: [`
    .topbar {
      height: var(--topbar-height);
      background: var(--topbar-bg);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1.5rem;
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(12px);
      gap: 1rem;
    }

    .topbar-left {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      font-size: 0.875rem;
    }

    .bread-home {
      color: var(--text-muted);
      font-weight: 500;
    }

    .bread-sep {
      color: var(--text-muted);
      font-size: 1rem;
    }

    .bread-current {
      color: var(--text-primary);
      font-weight: 600;
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    /* Search */
    .topbar-search {
      position: relative;
      display: flex;
      align-items: center;
    }

    .search-icon {
      position: absolute;
      left: 0.75rem;
      color: var(--text-muted);
      font-size: 1.1rem;
      pointer-events: none;
    }

    .topbar-search input {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 0.5rem 1rem 0.5rem 2.5rem;
      color: var(--text-primary);
      font-size: 0.875rem;
      font-family: 'Inter', sans-serif;
      width: 200px;
      transition: var(--transition);
      outline: none;
    }

    .topbar-search input:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--primary);
      width: 250px;
      box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.15);
    }

    .topbar-search input::placeholder {
      color: var(--text-muted);
    }

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    /* Notifications */
    .notif-btn {
      position: relative;
    }

    .notif-dot {
      position: absolute;
      top: 6px;
      right: 6px;
      width: 8px;
      height: 8px;
      background: var(--accent-red);
      border-radius: 50%;
      border: 2px solid var(--topbar-bg);
      animation: pulse 2s infinite;
    }

    /* AI Status */
    .ai-status {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.35rem 0.75rem;
      background: rgba(67, 217, 173, 0.1);
      border: 1px solid rgba(67, 217, 173, 0.25);
      border-radius: 100px;
      cursor: default;
    }

    .ai-dot {
      width: 7px;
      height: 7px;
      background: var(--secondary);
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    .ai-label {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--secondary);
    }

    /* User */
    .user-profile-menu {
      display: flex;
      align-items: center;
      gap: 0.375rem;
    }

    .user-btn {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 0.35rem 0.75rem;
      color: var(--text-primary);
    }

    .user-avatar {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.875rem;
      font-weight: 700;
      color: white;
    }

    .user-info {
      display: flex;
      flex-direction: column;
      line-height: 1.2;
    }

    .user-name {
      font-size: 0.8125rem;
      font-weight: 600;
    }

    .user-role {
      font-size: 0.7rem;
      color: var(--text-muted);
    }

    .btn-logout {
      background: rgba(255, 101, 132, 0.1);
      border: 1px solid rgba(255, 101, 132, 0.3);
      color: #FF6584;
      border-radius: var(--radius-sm);
      width: 34px;
      height: 34px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-logout:hover {
      background: rgba(255, 101, 132, 0.25);
    }

    @media (max-width: 768px) {
      .topbar-search, .user-info, .ai-status, .bread-home, .bread-sep {
        display: none;
      }
    }
  `]
})
export class TopbarComponent {
  @Output() toggleSidebar = new EventEmitter<void>();

  constructor(
    public authService: AuthService,
    private router: Router
  ) {}

  logout() {
    this.authService.logout();
    this.router.navigate(['/login']);
  }

  getRoleLabel(role: string): string {
    const map: Record<string, string> = {
      user: 'Particulier',
      atelier: 'Atelier de Réparation',
      association: 'Association',
      admin: 'Administrateur',
    };
    return map[role] || 'Utilisateur';
  }

  getRoleColor(role: string): string {
    const map: Record<string, string> = {
      user: 'linear-gradient(135deg, #6C63FF, #4A42D6)',
      atelier: 'linear-gradient(135deg, #FF6584, #D94466)',
      association: 'linear-gradient(135deg, #43D9AD, #2BB88E)',
      admin: 'linear-gradient(135deg, #FFA726, #FB8C00)',
    };
    return map[role] || 'linear-gradient(135deg, #6C63FF, #4A42D6)';
  }
}
