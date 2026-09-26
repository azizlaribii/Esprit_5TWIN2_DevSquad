import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';

@Injectable({ providedIn: 'root' })
export class DashboardService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getOverview(): Observable<any> {
    return this.http.get(`${this.apiUrl}/dashboard/overview`).pipe(
      map((res: any) => res.data),
      catchError(() => of(this.getMockOverview()))
    );
  }

  getKpis(): Observable<any[]> {
    return this.http.get(`${this.apiUrl}/dashboard/kpis`).pipe(
      map((res: any) => res.data),
      catchError(() => of(this.getMockKpis()))
    );
  }

  getRecentActivity(): Observable<any[]> {
    return this.http.get(`${this.apiUrl}/dashboard/recent-activity`).pipe(
      map((res: any) => res.data),
      catchError(() => of(this.getMockActivity()))
    );
  }

  getAlerts(): Observable<any[]> {
    return this.http.get(`${this.apiUrl}/dashboard/alerts`).pipe(
      map((res: any) => res.data),
      catchError(() => of([]))
    );
  }

  // === Mock data for demo ===
  private getMockOverview() {
    return {
      resume: {
        total_depots: 1247,
        total_reparations: 834,
        total_dons: 2156,
        total_transformations: 312,
        utilisateurs_actifs: 487,
        ateliers_actifs: 18,
        associations_actives: 24,
      },
      ce_mois: { depots: 124, reparations: 89, dons: 213 },
      mois_precedent: { depots: 108, reparations: 76, dons: 189 },
      impact_ecologique: {
        kg_vetements_sauves: 623.5,
        co2_evite_kg: 1558.75,
        eau_economisee_litres: 124700,
      },
      evolution_semaine: this.getWeekData(),
    };
  }

  private getMockKpis() {
    return [
      { id: 'depots_actifs', label: 'Dépôts en attente', valeur: 47, variation: 14.8, unite: 'articles', icone: 'inventory', couleur: '#6C63FF' },
      { id: 'reparations_cours', label: 'Réparations en cours', valeur: 23, variation: -5.2, unite: 'articles', icone: 'build', couleur: '#FF6584' },
      { id: 'dons_disponibles', label: 'Dons disponibles', valeur: 89, variation: 22.1, unite: 'articles', icone: 'volunteer_activism', couleur: '#43D9AD' },
      { id: 'transformations_finalisees', label: 'Transformations finalisées', valeur: 15, variation: 7.3, unite: 'créations', icone: 'auto_fix_high', couleur: '#FFA726' },
      { id: 'taux_valorisation', label: 'Taux de valorisation', valeur: 76.4, variation: 2.5, unite: '%', icone: 'trending_up', couleur: '#29B6F6' },
      { id: 'score_eco', label: 'Score éco-impact', valeur: 78, variation: 1.8, unite: '/100', icone: 'eco', couleur: '#66BB6A' },
    ];
  }

  private getMockActivity() {
    return [
      { type: 'depot', icone: 'inventory_2', couleur: '#6C63FF', message: 'Nouveau dépôt : Vestes & Manteaux', utilisateur: 'Marie L.', date: 'Il y a 5 min' },
      { type: 'reparation', icone: 'build', couleur: '#FF6584', message: 'Réparation demandée : Couture pantalon', utilisateur: 'Ahmed K.', date: 'Il y a 12 min' },
      { type: 'don', icone: 'volunteer_activism', couleur: '#43D9AD', message: 'Don enregistré : 12 articles', utilisateur: 'Sophie M.', date: 'Il y a 28 min' },
      { type: 'transformation', icone: 'auto_fix_high', couleur: '#FFA726', message: 'Transformation finalisée : Sac DIY', utilisateur: 'Atelier Eco', date: 'Il y a 1h' },
      { type: 'depot', icone: 'inventory_2', couleur: '#6C63FF', message: 'Nouveau dépôt : Robes & Jupes', utilisateur: 'Julie P.', date: 'Il y a 1h 20' },
      { type: 'reparation', icone: 'build', couleur: '#FF6584', message: 'Réparation terminée : Fermeture éclair', utilisateur: 'Atelier Mode', date: 'Il y a 2h' },
      { type: 'don', icone: 'volunteer_activism', couleur: '#43D9AD', message: 'Don accepté par Emmaüs Paris', utilisateur: 'Thomas B.', date: 'Il y a 3h' },
    ];
  }

  private getWeekData() {
    const days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    return days.map(d => ({
      date: d,
      depots: Math.floor(Math.random() * 25) + 5,
      reparations: Math.floor(Math.random() * 15) + 3,
      dons: Math.floor(Math.random() * 30) + 8,
    }));
  }
}
