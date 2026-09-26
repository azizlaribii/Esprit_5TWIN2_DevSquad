import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-depots-stub',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="animate-fade-in-up" style="max-width: 1600px; margin: 0 auto;">
      <div style="margin-bottom: 1.5rem;">
        <h1 class="page-title">Gestion des Dépôts</h1>
        <p class="page-subtitle">CRUD dépôts - À implémenter par l'équipe</p>
      </div>
      <div class="card" style="text-align: center; padding: 3rem; background: linear-gradient(135deg, rgba(108, 99, 255, 0.1), rgba(108, 99, 255, 0.05)); border-color: rgba(108, 99, 255, 0.3);">
        <span class="material-icons-round" style="font-size: 4rem; color: #6C63FF; margin-bottom: 1rem; display: block;">inventory_2</span>
        <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Gestion des Dépôts</div>
        <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto 1.5rem;">
          Ce module est assigné à votre équipe. Implémentez vos fonctionnalités CRUD ici.
        </p>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: rgba(108, 99, 255, 0.2); border: 1px solid rgba(108, 99, 255, 0.4); border-radius: var(--radius-sm); color: #6C63FF; font-weight: 600; font-size: 0.875rem;">
          <span class="material-icons-round" style="font-size: 1rem;">construction</span>
          Prêt pour développement équipe
        </div>
      </div>
    </div>
  `
})
export class DepotsComponent {}

@Component({
  selector: 'app-reparations-stub',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="animate-fade-in-up" style="max-width: 1600px; margin: 0 auto;">
      <div style="margin-bottom: 1.5rem;">
        <h1 class="page-title">Gestion des Réparations</h1>
        <p class="page-subtitle">CRUD réparations - À implémenter par l'équipe</p>
      </div>
      <div class="card" style="text-align: center; padding: 3rem; background: linear-gradient(135deg, rgba(255, 101, 132, 0.1), rgba(255, 101, 132, 0.05)); border-color: rgba(255, 101, 132, 0.3);">
        <span class="material-icons-round" style="font-size: 4rem; color: #FF6584; margin-bottom: 1rem; display: block;">build</span>
        <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Gestion des Réparations</div>
        <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto 1.5rem;">
          Ce module est assigné à votre équipe. Implémentez vos fonctionnalités CRUD ici.
        </p>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: rgba(255, 101, 132, 0.2); border: 1px solid rgba(255, 101, 132, 0.4); border-radius: var(--radius-sm); color: #FF6584; font-weight: 600; font-size: 0.875rem;">
          <span class="material-icons-round" style="font-size: 1rem;">construction</span>
          Prêt pour développement équipe
        </div>
      </div>
    </div>
  `
})
export class ReparationsComponent {}

@Component({
  selector: 'app-dons-stub',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="animate-fade-in-up" style="max-width: 1600px; margin: 0 auto;">
      <div style="margin-bottom: 1.5rem;">
        <h1 class="page-title">Gestion des Dons</h1>
        <p class="page-subtitle">CRUD dons - À implémenter par l'équipe</p>
      </div>
      <div class="card" style="text-align: center; padding: 3rem; background: linear-gradient(135deg, rgba(67, 217, 173, 0.1), rgba(67, 217, 173, 0.05)); border-color: rgba(67, 217, 173, 0.3);">
        <span class="material-icons-round" style="font-size: 4rem; color: #43D9AD; margin-bottom: 1rem; display: block;">volunteer_activism</span>
        <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Gestion des Dons</div>
        <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto 1.5rem;">
          Ce module est assigné à votre équipe. Implémentez vos fonctionnalités CRUD ici.
        </p>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: rgba(67, 217, 173, 0.2); border: 1px solid rgba(67, 217, 173, 0.4); border-radius: var(--radius-sm); color: #43D9AD; font-weight: 600; font-size: 0.875rem;">
          <span class="material-icons-round" style="font-size: 1rem;">construction</span>
          Prêt pour développement équipe
        </div>
      </div>
    </div>
  `
})
export class DonsComponent {}

@Component({
  selector: 'app-transformations-stub',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="animate-fade-in-up" style="max-width: 1600px; margin: 0 auto;">
      <div style="margin-bottom: 1.5rem;">
        <h1 class="page-title">Gestion des Transformations</h1>
        <p class="page-subtitle">CRUD transformations - À implémenter par l'équipe</p>
      </div>
      <div class="card" style="text-align: center; padding: 3rem; background: linear-gradient(135deg, rgba(255, 167, 38, 0.1), rgba(255, 167, 38, 0.05)); border-color: rgba(255, 167, 38, 0.3);">
        <span class="material-icons-round" style="font-size: 4rem; color: #FFA726; margin-bottom: 1rem; display: block;">auto_fix_high</span>
        <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Gestion des Transformations</div>
        <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto 1.5rem;">
          Ce module est assigné à votre équipe. Implémentez vos fonctionnalités CRUD ici.
        </p>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: rgba(255, 167, 38, 0.2); border: 1px solid rgba(255, 167, 38, 0.4); border-radius: var(--radius-sm); color: #FFA726; font-weight: 600; font-size: 0.875rem;">
          <span class="material-icons-round" style="font-size: 1rem;">construction</span>
          Prêt pour développement équipe
        </div>
      </div>
    </div>
  `
})
export class TransformationsComponent {}

