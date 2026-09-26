import { Component, OnInit, AfterViewInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DashboardService } from '../../core/services/dashboard.service';
import { PredictionService } from '../../core/services/prediction.service';
import { forkJoin } from 'rxjs';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  template: `
    <div class="dashboard animate-fade-in-up">
      <!-- Header -->
      <div class="dashboard-header">
        <div>
          <h1 class="page-title">Tableau de Bord Intelligent</h1>
          <p class="page-subtitle">
            Analyse en temps réel • IA Active
            <span class="ai-badge" style="margin-left: 0.5rem;">Propulsé par l'IA</span>
          </p>
        </div>
        <div class="header-actions">
          <button class="btn btn-secondary" id="btn-refresh" (click)="loadData()">
            <span class="material-icons-round" [class.spin]="loading">refresh</span>
            Actualiser
          </button>
          <button class="btn btn-primary" [routerLink]="['/predictions']" id="btn-predictions">
            <span class="material-icons-round">auto_awesome</span>
            Voir Prédictions IA
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <ng-container *ngIf="loading">
        <div class="grid-4" style="margin-bottom: 1rem;">
          <div *ngFor="let i of [1,2,3,4]" class="kpi-card skeleton" style="height: 96px;"></div>
        </div>
        <div class="grid-4" style="margin-bottom: 1rem;">
          <div *ngFor="let i of [1,2]" class="card skeleton" style="height: 96px;"></div>
        </div>
      </ng-container>

      <!-- Content -->
      <ng-container *ngIf="!loading">

        <!-- ==================== KPI CARDS ROW 1 ==================== -->
        <div class="grid-4 section">
          <div
            *ngFor="let kpi of kpis.slice(0, 4); let i = index"
            class="kpi-card"
            [id]="'kpi-' + kpi.id"
            [style.animation-delay]="(i * 0.1) + 's'"
            style="animation: fadeInUp 0.5s ease forwards;"
          >
            <div class="kpi-icon-wrap" [style.background]="kpi.couleur + '20'" [style.border]="'1px solid ' + kpi.couleur + '40'">
              <span class="material-icons-round" [style.color]="kpi.couleur">{{ kpi.icone }}</span>
            </div>
            <div class="kpi-content">
              <div class="kpi-label">{{ kpi.label }}</div>
              <div>
                <span class="kpi-value">{{ kpi.valeur }}</span>
                <span class="kpi-unit">{{ kpi.unite }}</span>
              </div>
              <div class="kpi-variation" [class.positive]="kpi.variation > 0" [class.negative]="kpi.variation < 0" [class.neutral]="kpi.variation === 0">
                <span class="material-icons-round" style="font-size: 0.9rem;">
                  {{ kpi.variation > 0 ? 'trending_up' : (kpi.variation < 0 ? 'trending_down' : 'trending_flat') }}
                </span>
                {{ kpi.variation > 0 ? '+' : '' }}{{ kpi.variation }}% vs mois dernier
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== KPI CARDS ROW 2 ==================== -->
        <div class="grid-2 section">
          <div
            *ngFor="let kpi of kpis.slice(4, 6); let i = index"
            class="kpi-card"
            [id]="'kpi-extra-' + kpi.id"
          >
            <div class="kpi-icon-wrap" [style.background]="kpi.couleur + '20'" [style.border]="'1px solid ' + kpi.couleur + '40'">
              <span class="material-icons-round" [style.color]="kpi.couleur">{{ kpi.icone }}</span>
            </div>
            <div class="kpi-content">
              <div class="kpi-label">{{ kpi.label }}</div>
              <div>
                <span class="kpi-value">{{ kpi.valeur }}</span>
                <span class="kpi-unit">{{ kpi.unite }}</span>
              </div>
              <div class="kpi-variation positive">
                <span class="material-icons-round" style="font-size: 0.9rem;">trending_up</span>
                +{{ kpi.variation }}% vs mois dernier
              </div>
            </div>
            <!-- Progress bar for percentage KPIs -->
            <div class="kpi-progress" *ngIf="kpi.unite === '%' || kpi.unite === '/100'">
              <div class="progress-bar" style="width: 100px;">
                <div class="progress-fill" [style.width]="kpi.valeur + '%'" [style.background]="'linear-gradient(90deg, ' + kpi.couleur + ', ' + kpi.couleur + 'aa)'"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== MAIN GRID ==================== -->
        <div class="grid-main section">

          <!-- Evolution Chart -->
          <div class="card" id="card-evolution-chart">
            <div class="card-header">
              <div>
                <div class="card-title">Évolution de la Semaine</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Dépôts, réparations et dons quotidiens</div>
              </div>
              <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-ghost btn-icon" (click)="activeChart = 'bar'" [class.active-chart-btn]="activeChart === 'bar'">
                  <span class="material-icons-round">bar_chart</span>
                </button>
                <button class="btn btn-ghost btn-icon" (click)="activeChart = 'line'" [class.active-chart-btn]="activeChart === 'line'">
                  <span class="material-icons-round">show_chart</span>
                </button>
              </div>
            </div>

            <!-- Chart Legend -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
              <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--text-secondary);">
                <div style="width: 12px; height: 12px; border-radius: 3px; background: #6C63FF;"></div> Dépôts
              </div>
              <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--text-secondary);">
                <div style="width: 12px; height: 12px; border-radius: 3px; background: #FF6584;"></div> Réparations
              </div>
              <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--text-secondary);">
                <div style="width: 12px; height: 12px; border-radius: 3px; background: #43D9AD;"></div> Dons
              </div>
            </div>

            <!-- Bar Chart (CSS-based) -->
            <div class="chart-area">
              <div class="bar-chart" *ngIf="weekData.length > 0">
                <div *ngFor="let day of weekData" class="bar-group">
                  <div class="bar-trio">
                    <div class="bar bar-primary" [style.height.%]="(day.depots / maxWeekValue) * 100" [attr.data-tooltip]="day.depots + ' dépôts'"></div>
                    <div class="bar bar-danger" [style.height.%]="(day.reparations / maxWeekValue) * 100" [attr.data-tooltip]="day.reparations + ' réparations'"></div>
                    <div class="bar bar-success" [style.height.%]="(day.dons / maxWeekValue) * 100" [attr.data-tooltip]="day.dons + ' dons'"></div>
                  </div>
                  <div class="bar-label">{{ day.date }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Activity + Alerts -->
          <div style="display: flex; flex-direction: column; gap: 1rem;">
            <!-- Alerts -->
            <div class="card" id="card-alerts" *ngIf="alerts.length > 0">
              <div class="card-header">
                <div class="card-title">
                  <span class="material-icons-round" style="color: var(--accent-orange); font-size: 1.1rem;">notifications_active</span>
                  Alertes Système
                </div>
              </div>
              <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <div *ngFor="let alert of alerts" class="alert" [class]="'alert-' + (alert.type === 'warning' ? 'warning' : alert.type === 'danger' ? 'danger' : 'success')">
                  <span class="material-icons-round" style="font-size: 1.1rem; flex-shrink: 0;">
                    {{ alert.type === 'warning' ? 'warning' : alert.type === 'danger' ? 'error' : 'check_circle' }}
                  </span>
                  <div>
                    <div style="font-weight: 600; font-size: 0.8rem;">{{ alert.titre }}</div>
                    <div style="font-size: 0.8rem; opacity: 0.8; margin-top: 0.2rem;">{{ alert.message }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Activity -->
            <div class="card" id="card-activity" style="flex: 1;">
              <div class="card-header">
                <div class="card-title">
                  <span class="material-icons-round" style="color: var(--primary); font-size: 1.1rem;">history</span>
                  Activité Récente
                </div>
                <a routerLink="/statistiques" style="font-size: 0.8rem; color: var(--primary);">Tout voir →</a>
              </div>
              <div class="activity-list">
                <div *ngFor="let act of recentActivity.slice(0, 6)" class="activity-item">
                  <div class="activity-icon" [style.background]="act.couleur + '20'" [style.border]="'1px solid ' + act.couleur + '30'">
                    <span class="material-icons-round" [style.color]="act.couleur" style="font-size: 1rem;">{{ act.icone }}</span>
                  </div>
                  <div class="activity-content">
                    <div class="activity-message">{{ act.message }}</div>
                    <div class="activity-meta">{{ act.utilisateur }} • {{ act.date }}</div>
                  </div>
                  <div class="activity-badge">
                    <span class="badge" [class]="getBadgeClass(act.type)">{{ act.type }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== BOTTOM ROW ==================== -->
        <div class="grid-3 section">

          <!-- Impact Écologique -->
          <div class="card" id="card-eco-impact" style="background: linear-gradient(135deg, rgba(67, 217, 173, 0.08) 0%, rgba(41, 182, 246, 0.05) 100%); border-color: rgba(67, 217, 173, 0.2);">
            <div class="card-header">
              <div class="card-title" style="color: var(--secondary);">
                <span class="material-icons-round" style="font-size: 1.1rem;">eco</span>
                Impact Écologique
              </div>
              <span class="badge badge-success">Ce mois</span>
            </div>
            <div class="eco-stats">
              <div class="eco-stat">
                <span class="eco-num" style="color: var(--secondary);">{{ overview?.impact_ecologique?.kg_vetements_sauves | number:'1.0-0' }}</span>
                <span class="eco-desc">kg textiles sauvés</span>
              </div>
              <div class="eco-stat">
                <span class="eco-num" style="color: var(--accent-blue);">{{ overview?.impact_ecologique?.co2_evite_kg | number:'1.0-0' }}</span>
                <span class="eco-desc">kg CO₂ évités</span>
              </div>
              <div class="eco-stat">
                <span class="eco-num" style="color: var(--accent-orange);">{{ (overview?.impact_ecologique?.eau_economisee_litres / 1000) | number:'1.0-0' }}k</span>
                <span class="eco-desc">litres d'eau économisés</span>
              </div>
            </div>
          </div>

          <!-- IA Recommandations Preview -->
          <div class="card" id="card-ai-preview" style="background: linear-gradient(135deg, rgba(108, 99, 255, 0.08) 0%, rgba(255, 101, 132, 0.05) 100%); border-color: rgba(108, 99, 255, 0.2);">
            <div class="card-header">
              <div class="card-title" style="color: var(--primary-light);">
                <span class="material-icons-round" style="font-size: 1.1rem;">auto_awesome</span>
                IA - Recommandation Principale
              </div>
              <span class="ai-badge">IA</span>
            </div>
            <div *ngIf="topRecommandation" style="display: flex; flex-direction: column; gap: 0.75rem;">
              <div class="alert alert-warning">
                <span class="material-icons-round" style="font-size: 1.1rem;">priority_high</span>
                <div>
                  <div style="font-weight: 700; font-size: 0.85rem; margin-bottom: 0.25rem;">{{ topRecommandation.titre }}</div>
                  <div style="font-size: 0.8rem; opacity: 0.85;">{{ topRecommandation.message }}</div>
                </div>
              </div>
              <div style="font-size: 0.8rem; color: var(--text-secondary);">
                <strong>Action :</strong> {{ topRecommandation.action_principale }}
              </div>
              <a [routerLink]="['/predictions']" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
                <span class="material-icons-round" style="font-size: 1rem;">auto_awesome</span>
                Voir toutes les recommandations
              </a>
            </div>
          </div>

          <!-- Stats Rapides -->
          <div class="card" id="card-quick-stats">
            <div class="card-header">
              <div class="card-title">
                <span class="material-icons-round" style="color: var(--accent-orange); font-size: 1.1rem;">bolt</span>
                Stats Rapides
              </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
              <div *ngFor="let stat of quickStats">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                  <span style="font-size: 0.875rem; color: var(--text-secondary);">{{ stat.label }}</span>
                  <span style="font-size: 0.875rem; font-weight: 700; color: var(--text-primary);">{{ stat.value }}{{ stat.unit }}</span>
                </div>
                <div class="progress-bar">
                  <div class="progress-fill" [style.width]="stat.percent + '%'" [style.background]="'linear-gradient(90deg, ' + stat.color + ', ' + stat.color + '80)'"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </ng-container>
    </div>
  `,
  styles: [`
    .dashboard {
      max-width: 1600px;
      margin: 0 auto;
    }

    .dashboard-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .header-actions {
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }

    /* Chart */
    .chart-area {
      height: 200px;
      position: relative;
    }

    .bar-chart {
      display: flex;
      align-items: flex-end;
      gap: 0.75rem;
      height: 100%;
      padding-top: 1rem;
    }

    .bar-group {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.375rem;
      height: 100%;
    }

    .bar-trio {
      flex: 1;
      display: flex;
      align-items: flex-end;
      gap: 2px;
      width: 100%;
      padding: 0 2px;
    }

    .bar {
      flex: 1;
      border-radius: 4px 4px 0 0;
      min-height: 4px;
      transition: height 1s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      position: relative;
    }

    .bar:hover {
      filter: brightness(1.2);
    }

    .bar-primary { background: linear-gradient(180deg, #6C63FF, #4A42D6); }
    .bar-danger { background: linear-gradient(180deg, #FF6584, #D94466); }
    .bar-success { background: linear-gradient(180deg, #43D9AD, #2BB88E); }

    .bar-label {
      font-size: 0.75rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    /* Activity */
    .activity-list {
      display: flex;
      flex-direction: column;
      gap: 0.625rem;
    }

    .activity-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.5rem;
      border-radius: var(--radius-sm);
      transition: var(--transition);
    }

    .activity-item:hover {
      background: rgba(255, 255, 255, 0.03);
    }

    .activity-icon {
      width: 34px;
      height: 34px;
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .activity-content {
      flex: 1;
      min-width: 0;
    }

    .activity-message {
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--text-primary);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .activity-meta {
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-top: 0.1rem;
    }

    .activity-badge {
      flex-shrink: 0;
    }

    /* Eco stats */
    .eco-stats {
      display: flex;
      flex-direction: column;
      gap: 0.875rem;
    }

    .eco-stat {
      display: flex;
      align-items: baseline;
      gap: 0.5rem;
    }

    .eco-num {
      font-family: 'Outfit', sans-serif;
      font-size: 1.5rem;
      font-weight: 800;
    }

    .eco-desc {
      font-size: 0.8rem;
      color: var(--text-secondary);
    }

    /* Spin animation */
    .spin {
      animation: spin 1s linear infinite;
    }

    /* Active chart button */
    .active-chart-btn {
      background: rgba(108, 99, 255, 0.15) !important;
      color: var(--primary-light) !important;
    }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  `]
})
export class DashboardComponent implements OnInit {
  loading = true;
  overview: any = null;
  kpis: any[] = [];
  recentActivity: any[] = [];
  alerts: any[] = [];
  weekData: any[] = [];
  maxWeekValue = 1;
  topRecommandation: any = null;
  activeChart = 'bar';

