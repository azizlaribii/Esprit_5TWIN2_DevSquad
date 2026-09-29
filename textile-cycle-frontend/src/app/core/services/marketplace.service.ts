import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

export interface ArticleMarketplace {
  id: number;
  titre: string;
  description: string;
  categorie: string;
  marque?: string;
  taille: string;
  genre: string;
  etat: string;
  type: 'vente' | 'echange' | 'don';
  prix?: number;
  article_echange?: string;
  image_url?: string;
  statut: string;
  ai_score: number;
  ai_prix_min?: number;
  ai_prix_max?: number;
  ai_classification?: string;
  note_vendeur?: number;
  vues: number;
  is_favori?: boolean;
  vendeur?: { id: number; name: string };
  created_at: string;
}

export interface MarketplaceFilters {
  q?: string;
  categorie?: string;
  type?: string;
  taille?: string;
  prix_min?: number;
  prix_max?: number;
  etat?: string[];
  genre?: string[];
  tri?: string;
  page?: number;
}

export interface PaginatedArticles {
  data: ArticleMarketplace[];
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

@Injectable({ providedIn: 'root' })
export class MarketplaceService {
  private apiBase = 'http://localhost:8000/api/marketplace';

  constructor(private http: HttpClient) {}

  getArticles(filters: MarketplaceFilters = {}): Observable<PaginatedArticles> {
    let params = new HttpParams();
    Object.entries(filters).forEach(([key, val]) => {
      if (val !== undefined && val !== null && val !== '') {
        if (Array.isArray(val)) {
          val.forEach(v => params = params.append(key + '[]', v));
        } else {
          params = params.set(key, String(val));
        }
      }
    });
    return this.http.get<any>(this.apiBase, { params }).pipe(
      map(r => r.data || r)
    );
  }

  getArticle(id: number): Observable<ArticleMarketplace> {
    return this.http.get<any>(`${this.apiBase}/${id}`).pipe(map(r => r.data || r));
  }

  createArticle(formData: FormData): Observable<ArticleMarketplace> {
    return this.http.post<any>(this.apiBase, formData).pipe(map(r => r.data || r));
  }

  updateArticle(id: number, formData: FormData): Observable<ArticleMarketplace> {
    formData.append('_method', 'PUT');
    return this.http.post<any>(`${this.apiBase}/${id}`, formData).pipe(map(r => r.data || r));
  }

  deleteArticle(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiBase}/${id}`);
  }

  toggleFavori(id: number): Observable<any> {
    return this.http.post<any>(`${this.apiBase}/${id}/favori`, {});
  }

  demandeAchat(id: number, message?: string): Observable<any> {
    return this.http.post<any>(`${this.apiBase}/${id}/demande`, { message });
  }

  evaluerVendeur(id: number, note: number, commentaire?: string): Observable<any> {
    return this.http.post<any>(`${this.apiBase}/${id}/evaluer`, { note, commentaire });
  }

  getRecommandationsIA(): Observable<ArticleMarketplace[]> {
    return this.http.get<any>(`${this.apiBase}/recommandations-ia`).pipe(
      map(r => r.data || r)
    );
  }

  getFavoris(): Observable<ArticleMarketplace[]> {
    return this.http.get<any>(`${this.apiBase}/mes-favoris`).pipe(
      map(r => r.data || r)
    );
  }

  // Estimation IA du prix côté client (heuristique)
  estimerPrixIA(categorie: string, etat: string, marque?: string): { min: number; max: number } {
    const base: Record<string, number> = {
      'Vestes & Manteaux': 45, 'Robes & Jupes': 30, 'Jeans & Pantalons': 25,
      'Chaussures': 35, 'T-Shirts & Tops': 15, 'Sportswear': 20, 'Accessoires': 12
    };
    const etatMult: Record<string, number> = {
      'Neuf avec étiquette': 1.5, 'Très bon état': 1.2, 'Bon état': 1.0, 'État correct': 0.6
    };
    const premiumBrands = ['Nike', 'Adidas', 'Zara', 'H&M', 'Levi\'s', 'Ralph Lauren'];
    const brandBonus = marque && premiumBrands.includes(marque) ? 1.3 : 1.0;
    const est = (base[categorie] || 20) * (etatMult[etat] || 1.0) * brandBonus;
    return { min: Math.round(est * 0.8), max: Math.round(est * 1.2) };
  }
}