@Component({
  selector: 'app-ateliers-stub',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="animate-fade-in-up" style="max-width: 1600px; margin: 0 auto;">
      <div style="margin-bottom: 1.5rem;">
        <h1 class="page-title">Gestion des Ateliers</h1>
        <p class="page-subtitle">CRUD ateliers - À implémenter par l'équipe</p>
      </div>
      <div class="card" style="text-align: center; padding: 3rem; background: linear-gradient(135deg, rgba(41, 182, 246, 0.1), rgba(41, 182, 246, 0.05)); border-color: rgba(41, 182, 246, 0.3);">
        <span class="material-icons-round" style="font-size: 4rem; color: #29B6F6; margin-bottom: 1rem; display: block;">storefront</span>
        <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Gestion des Ateliers</div>
        <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto 1.5rem;">
          Ce module est assigné à votre équipe. Implémentez vos fonctionnalités CRUD ici.
        </p>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: rgba(41, 182, 246, 0.2); border: 1px solid rgba(41, 182, 246, 0.4); border-radius: var(--radius-sm); color: #29B6F6; font-weight: 600; font-size: 0.875rem;">
          <span class="material-icons-round" style="font-size: 1rem;">construction</span>
          Prêt pour développement équipe
        </div>
      </div>
    </div>
  `
})
export class AteliersComponent {}

@Component({
  selector: 'app-associations-stub',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="animate-fade-in-up" style="max-width: 1600px; margin: 0 auto;">
      <div style="margin-bottom: 1.5rem;">
        <h1 class="page-title">Gestion des Associations</h1>
        <p class="page-subtitle">CRUD associations - À implémenter par l'équipe</p>
      </div>
      <div class="card" style="text-align: center; padding: 3rem; background: linear-gradient(135deg, rgba(171, 71, 188, 0.1), rgba(171, 71, 188, 0.05)); border-color: rgba(171, 71, 188, 0.3);">
        <span class="material-icons-round" style="font-size: 4rem; color: #AB47BC; margin-bottom: 1rem; display: block;">groups</span>
        <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Gestion des Associations</div>
        <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto 1.5rem;">
          Ce module est assigné à votre équipe. Implémentez vos fonctionnalités CRUD ici.
        </p>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: rgba(171, 71, 188, 0.2); border: 1px solid rgba(171, 71, 188, 0.4); border-radius: var(--radius-sm); color: #AB47BC; font-weight: 600; font-size: 0.875rem;">
          <span class="material-icons-round" style="font-size: 1rem;">construction</span>
          Prêt pour développement équipe
        </div>
      </div>
    </div>
  `
})
export class AssociationsComponent {}

@Component({
  selector: 'app-impact-stub',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="animate-fade-in-up" style="max-width: 1600px; margin: 0 auto;">
      <div style="margin-bottom: 1.5rem;">
        <h1 class="page-title">Impact Écologique</h1>
        <p class="page-subtitle">Visualisation de l'impact - À implémenter par l'équipe</p>
      </div>
      <div class="card" style="text-align: center; padding: 3rem; background: linear-gradient(135deg, rgba(102, 187, 106, 0.1), rgba(102, 187, 106, 0.05)); border-color: rgba(102, 187, 106, 0.3);">
        <span class="material-icons-round" style="font-size: 4rem; color: #66BB6A; margin-bottom: 1rem; display: block;">eco</span>
        <div style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Impact Écologique</div>
        <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto 1.5rem;">
          Ce module est assigné à votre équipe. Implémentez vos fonctionnalités de suivi d'impact ici.
        </p>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: rgba(102, 187, 106, 0.2); border: 1px solid rgba(102, 187, 106, 0.4); border-radius: var(--radius-sm); color: #66BB6A; font-weight: 600; font-size: 0.875rem;">
          <span class="material-icons-round" style="font-size: 1rem;">construction</span>
          Prêt pour développement équipe
        </div>
      </div>
    </div>
  `
})
export class ImpactComponent {}
