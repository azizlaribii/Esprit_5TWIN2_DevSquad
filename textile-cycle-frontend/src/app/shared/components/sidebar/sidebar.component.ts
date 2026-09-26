import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule } from '@angular/common';

interface NavItem {
  label: string;
  icon: string;
  route: string;
  badge?: number;
  badgeColor?: string;
  isSection?: boolean;
  sectionLabel?: string;
}

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, CommonModule],
  template: `
    <aside class="sidebar" [class.collapsed]="collapsed">
      <!-- Logo -->
      <div class="sidebar-logo">
        <div class="logo-icon">
          <span class="material-icons-round">recycling</span>
        </div>
        <div class="logo-text" *ngIf="!collapsed">
          <span class="logo-name">TexTile<span class="logo-accent">Cycle</span></span>
          <span class="logo-tagline">Économie Circulaire</span>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <ng-container *ngFor="let item of navItems">
          <!-- Section Header -->
          <div *ngIf="item.isSection && !collapsed" class="nav-section-label">
            {{ item.sectionLabel }}
          </div>
          <div *ngIf="item.isSection && collapsed" class="nav-divider"></div>

          <!-- Nav Link -->
          <a
            *ngIf="!item.isSection"
            [routerLink]="item.route"
            routerLinkActive="active"
            class="nav-item"
            [attr.data-tooltip]="collapsed ? item.label : null"
          >
            <span class="nav-icon material-icons-round">{{ item.icon }}</span>
            <span class="nav-label" *ngIf="!collapsed">{{ item.label }}</span>
            <span
              *ngIf="item.badge && !collapsed"
              class="nav-badge"
              [style.background]="item.badgeColor || 'var(--primary)'"
            >{{ item.badge }}</span>
          </a>
        </ng-container>
      </nav>

      <!-- Footer -->
      <div class="sidebar-footer" *ngIf="!collapsed">
        <div class="eco-score">
          <div class="eco-score-header">
            <span class="material-icons-round eco-icon">eco</span>
            <span class="eco-label">Score Éco-Impact</span>
          </div>
          <div class="eco-value">78<span>/100</span></div>
          <div class="eco-bar">
            <div class="eco-fill" style="width: 78%"></div>
          </div>
          <div class="eco-sublabel">+12% ce mois</div>
        </div>
      </div>

      <!-- Collapse button -->
      <button class="collapse-btn" (click)="toggleCollapse.emit()" [attr.title]="collapsed ? 'Développer' : 'Réduire'">
        <span class="material-icons-round">{{ collapsed ? 'chevron_right' : 'chevron_left' }}</span>
      </button>
    </aside>
  `,
  styles: [`
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      height: 100vh;
      width: var(--sidebar-width);
      background: var(--sidebar-bg);
      border-right: 1px solid var(--sidebar-border);
      display: flex;
      flex-direction: column;
      transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 100;
      overflow: hidden;
    }

    .sidebar.collapsed {
      width: var(--sidebar-collapsed);
    }

    /* Logo */
    .sidebar-logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 1.25rem 1rem;
      border-bottom: 1px solid var(--sidebar-border);
      min-height: 72px;
    }

    .logo-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: var(--gradient-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
    }

    .logo-icon .material-icons-round {
      color: white;
      font-size: 1.375rem;
    }

    .logo-text {
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .logo-name {
      font-family: 'Outfit', sans-serif;
      font-size: 1.125rem;
      font-weight: 800;
      color: var(--text-primary);
      white-space: nowrap;
    }

    .logo-accent {
      color: var(--secondary);
    }

    .logo-tagline {
      font-size: 0.65rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      white-space: nowrap;
    }

    /* Navigation */
    .sidebar-nav {
      flex: 1;
      padding: 0.75rem 0;
      overflow-y: auto;
      overflow-x: hidden;
    }

    .nav-section-label {
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--text-muted);
      padding: 0.75rem 1.25rem 0.35rem;
      white-space: nowrap;
    }

    .nav-divider {
      height: 1px;
      background: var(--sidebar-border);
      margin: 0.5rem 0.75rem;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.65rem 1rem;
      margin: 0.125rem 0.5rem;
      border-radius: var(--radius-sm);
      color: var(--text-secondary);
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      transition: var(--transition);
      position: relative;
      white-space: nowrap;
      overflow: hidden;
    }

    .nav-item:hover {
      background: rgba(255, 255, 255, 0.05);
      color: var(--text-primary);
    }

    .nav-item.active {
      background: rgba(108, 99, 255, 0.15);
      color: var(--primary-light);
      border: 1px solid rgba(108, 99, 255, 0.2);
    }

    .nav-item.active::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 3px;
      background: var(--gradient-primary);
      border-radius: 0 3px 3px 0;
    }

    .nav-item.active .nav-icon {
      color: var(--primary-light);
    }

    .nav-icon {
      font-size: 1.25rem;
      flex-shrink: 0;
      transition: var(--transition);
    }

    .nav-label {
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .nav-badge {
      min-width: 20px;
      height: 20px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.65rem;
      font-weight: 700;
      color: white;
      padding: 0 5px;
    }

    /* Tooltip on collapsed */
    [data-tooltip]:hover::after {
      content: attr(data-tooltip);
      position: absolute;
      left: calc(100% + 12px);
      top: 50%;
      transform: translateY(-50%);
      background: var(--bg-card);
      border: 1px solid var(--border);
      color: var(--text-primary);
      font-size: 0.8rem;
      padding: 0.4rem 0.75rem;
      border-radius: var(--radius-sm);
      white-space: nowrap;
      z-index: 200;
      box-shadow: var(--shadow-md);
      pointer-events: none;
    }

    /* Eco Footer */
    .sidebar-footer {
      padding: 1rem;
      border-top: 1px solid var(--sidebar-border);
    }

    .eco-score {
      background: linear-gradient(135deg, rgba(67, 217, 173, 0.1), rgba(41, 182, 246, 0.05));
      border: 1px solid rgba(67, 217, 173, 0.2);
      border-radius: var(--radius-md);
      padding: 0.875rem;
    }

    .eco-score-header {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
    }

    .eco-icon {
      color: var(--secondary);
      font-size: 1rem;
    }

    .eco-label {
      font-size: 0.75rem;
      color: var(--text-secondary);
      font-weight: 500;
    }

    .eco-value {
      font-family: 'Outfit', sans-serif;
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--secondary);
      line-height: 1;
      margin-bottom: 0.5rem;
    }

    .eco-value span {
      font-size: 0.875rem;
      color: var(--text-muted);
    }

    .eco-bar {
      height: 4px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 2px;
      overflow: hidden;
      margin-bottom: 0.35rem;
    }

    .eco-fill {
      height: 100%;
      background: var(--gradient-green);
      border-radius: 2px;
      transition: width 1s ease;
    }

    .eco-sublabel {
      font-size: 0.7rem;
      color: var(--secondary);
      font-weight: 600;
    }

    /* Collapse Button */
    .collapse-btn {
      position: absolute;
      bottom: 80px;
      right: -14px;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--bg-card);
      border: 1px solid var(--border);
      color: var(--text-secondary);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      z-index: 10;
    }

    .collapse-btn:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }

    .collapse-btn .material-icons-round {
      font-size: 1rem;
    }
  `]
})
export class SidebarComponent {
  @Input() collapsed = false;
  @Output() toggleCollapse = new EventEmitter<void>();

