import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-statistiques',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="animate-fade-in-up">
      <div class="section-header" style="margin-bottom: 1.5rem;">
        <div>
          <h1 class="page-title">Statistiques Globales</h1>
          <p class="page-subtitle">Analyse complète des données de la plateforme</p>
        </div>
      </div>

      <!-- Quick Stats Grid -->
      <div class="grid-4 section">
        <div *ngFor="let stat of stats" class="card" style="text-align: center; padding: 1.5rem;">
          <div class="kpi-icon-wrap" style="margin: 0 auto 0.75rem;" [style.background]="stat.color + '20'">
            <span class="material-icons-round" [style.color]="stat.color">{{ stat.icon }}</span>
          </div>
          <div style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: var(--text-primary);">{{ stat.value }}</div>
          <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">{{ stat.label }}</div>
        </div>
      </div>

      <!-- Par statut -->
      <div class="grid-2 section">
        <div class="card" id="card-depot-statut">
          <div class="card-title" style="margin-bottom: 1rem;">Dépôts par Statut</div>
          <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div *ngFor="let s of depotsStatut">
              <div style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-bottom: 0.35rem;">
                <span style="color: var(--text-secondary);">{{ s.label }}</span>
                <span style="font-weight: 600; color: var(--text-primary);">{{ s.count }}</span>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" [style.width]="((s.count / 1247) * 100) + '%'" [style.background]="s.color"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="card" id="card-evolution-mois">
          <div class="card-title" style="margin-bottom: 1rem;">Évolution Mensuelle (6 mois)</div>
          <div style="display: flex; align-items: flex-end; gap: 0.5rem; height: 150px;">
            <div *ngFor="let m of evolutionMois" style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.375rem; height: 100%;">
              <div style="flex: 1; display: flex; align-items: flex-end; width: 100%;">
                <div style="width: 100%; border-radius: 4px 4px 0 0; background: var(--gradient-primary); min-height: 4px;"
                     [style.height.%]="(m.depots / 130) * 100"></div>
              </div>
              <div style="font-size: 0.7rem; color: var(--text-muted);">{{ m.label }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Par catégorie -->
      <div class="card section" id="card-categories-table">
        <div class="card-header">
          <div class="card-title">Dépôts par Catégorie</div>
        </div>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Catégorie</th>
                <th>Total</th>
                <th>Valorisés</th>
                <th>Taux</th>
                <th>Tendance</th>
              </tr>
            </thead>
            <tbody>
              <tr *ngFor="let cat of categoriesTable">
                <td><strong style="color: var(--text-primary);">{{ cat.categorie }}</strong></td>
                <td>{{ cat.total }}</td>
                <td style="color: var(--secondary);">{{ cat.valorises }}</td>
                <td>
                  <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="progress-bar" style="width: 60px;">
                      <div class="progress-fill" [style.width]="cat.taux + '%'" style="background: var(--gradient-green);"></div>
                    </div>
                    <span>{{ cat.taux }}%</span>
                  </div>
                </td>
                <td>
                  <span class="badge" [class.badge-success]="cat.tendance === 'hausse'" [class.badge-danger]="cat.tendance === 'baisse'" [class.badge-warning]="cat.tendance === 'stable'">
                    {{ cat.tendance }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `
})
export class StatistiquesComponent {
  stats = [
    { label: 'Total Dépôts', value: '1 247', icon: 'inventory_2', color: '#6C63FF' },
    { label: 'Total Réparations', value: '834', icon: 'build', color: '#FF6584' },
    { label: 'Total Dons', value: '2 156', icon: 'volunteer_activism', color: '#43D9AD' },
    { label: 'Transformations', value: '312', icon: 'auto_fix_high', color: '#FFA726' },
  ];

  depotsStatut = [
    { label: 'Valorisés', count: 892, color: '#43D9AD' },
    { label: 'En attente', count: 247, color: '#FFA726' },
    { label: 'En traitement', count: 86, color: '#6C63FF' },
    { label: 'Rejetés', count: 22, color: '#FF6584' },
  ];

  evolutionMois = [
    { label: 'Avr', depots: 89 },
    { label: 'Mai', depots: 102 },
    { label: 'Jun', depots: 95 },
    { label: 'Jul', depots: 118 },
    { label: 'Aoû', depots: 108 },
    { label: 'Sep', depots: 124 },
  ];

  categoriesTable = [
    { categorie: 'Vestes & Manteaux', total: 287, valorises: 231, taux: 80.5, tendance: 'hausse' },
    { categorie: 'Pantalons & Jeans', total: 245, valorises: 189, taux: 77.1, tendance: 'stable' },
    { categorie: 'Robes & Jupes', total: 198, valorises: 158, taux: 79.8, tendance: 'hausse' },
    { categorie: 'T-shirts & Polos', total: 176, valorises: 127, taux: 72.2, tendance: 'stable' },
    { categorie: 'Chaussures', total: 145, valorises: 98, taux: 67.6, tendance: 'baisse' },
  ];
}
