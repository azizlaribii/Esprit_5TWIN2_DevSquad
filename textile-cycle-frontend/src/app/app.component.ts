import { Component } from '@angular/core';
import { RouterOutlet, Router } from '@angular/router';
import { SidebarComponent } from './shared/components/sidebar/sidebar.component';
import { TopbarComponent } from './shared/components/topbar/topbar.component';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, SidebarComponent, TopbarComponent, CommonModule],
  template: `
    <ng-container *ngIf="isAuthPage(); else fullAppShell">
      <div class="auth-only-wrapper">
        <router-outlet></router-outlet>
      </div>
    </ng-container>

    <ng-template #fullAppShell>
      <div class="app-shell">
        <app-sidebar [collapsed]="sidebarCollapsed" (toggleCollapse)="toggleSidebar()"></app-sidebar>
        <div class="main-content" [class.sidebar-collapsed]="sidebarCollapsed">
          <app-topbar (toggleSidebar)="toggleSidebar()"></app-topbar>
          <div class="page-content">
            <router-outlet></router-outlet>
          </div>
        </div>
      </div>
    </ng-template>
  `,
  styles: [`
    .auth-only-wrapper {
      min-height: 100vh;
      width: 100vw;
      overflow-x: hidden;
      background: var(--bg-main);
    }
    .app-shell {
      display: flex;
      min-height: 100vh;
      background: var(--bg-main);
    }
    .main-content {
      flex: 1;
      margin-left: 260px;
      transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    .main-content.sidebar-collapsed {
      margin-left: 72px;
    }
    .page-content {
      flex: 1;
      padding: 24px;
      overflow-y: auto;
    }
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
      }
    }
  `]
})
export class AppComponent {
  sidebarCollapsed = false;

  constructor(public router: Router) {}

  toggleSidebar() {
    this.sidebarCollapsed = !this.sidebarCollapsed;
  }

  isAuthPage(): boolean {
    return this.router.url.includes('/login');
  }
}