  navItems: NavItem[] = [
    // Principal
    { isSection: true, sectionLabel: 'Principal', label: '', icon: '', route: '' },
    { label: 'Tableau de bord', icon: 'dashboard', route: '/dashboard' },
    { label: 'Statistiques', icon: 'bar_chart', route: '/statistiques' },
    { label: 'Prédictions IA', icon: 'auto_awesome', route: '/predictions', badgeColor: '#6C63FF' },

    // Gestion
    { isSection: true, sectionLabel: 'Gestion', label: '', icon: '', route: '' },
    { label: 'Dépôts', icon: 'inventory_2', route: '/depots', badge: 8, badgeColor: '#FFA726' },
    { label: 'Réparations', icon: 'build', route: '/reparations', badge: 3, badgeColor: '#FF6584' },
    { label: 'Dons', icon: 'volunteer_activism', route: '/dons' },
    { label: 'Transformations', icon: 'auto_fix_high', route: '/transformations' },

    // Partenaires
    { isSection: true, sectionLabel: 'Partenaires', label: '', icon: '', route: '' },
    { label: 'Ateliers', icon: 'storefront', route: '/ateliers' },
    { label: 'Associations', icon: 'groups', route: '/associations' },

    // Impact
    { isSection: true, sectionLabel: 'Impact', label: '', icon: '', route: '' },
    { label: 'Impact Écologique', icon: 'eco', route: '/impact' },
  ];
}
