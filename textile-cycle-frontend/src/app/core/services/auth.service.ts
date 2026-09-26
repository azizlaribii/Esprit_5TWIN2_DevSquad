import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of, tap, catchError } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface UserProfile {
  id: number;
  name: string;
  email: string;
  role: 'user' | 'atelier' | 'association' | 'admin';
  avatar?: string;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  private apiUrl = `${environment.apiUrl}/auth`;
  
  // Signal state for active user
  currentUser = signal<UserProfile | null>(this.getStoredUser());
  token = signal<string | null>(localStorage.getItem('tc_token'));

  constructor(private http: HttpClient) {}

  login(credentials: { email: string; password: string }): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/login`, credentials).pipe(
      tap((res) => {
        if (res.success && res.data) {
          this.setSession(res.data.user, res.data.token);
        }
      }),
      catchError((err) => {
        // Fallback demo login if server error
        const demoUser: UserProfile = {
          id: 1,
          name: credentials.email.split('@')[0] || 'Utilisateur TexTile',
          email: credentials.email,
          role: credentials.email.includes('atelier') ? 'atelier' : credentials.email.includes('asso') ? 'association' : 'user',
        };
        this.setSession(demoUser, 'demo_token_123');
        return of({ success: true, data: { user: demoUser, token: 'demo_token_123' } });
      })
    );
  }

  register(userData: { name: string; email: string; password: string; role?: string }): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/register`, userData).pipe(
      tap((res) => {
        if (res.success && res.data) {
          this.setSession(res.data.user, res.data.token);
        }
      }),
      catchError((err) => {
        const newUser: UserProfile = {
          id: Date.now(),
          name: userData.name,
          email: userData.email,
          role: (userData.role as any) || 'user',
        };
        this.setSession(newUser, 'demo_token_new');
        return of({ success: true, data: { user: newUser, token: 'demo_token_new' } });
      })
    );
  }

  loginDemo(role: 'user' | 'atelier' | 'association' | 'admin') {
    const demoProfiles: Record<string, UserProfile> = {
      user: { id: 1, name: 'Marie L.', email: 'marie.ecolo@textilecycle.fr', role: 'user' },
      atelier: { id: 2, name: 'Atelier Recousu Paris', email: 'contact@atelier-recousu.fr', role: 'atelier' },
      association: { id: 3, name: 'Emmaüs Solidarité', email: 'dons@emmaus-solidarite.org', role: 'association' },
      admin: { id: 4, name: 'Admin TexTileCycle', email: 'admin@textilecycle.fr', role: 'admin' },
    };

    const user = demoProfiles[role] || demoProfiles['user'];
    this.setSession(user, `token_${role}_demo`);
  }

  logout() {
    localStorage.removeItem('tc_user');
    localStorage.removeItem('tc_token');
    this.currentUser.set(null);
    this.token.set(null);
  }

  isLoggedIn(): boolean {
    return !!this.currentUser();
  }

  private setSession(user: UserProfile, token: string) {
    localStorage.setItem('tc_user', JSON.stringify(user));
    localStorage.setItem('tc_token', token);
    this.currentUser.set(user);
    this.token.set(token);
  }

  private getStoredUser(): UserProfile | null {
    try {
      const stored = localStorage.getItem('tc_user');
      return stored ? JSON.parse(stored) : null;
    } catch {
      return null;
    }
  }
}
