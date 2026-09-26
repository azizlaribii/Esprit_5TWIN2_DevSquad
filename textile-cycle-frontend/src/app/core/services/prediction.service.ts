import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';

@Injectable({ providedIn: 'root' })
export class PredictionService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getPredictionsDepots(): Observable<any> {
    return this.http.get(`${this.apiUrl}/predictions/depots`).pipe(
      map((res: any) => res.data),
      catchError(() => of(this.getMockPredictions('depots')))
    );
  }

  getPredictionsReparations(): Observable<any> {
    return this.http.get(`${this.apiUrl}/predictions/reparations`).pipe(
      map((res: any) => res.data),
      catchError(() => of(this.getMockPredictions('reparations')))
    );
  }

  getPredictionsDons(): Observable<any> {
    return this.http.get(`${this.apiUrl}/predictions/dons`).pipe(
      map((res: any) => res.data),
      catchError(() => of(this.getMockPredictions('dons')))
    );
  }

  getTendances(): Observable<any> {
    return this.http.get(`${this.apiUrl}/predictions/tendances`).pipe(
      map((res: any) => res.data),
      catchError(() => of(this.getMockTendances()))
    );
  }

  getRecommandations(): Observable<any> {
    return this.http.get(`${this.apiUrl}/predictions/recommandations`).pipe(
      map((res: any) => res.data),
      catchError(() => of(this.getMockRecommandations()))
    );
  }

  getCategoriesPopulaires(): Observable<any> {
    return this.http.get(`${this.apiUrl}/predictions/categories-populaires`).pipe(
      map((res: any) => res.data),
      catchError(() => of(this.getMockCategories()))
    );
  }

  // === Mocks ===
  private getMockPredictions(type: string) {
    const predictions = [];
    let baseValue = type === 'depots' ? 18 : type === 'reparations' ? 12 : 24;
    for (let i = 1; i <= 30; i++) {
      const date = new Date();
      date.setDate(date.getDate() + i);
      const val = Math.round(baseValue + (Math.random() - 0.4) * 5 + i * 0.2);
      predictions.push({
        date: date.toISOString().split('T')[0],
        valeur_predite: val,
        intervalle_bas: Math.round(val * 0.8),
        intervalle_haut: Math.round(val * 1.2),
      });
    }
    return {
      predictions,
      tendance: 'hausse',
      moyenne_historique: baseValue,
      confiance: 0.82,
      modele: 'Prophet',
    };
  }

  private getMockTendances() {
    return {
      tendances: [
        { metrique: 'Dépôts', couleur: '#6C63FF', direction: 'hausse', pourcentage: 14.8, valeur_actuelle: 124, moyenne: 108 },
        { metrique: 'Réparations', couleur: '#FF6584', direction: 'stable', pourcentage: -2.3, valeur_actuelle: 89, moyenne: 91 },
        { metrique: 'Dons', couleur: '#43D9AD', direction: 'hausse', pourcentage: 22.1, valeur_actuelle: 213, moyenne: 175 },
      ],
      insights: [
        '📈 Les dépôts sont en forte hausse (+14.8%). Préparez-vous à une augmentation de la demande.',
        '📊 La catégorie la plus populaire est "Vestes & Manteaux". Concentrez les efforts sur cette catégorie.',
        '🌿 Excellent taux de valorisation (76.4%) ! La plateforme remplit pleinement sa mission.',
      ],
      valorisation: { taux_moyen: 76.4, tendance: 'hausse' },
    };
  }

  private getMockRecommandations() {
    return {
      recommandations: [
        {
          id: 'depots_eleves', priorite: 'haute', type: 'operationnel',
          titre: '⚠️ Dépôts en attente importants',
          message: '47 dépôts en attente. Planifier une augmentation de la capacité.',
          action_principale: 'Contacter 2-3 ateliers supplémentaires',
          actions_secondaires: ['Analyser les catégories les plus représentées', 'Préparer des sessions de tri'],
          impact_potentiel: 'Réduction de 40% du délai moyen',
          couleur: '#FFA726',
        },
        {
          id: 'categorie_opportunite', priorite: 'moyenne', type: 'stratégique',
          titre: "📊 Opportunité : catégorie 'Vestes & Manteaux'",
          message: "Cette catégorie représente 23% des dépôts. Développer une expertise spécialisée.",
          action_principale: 'Développer une expertise spécialisée pour cette catégorie',
          actions_secondaires: ['Former des ateliers sur cette catégorie', 'Créer des contenus éducatifs'],
          impact_potentiel: 'Augmentation de 30% du taux de valorisation',
          couleur: '#6C63FF',
        },
        {
          id: 'sensibilisation', priorite: 'basse', type: 'communication',
          titre: '📣 Campagne de sensibilisation saisonnière',
          message: "La période actuelle est favorable pour une campagne de sensibilisation.",
          action_principale: 'Planifier une campagne sur les réseaux sociaux',
          actions_secondaires: ['Contenu sur l\'impact écologique', 'Témoignages d\'utilisateurs'],
          impact_potentiel: 'Augmentation de 25% des nouveaux utilisateurs',
          couleur: '#29B6F6',
        },
      ],
      score_sante_plateforme: 78,
      nb_actions_urgentes: 1,
    };
  }

  private getMockCategories() {
    return {
      categories: [
        { categorie: 'Vestes & Manteaux', count: 287, part: 23, prediction_mois_prochain: 301, tendance: 'hausse' },
        { categorie: 'Pantalons & Jeans', count: 245, part: 19.6, prediction_mois_prochain: 257, tendance: 'stable' },
        { categorie: 'Robes & Jupes', count: 198, part: 15.9, prediction_mois_prochain: 208, tendance: 'hausse' },
        { categorie: 'T-shirts & Polos', count: 176, part: 14.1, prediction_mois_prochain: 185, tendance: 'stable' },
        { categorie: 'Chaussures', count: 145, part: 11.6, prediction_mois_prochain: 152, tendance: 'baisse' },
        { categorie: 'Accessoires', count: 112, part: 9.0, prediction_mois_prochain: 118, tendance: 'hausse' },
        { categorie: 'Autres', count: 84, part: 6.7, prediction_mois_prochain: 88, tendance: 'stable' },
      ],
    };
  }
}
