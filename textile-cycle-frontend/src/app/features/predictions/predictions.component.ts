import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { PredictionService } from '../../core/services/prediction.service';
import { forkJoin } from 'rxjs';

// =============================================
// Sub-component: Prediction Chart View
// =============================================
@Component({
  selector: 'app-prediction-chart-view',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div *ngIf="data" style="position: relative;">
      <div style="display: flex; gap: 2rem; margin-bottom: 1rem; flex-wrap: wrap;">
        <div>
          <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">
            {{ data.moyenne_historique }}
          </div>
          <div style="font-size: 0.75rem; color: var(--text-muted);">Moy. historique/jour</div>
        </div>
        <div>
          <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800;" [style.color]="color">
            {{ data.tendance === 'hausse' ? '↑' : data.tendance === 'baisse' ? '↓' : '→' }} {{ data.tendance }}
          </div>
          <div style="font-size: 0.75rem; color: var(--text-muted);">Tendance prédite</div>
        </div>
        <div>
          <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--secondary);">
            {{ (data.confiance * 100) | number:'1.0-0' }}%
          </div>
          <div style="font-size: 0.75rem; color: var(--text-muted);">Niveau de confiance</div>
        </div>
        <div>
          <div style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary);">{{ data.modele }}</div>
          <div style="font-size: 0.75rem; color: var(--text-muted);">Modèle IA utilisé</div>
        </div>
      </div>

      <!-- Prediction visualization -->
      <div class="pred-chart">
        <ng-container *ngFor="let p of data.predictions?.slice(0, 30)">
          <div class="pred-col">
            <div class="pred-interval"
                 [style.height.%]="getIntervalHeight(p)"
                 [style.bottom.%]="getIntervalBottom(p)"
                 [style.background]="color + '20'">
            </div>
            <div class="pred-bar"
                 [style.height.%]="getBarHeight(p)"
                 [style.background]="color"
                 [title]="p.date + ': ' + p.valeur_predite">
            </div>
          </div>
        </ng-container>
      </div>
      <div style="display: flex; justify-content: space-between; font-size: 0.7rem; color: var(--text-muted); margin-top: 0.5rem;">
        <span>Aujourd'hui</span>
        <span>+15 jours</span>
        <span>+30 jours</span>
      </div>
    </div>
    <div *ngIf="!data" style="display: flex; align-items: center; justify-content: center; height: 120px; color: var(--text-muted);">
      <div class="spinner"></div>
    </div>
  `,
  styles: [`
    .pred-chart {
      display: flex;
      align-items: flex-end;
      gap: 2px;
      height: 140px;
      background: rgba(255,255,255,0.02);
      border-radius: var(--radius-sm);
      padding: 8px 4px 4px;
      position: relative;
    }
    .pred-col {
      flex: 1;
      position: relative;
      height: 100%;
      display: flex;
      align-items: flex-end;
    }
    .pred-bar {
      width: 100%;
      border-radius: 2px 2px 0 0;
      min-height: 2px;
      position: absolute;
      bottom: 0;
      transition: height 0.8s ease;
      opacity: 0.85;
    }
    .pred-bar:hover { opacity: 1; }
    .pred-interval {
      width: 100%;
      position: absolute;
      border-radius: 2px;
      min-height: 2px;
    }
  `]
})
export class PredictionChartViewComponent {
  @Input() data: any;
  @Input() label = '';
  @Input() color = '#6C63FF';

  get maxVal(): number {
    if (!this.data?.predictions?.length) return 1;
    return Math.max(...this.data.predictions.map((p: any) => p.intervalle_haut), 1);
  }

  getBarHeight(p: any): number {
    return (p.valeur_predite / this.maxVal) * 100;
  }

  getIntervalHeight(p: any): number {
    return ((p.intervalle_haut - p.intervalle_bas) / this.maxVal) * 100;
  }

  getIntervalBottom(p: any): number {
    return (p.intervalle_bas / this.maxVal) * 100;
  }
}

// =============================================
// Main Component: Predictions Page
// =============================================
@Component({
  selector: 'app-predictions',
  standalone: true,
  imports: [CommonModule, PredictionChartViewComponent],
  template: `
    <div class="predictions animate-fade-in-up">

      <!-- Header -->
      <div class="section-header" style="margin-bottom: 1.5rem;">
        <div>
          <h1 class="page-title">Prédictions & Intelligence Artificielle</h1>
          <p class="page-subtitle">
            Analyse prédictive basée sur vos données historiques
            <span class="ai-badge" style="margin-left: 0.5rem;">Propulsé par Prophet & Scikit-learn</span>
          </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
          <button class="btn btn-secondary" id="btn-refresh-pred" (click)="loadData()">
            <span class="material-icons-round">refresh</span> Actualiser
          </button>
          <button class="btn btn-primary" id="btn-export-report" (click)="exportReport()">
            <span class="material-icons-round">download</span> Exporter Rapport
          </button>
        </div>
      </div>

      <!-- AI Health Score -->
      <div class="card ai-health-card section" id="ai-health-card" *ngIf="recommandations">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
          <div style="display: flex; align-items: center; gap: 1.5rem;">
            <div class="health-circle" [style.background]="getHealthGradient()">
              <div class="health-inner">
                <span class="health-value">{{ recommandations.score_sante_plateforme }}</span>
                <span class="health-label">/100</span>
              </div>
            </div>
            <div>
              <div style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary);">Score de Santé Plateforme</div>
              <div style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem;">
                Évaluation globale • {{ recommandations.nb_actions_urgentes }} actions urgentes identifiées par l'IA
              </div>
              <div class="ai-badge" style="margin-top: 0.5rem; display: inline-flex;">Analyse IA en temps réel</div>
            </div>
          </div>
          <div class="health-indicators">
            <div class="hi-item">
              <span class="hi-dot" style="background: #43D9AD;"></span>
              <span class="hi-label">Dépôts</span>
              <span class="hi-val hi-good">↑ Bonne tendance</span>
            </div>
            <div class="hi-item">
              <span class="hi-dot" style="background: #FFA726;"></span>
              <span class="hi-label">Capacité</span>
              <span class="hi-val hi-warn">⚠ À surveiller</span>
            </div>
            <div class="hi-item">
              <span class="hi-dot" style="background: #43D9AD;"></span>
              <span class="hi-label">Dons</span>
              <span class="hi-val hi-good">↑ Forte hausse</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tendances -->
      <div class="section" *ngIf="tendances">
        <div class="section-header">
          <div class="section-title">
            <span class="material-icons-round">trending_up</span>
            Tendances Analysées par l'IA
          </div>
        </div>
        <div class="grid-3">
          <div *ngFor="let t of tendances.tendances" class="card tendance-card" [id]="'tendance-' + (t.metrique?.toLowerCase() || 'item')">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
              <div style="font-weight: 700; color: var(--text-primary);">{{ t.metrique }}</div>
              <div class="tendance-badge" [class.hausse]="t.direction === 'hausse'" [class.baisse]="t.direction === 'baisse'" [class.stable]="t.direction === 'stable'">
                <span class="material-icons-round" style="font-size: 1rem;">
                  {{ t.direction === 'hausse' ? 'trending_up' : (t.direction === 'baisse' ? 'trending_down' : 'trending_flat') }}
                </span>
                {{ t.direction }}
              </div>
            </div>
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
              <div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); font-family: 'Outfit', sans-serif;">
                  {{ t.valeur_actuelle }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">ce mois</div>
              </div>
              <div style="display: flex; align-items: center; font-size: 1rem; font-weight: 700;"
                   [style.color]="t.direction === 'hausse' ? 'var(--secondary)' : t.direction === 'baisse' ? 'var(--accent-red)' : 'var(--text-muted)'">
                {{ t.pourcentage > 0 ? '+' : '' }}{{ t.pourcentage }}%
              </div>
            </div>
            <!-- Mini sparkline -->
            <div class="sparkline" *ngIf="t.valeurs?.length">
              <div *ngFor="let v of t.valeurs" class="spark-bar"
                   [style.height.%]="(v / (t.max || 1)) * 100"
                   [style.background]="t.couleur">
              </div>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
              Moy. {{ t.moyenne }} | Min {{ t.min }} | Max {{ t.max }}
            </div>
          </div>
        </div>

        <!-- AI Insights -->
        <div *ngIf="tendances.insights?.length" class="card" style="margin-top: 1rem; background: linear-gradient(135deg, rgba(108,99,255,0.05), rgba(67,217,173,0.03)); border-color: rgba(108,99,255,0.2);">
          <div style="font-weight: 700; color: var(--primary-light); margin-bottom: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
            <span class="material-icons-round" style="font-size: 1.1rem;">lightbulb</span>
            Insights Générés par l'IA
          </div>
          <div style="display: flex; flex-direction: column; gap: 0.625rem;">
            <div *ngFor="let insight of tendances.insights"
                 style="font-size: 0.875rem; color: var(--text-secondary); padding: 0.625rem; background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); border-left: 3px solid var(--primary);">
              {{ insight }}
            </div>
          </div>
        </div>
      </div>

      <!-- Prédictions 30 jours -->
      <div class="section">
        <div class="section-header">
          <div class="section-title">
            <span class="material-icons-round">query_stats</span>
            Prédictions sur 30 Jours
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-ghost" id="btn-pred-depots"
                    [style.background]="activePred === 'depots' ? 'rgba(108,99,255,0.15)' : ''"
                    [style.color]="activePred === 'depots' ? 'var(--primary-light)' : ''"
                    (click)="activePred = 'depots'">Dépôts</button>
            <button class="btn btn-ghost" id="btn-pred-reparations"
                    [style.background]="activePred === 'reparations' ? 'rgba(255,101,132,0.15)' : ''"
                    [style.color]="activePred === 'reparations' ? 'var(--accent-red)' : ''"
                    (click)="activePred = 'reparations'">Réparations</button>
            <button class="btn btn-ghost" id="btn-pred-dons"
                    [style.background]="activePred === 'dons' ? 'rgba(67,217,173,0.15)' : ''"
                    [style.color]="activePred === 'dons' ? 'var(--secondary)' : ''"
                    (click)="activePred = 'dons'">Dons</button>
          </div>
        </div>

        <div class="card" id="card-predictions-chart">
          <ng-container *ngIf="activePred === 'depots'">
            <app-prediction-chart-view [data]="predictionsDepots" label="Dépôts" color="#6C63FF"></app-prediction-chart-view>
          </ng-container>
          <ng-container *ngIf="activePred === 'reparations'">
            <app-prediction-chart-view [data]="predictionsReparations" label="Réparations" color="#FF6584"></app-prediction-chart-view>
          </ng-container>
          <ng-container *ngIf="activePred === 'dons'">
            <app-prediction-chart-view [data]="predictionsDons" label="Dons" color="#43D9AD"></app-prediction-chart-view>
          </ng-container>
        </div>
      </div>

      <!-- Grid: Categories + Recommandations -->
      <div class="grid-main section">

        <!-- Categories Populaires -->
        <div class="card" id="card-categories">
          <div class="card-header">
            <div class="card-title">
              <span class="material-icons-round" style="color: var(--accent-orange); font-size: 1.1rem;">category</span>
              Catégories Populaires & Prédictions
            </div>
            <span class="ai-badge">IA</span>
          </div>

          <div *ngIf="categories?.categories?.length" style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div *ngFor="let cat of categories.categories; let i = index" class="cat-item">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <span style="font-size: 0.75rem; color: var(--text-muted); width: 16px; text-align: right;">{{ i + 1 }}</span>
                  <span style="font-size: 0.875rem; font-weight: 500;">{{ cat.categorie }}</span>
                  <span class="badge"
                        [class.badge-success]="cat.tendance === 'hausse'"
                        [class.badge-warning]="cat.tendance === 'stable'"
                        [class.badge-danger]="cat.tendance === 'baisse'"
                        style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">
                    {{ cat.tendance === 'hausse' ? '↑' : cat.tendance === 'baisse' ? '↓' : '→' }}
                  </span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-secondary);">
                  {{ cat.count }} <span style="color: var(--text-muted);">→</span>
                  <span style="color: var(--secondary);">{{ cat.prediction_mois_prochain }}</span>
                </div>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" [style.width]="cat.part + '%'"
                     [style.background]="getCategoryColor(i)"></div>
              </div>
              <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.2rem;">{{ cat.part }}% des dépôts</div>
            </div>
          </div>
        </div>

        <!-- Recommandations -->
        <div class="card" id="card-recommandations">
          <div class="card-header">
            <div class="card-title">
              <span class="material-icons-round" style="color: var(--primary); font-size: 1.1rem;">recommend</span>
              Recommandations IA
            </div>
          </div>

          <div *ngIf="recommandations?.recommandations?.length" style="display: flex; flex-direction: column; gap: 0.875rem;">
            <div *ngFor="let rec of recommandations.recommandations"
                 class="rec-card" [style.border-left-color]="rec.couleur">
              <div class="rec-header">
                <div class="rec-title">{{ rec.titre }}</div>
                <span class="badge" [class]="getPrioClass(rec.priorite)">{{ rec.priorite }}</span>
              </div>
              <div class="rec-message">{{ rec.message }}</div>
              <div class="rec-action">
                <span class="material-icons-round" style="font-size: 0.875rem; color: var(--secondary);">arrow_forward</span>
                {{ rec.action_principale }}
              </div>
              <div class="rec-impact">💡 {{ rec.impact_potentiel }}</div>
              <div *ngIf="rec.actions_secondaires?.length" class="rec-secondary">
                <div *ngFor="let action of rec.actions_secondaires"
                     style="display: flex; align-items: center; gap: 0.35rem;">
                  <span class="material-icons-round" style="font-size: 0.75rem; color: var(--text-muted);">fiber_manual_record</span>
                  {{ action }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  `,
  styles: [`
    .predictions { max-width: 1600px; margin: 0 auto; }

    .ai-health-card {
      background: linear-gradient(135deg, rgba(108,99,255,0.08), rgba(67,217,173,0.05));
      border-color: rgba(108,99,255,0.25);
    }

    .health-circle {
      width: 80px; height: 80px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
    }
    .health-inner {
      width: 62px; height: 62px; border-radius: 50%;
      background: var(--bg-card);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .health-value {
      font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800;
      color: var(--primary-light); line-height: 1;
    }
    .health-label { font-size: 0.65rem; color: var(--text-muted); }

    .health-indicators { display: flex; flex-direction: column; gap: 0.5rem; }
    .hi-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; }
    .hi-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .hi-label { color: var(--text-secondary); width: 80px; }
    .hi-val { font-weight: 600; }
    .hi-good { color: var(--secondary); }
    .hi-warn { color: var(--accent-orange); }

    .tendance-badge {
      display: inline-flex; align-items: center; gap: 0.25rem;
      padding: 0.2rem 0.6rem; border-radius: 100px;
      font-size: 0.75rem; font-weight: 600;
    }
    .tendance-badge.hausse { background: rgba(67,217,173,0.15); color: var(--secondary); border: 1px solid rgba(67,217,173,0.3); }
    .tendance-badge.baisse { background: rgba(255,101,132,0.15); color: var(--accent-red); border: 1px solid rgba(255,101,132,0.3); }
    .tendance-badge.stable { background: rgba(255,255,255,0.05); color: var(--text-muted); border: 1px solid var(--border); }

    .sparkline {
      display: flex; align-items: flex-end; gap: 2px;
      height: 40px; background: rgba(255,255,255,0.02);
      border-radius: var(--radius-sm); padding: 4px;
    }
    .spark-bar { flex: 1; border-radius: 2px; min-height: 2px; opacity: 0.7; }

    .cat-item { padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.03); }
    .cat-item:last-child { border-bottom: none; }

    .rec-card {
      background: rgba(255,255,255,0.02); border-radius: var(--radius-sm);
      padding: 0.875rem; border-left: 3px solid var(--primary);
      transition: var(--transition);
    }
    .rec-card:hover { background: rgba(255,255,255,0.04); }
    .rec-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.375rem; }
    .rec-title { font-weight: 700; font-size: 0.8125rem; color: var(--text-primary); flex: 1; }
    .rec-message { font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.5rem; }
    .rec-action { font-size: 0.8rem; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 0.375rem; margin-bottom: 0.375rem; }
    .rec-impact { font-size: 0.75rem; color: var(--secondary); margin-bottom: 0.5rem; font-style: italic; }
    .rec-secondary { font-size: 0.75rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 0.2rem; }
  `]
})
export class PredictionsComponent implements OnInit {
  loading = true;
  tendances: any = null;
  recommandations: any = null;
  categories: any = null;
  predictionsDepots: any = null;
  predictionsReparations: any = null;
  predictionsDons: any = null;
  activePred = 'depots';

  constructor(private predictionService: PredictionService) {}

  ngOnInit() { this.loadData(); }

  loadData() {
    this.loading = true;
    forkJoin({
      tendances: this.predictionService.getTendances(),
      recommandations: this.predictionService.getRecommandations(),
      categories: this.predictionService.getCategoriesPopulaires(),
      depots: this.predictionService.getPredictionsDepots(),
      reparations: this.predictionService.getPredictionsReparations(),
      dons: this.predictionService.getPredictionsDons(),
    }).subscribe({
      next: (data) => {
        this.tendances = data.tendances;
        this.recommandations = data.recommandations;
        this.categories = data.categories;
        this.predictionsDepots = data.depots;
        this.predictionsReparations = data.reparations;
        this.predictionsDons = data.dons;
        this.loading = false;
      },
      error: () => { this.loading = false; }
    });
  }

  getHealthGradient(): string {
    const score = this.recommandations?.score_sante_plateforme ?? 0;
    return `conic-gradient(var(--primary) ${score}%, rgba(255,255,255,0.08) 0)`;
  }

  getCategoryColor(i: number): string {
    const colors = ['#6C63FF', '#FF6584', '#43D9AD', '#FFA726', '#29B6F6', '#FF8A65', '#AB47BC'];
    return colors[i % colors.length];
  }

  getPrioClass(prio: string): string {
    const map: Record<string, string> = {
      critique: 'badge-danger', haute: 'badge-warning',
      moyenne: 'badge-primary', basse: 'badge-info',
    };
    return map[prio] || 'badge-info';
  }

  exportReport() {
    alert('Génération du rapport IA en cours...');
  }
}