  quickStats = [
    { label: 'Taux valorisation', value: 76.4, unit: '%', percent: 76.4, color: '#6C63FF' },
    { label: 'Satisfaction ateliers', value: 4.7, unit: '/5', percent: 94, color: '#43D9AD' },
    { label: 'Dons attribués', value: 84, unit: '%', percent: 84, color: '#FFA726' },
    { label: 'Objectif mensuel', value: 87, unit: '%', percent: 87, color: '#29B6F6' },
  ];

  constructor(
    private dashboardService: DashboardService,
    private predictionService: PredictionService
  ) {}

  ngOnInit() {
    this.loadData();
  }

  loadData() {
    this.loading = true;

    forkJoin({
      overview: this.dashboardService.getOverview(),
      kpis: this.dashboardService.getKpis(),
      activity: this.dashboardService.getRecentActivity(),
      alerts: this.dashboardService.getAlerts(),
      recommandations: this.predictionService.getRecommandations(),
    }).subscribe({
      next: (data) => {
        this.overview = data.overview;
        this.kpis = data.kpis;
        this.recentActivity = data.activity;
        this.alerts = data.alerts;
        this.weekData = data.overview?.evolution_semaine || [];
        this.maxWeekValue = Math.max(
          ...this.weekData.map((d: any) => Math.max(d.depots, d.reparations, d.dons)),
          1
        );
        if (data.recommandations?.recommandations?.length > 0) {
          this.topRecommandation = data.recommandations.recommandations[0];
        }
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    });
  }

  getBadgeClass(type: string): string {
    const map: Record<string, string> = {
      depot: 'badge-primary',
      reparation: 'badge-danger',
      don: 'badge-success',
      transformation: 'badge-warning',
    };
    return map[type] || 'badge-info';
  }
}
