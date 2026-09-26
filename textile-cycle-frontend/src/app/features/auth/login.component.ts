import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <div class="auth-page animate-fade-in">
      <!-- Background Ambient Glows -->
      <div class="glow-orb orb-1"></div>
      <div class="glow-orb orb-2"></div>

      <div class="auth-container">
        
        <!-- Left Banner Card -->
        <div class="auth-banner">
          <div class="banner-badge">
            <span class="material-icons-round" style="font-size: 1rem;">eco</span>
            Économie Circulaire Textile
          </div>

          <div class="banner-title">
            Donnez une <span class="gradient-text">seconde vie</span> à vos vêtements.
          </div>

          <p class="banner-subtitle">
            Rejoignez le réseau TexTileCycle mettant en relation particuliers, ateliers d'upcycling et associations caritatives.
          </p>

          <!-- Impact Features List -->
          <div class="features-list">
            <div class="feature-item">
              <div class="feature-icon" style="background: rgba(108, 99, 255, 0.2); color: #6C63FF;">
                <span class="material-icons-round">inventory_2</span>
              </div>
              <div>
                <div class="feature-head">Dépôt & Valorisation</div>
                <div class="feature-sub">Faites recycler ou transformer vos textiles usagés.</div>
              </div>
            </div>

            <div class="feature-item">
              <div class="feature-icon" style="background: rgba(255, 101, 132, 0.2); color: #FF6584;">
                <span class="material-icons-round">build</span>
              </div>
              <div>
                <div class="feature-head">Ateliers de Réparation</div>
                <div class="feature-sub">Faites réparer par des artisans qualifiés près de chez vous.</div>
              </div>
            </div>

            <div class="feature-item">
              <div class="feature-icon" style="background: rgba(67, 217, 173, 0.2); color: #43D9AD;">
                <span class="material-icons-round">volunteer_activism</span>
              </div>
              <div>
                <div class="feature-head">Dons Solidaire</div>
                <div class="feature-sub">Offrez vos vêtements aux associations caritatives.</div>
              </div>
            </div>
          </div>

          <!-- Bottom Stat Preview -->
          <div class="banner-footer-stat">
            <div class="stat-box">
              <span class="stat-number">1 247+</span>
              <span class="stat-label">Vêtements sauvés</span>
            </div>
            <div class="stat-box">
              <span class="stat-number">1.5T</span>
              <span class="stat-label">CO₂ évités</span>
            </div>
          </div>
        </div>

        <!-- Right Form Card -->
        <div class="auth-card">
          <!-- Logo & Header -->
          <div class="auth-header">
            <div class="brand-logo">
              <div class="logo-icon">
                <span class="material-icons-round">recycling</span>
              </div>
              <span class="logo-text">TexTile<span class="logo-highlight">Cycle</span></span>
            </div>
            <h2 class="auth-heading">{{ activeTab === 'login' ? 'Bienvenue !' : 'Créer un Compte' }}</h2>
            <p class="auth-subheading">
              {{ activeTab === 'login' ? 'Connectez-vous pour accéder à votre espace intelligent' : 'Choisissez votre profil et rejoignez le réseau' }}
            </p>
          </div>

          <!-- Tabs Toggle -->
          <div class="auth-tabs">
            <button class="tab-btn" [class.active]="activeTab === 'login'" (click)="activeTab = 'login'">
              <span class="material-icons-round" style="font-size: 1.1rem;">login</span>
              Se connecter
            </button>
            <button class="tab-btn" [class.active]="activeTab === 'register'" (click)="activeTab = 'register'">
              <span class="material-icons-round" style="font-size: 1.1rem;">person_add</span>
              S'inscrire
            </button>
          </div>

          <!-- Error Feedback -->
          <div *ngIf="errorMessage" class="alert alert-danger animate-shake">
            <span class="material-icons-round">error_outline</span>
            {{ errorMessage }}
          </div>

          <!-- Success Feedback -->
          <div *ngIf="successMessage" class="alert alert-success animate-fade-in">
            <span class="material-icons-round">check_circle</span>
            {{ successMessage }}
          </div>

          <!-- ==================== LOGIN FORM ==================== -->
          <form *ngIf="activeTab === 'login'" (ngSubmit)="onLogin()" #loginForm="ngForm" class="auth-form">
            <div class="form-group">
              <label class="form-label">Adresse E-mail</label>
              <div class="input-wrapper">
                <span class="material-icons-round input-icon">email</span>
                <input
                  type="email"
                  class="form-input"
                  placeholder="exemple@textilecycle.fr"
                  [(ngModel)]="email"
                  name="email"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label class="form-label" style="margin-bottom: 0;">Mot de passe</label>
                <a href="#" (click)="$event.preventDefault()" style="font-size: 0.8rem; color: var(--primary-light);">Oublié ?</a>
              </div>
              <div class="input-wrapper">
                <span class="material-icons-round input-icon">lock</span>
                <input
                  [type]="showPassword ? 'text' : 'password'"
                  class="form-input"
                  placeholder="••••••••"
                  [(ngModel)]="password"
                  name="password"
                  required
                />
                <button type="button" class="btn-toggle-eye" (click)="showPassword = !showPassword">
                  <span class="material-icons-round">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" [disabled]="loading">
              <span class="material-icons-round" [class.spin]="loading">{{ loading ? 'sync' : 'arrow_forward' }}</span>
              {{ loading ? 'Connexion en cours...' : 'Se connecter' }}
            </button>
          </form>

          <!-- ==================== REGISTER FORM ==================== -->
          <form *ngIf="activeTab === 'register'" (ngSubmit)="onRegister()" #registerForm="ngForm" class="auth-form">
            
            <!-- Role Picker -->
            <div class="form-group">
              <label class="form-label">Vous êtes :</label>
              <div class="role-grid">
                <div
                  class="role-card"
                  [class.selected]="selectedRole === 'user'"
                  (click)="selectedRole = 'user'"
                >
                  <span class="material-icons-round role-icon" style="color: #6C63FF;">person</span>
                  <div class="role-name">Particulier</div>
                  <div class="role-desc">Donner & réparer</div>
                </div>

                <div
                  class="role-card"
                  [class.selected]="selectedRole === 'atelier'"
                  (click)="selectedRole = 'atelier'"
                >
                  <span class="material-icons-round role-icon" style="color: #FF6584;">storefront</span>
                  <div class="role-name">Atelier</div>
                  <div class="role-desc">Artisan / Couture</div>
                </div>

                <div
                  class="role-card"
                  [class.selected]="selectedRole === 'association'"
                  (click)="selectedRole = 'association'"
                >
                  <span class="material-icons-round role-icon" style="color: #43D9AD;">groups</span>
                  <div class="role-name">Association</div>
                  <div class="role-desc">Collecte & Dons</div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Nom complet / Raison sociale</label>
              <div class="input-wrapper">
                <span class="material-icons-round input-icon">badge</span>
                <input
                  type="text"
                  class="form-input"
                  placeholder="ex: Marie Laurent ou Atelier Eco"
                  [(ngModel)]="name"
                  name="name"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Adresse E-mail</label>
              <div class="input-wrapper">
                <span class="material-icons-round input-icon">email</span>
                <input
                  type="email"
                  class="form-input"
                  placeholder="contact@domaine.fr"
                  [(ngModel)]="email"
                  name="email"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Mot de passe</label>
              <div class="input-wrapper">
                <span class="material-icons-round input-icon">lock</span>
                <input
                  [type]="showPassword ? 'text' : 'password'"
                  class="form-input"
                  placeholder="•••••••• (6 caract. min)"
                  [(ngModel)]="password"
                  name="password"
                  required
                />
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" [disabled]="loading">
              <span class="material-icons-round" [class.spin]="loading">{{ loading ? 'sync' : 'check_circle' }}</span>
              {{ loading ? 'Création en cours...' : 'Créer mon compte' }}
            </button>
          </form>

          <!-- ==================== DEMO ONE-CLICK LOGIN ==================== -->
          <div class="demo-divider">
            <span>Ou tester en un clic (Comptes Démo)</span>
          </div>

          <div class="demo-buttons">
            <button class="btn-demo" (click)="quickDemo('user')">
              <span class="material-icons-round" style="color: #6C63FF;">person</span>
              Particulier
            </button>

            <button class="btn-demo" (click)="quickDemo('atelier')">
              <span class="material-icons-round" style="color: #FF6584;">build</span>
              Atelier
            </button>

            <button class="btn-demo" (click)="quickDemo('association')">
              <span class="material-icons-round" style="color: #43D9AD;">volunteer_activism</span>
              Association
            </button>
          </div>

        </div>
      </div>
    </div>
  `,
  styles: [`
    .auth-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      position: relative;
      overflow: hidden;
      background: radial-gradient(circle at 50% 10%, #1e1b4b 0%, #0f172a 70%);
    }

    /* Ambient Glow Background Orbs */
    .glow-orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(100px);
      opacity: 0.35;
      pointer-events: none;
    }
    .orb-1 {
      width: 450px;
      height: 450px;
      background: #6C63FF;
      top: -100px;
      left: -100px;
    }
    .orb-2 {
      width: 400px;
      height: 400px;
      background: #43D9AD;
      bottom: -100px;
      right: -100px;
    }

    .auth-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      max-width: 1100px;
      width: 100%;
      background: rgba(30, 41, 59, 0.7);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: var(--radius-lg);
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
      overflow: hidden;
    }

    @media (max-width: 900px) {
      .auth-container {
        grid-template-columns: 1fr;
      }
      .auth-banner {
        display: none;
      }
    }

    /* Left Banner */
    .auth-banner {
      padding: 3.5rem 3rem;
      background: linear-gradient(135deg, rgba(108, 99, 255, 0.15) 0%, rgba(67, 217, 173, 0.08) 100%);
      border-right: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .banner-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.375rem 0.875rem;
      background: rgba(67, 217, 173, 0.15);
      border: 1px solid rgba(67, 217, 173, 0.3);
      border-radius: 9999px;
      color: #43D9AD;
      font-size: 0.8rem;
      font-weight: 600;
      width: fit-content;
      margin-bottom: 1.5rem;
    }

    .banner-title {
      font-family: 'Outfit', sans-serif;
      font-size: 2.25rem;
      font-weight: 800;
      line-height: 1.25;
      color: var(--text-primary);
      margin-bottom: 1rem;
    }

    .gradient-text {
      background: linear-gradient(135deg, #6C63FF, #43D9AD);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .banner-subtitle {
      color: var(--text-secondary);
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 2rem;
    }

    .features-list {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
      margin-bottom: 2rem;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .feature-icon {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .feature-head {
      font-weight: 700;
      font-size: 0.9rem;
      color: var(--text-primary);
    }

    .feature-sub {
      font-size: 0.8rem;
      color: var(--text-muted);
      margin-top: 0.1rem;
    }

    .banner-footer-stat {
      display: flex;
      gap: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .stat-box {
      display: flex;
      flex-direction: column;
    }

    .stat-number {
      font-family: 'Outfit', sans-serif;
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--text-primary);
    }

    .stat-label {
      font-size: 0.75rem;
      color: var(--text-muted);
    }

    /* Right Auth Card */
    .auth-card {
      padding: 3rem 2.5rem;
      display: flex;
      flex-direction: column;
    }

    .auth-header {
      margin-bottom: 1.75rem;
    }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      margin-bottom: 1rem;
    }

    .logo-icon {
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, #6C63FF, #43D9AD);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }

    .logo-text {
      font-family: 'Outfit', sans-serif;
      font-size: 1.25rem;
      font-weight: 800;
      color: var(--text-primary);
    }

    .logo-highlight {
      color: #6C63FF;
    }

    .auth-heading {
      font-family: 'Outfit', sans-serif;
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--text-primary);
      margin-bottom: 0.25rem;
    }

    .auth-subheading {
      font-size: 0.875rem;
      color: var(--text-secondary);
    }

    /* Tabs */
    .auth-tabs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      background: rgba(15, 23, 42, 0.6);
      padding: 0.25rem;
      border-radius: var(--radius-sm);
      border: 1px solid rgba(255, 255, 255, 0.08);
      margin-bottom: 1.5rem;
    }

    .tab-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.625rem;
      border: none;
      background: transparent;
      color: var(--text-secondary);
      font-weight: 600;
      font-size: 0.875rem;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .tab-btn.active {
      background: var(--primary);
      color: white;
      box-shadow: 0 4px 12px rgba(108, 99, 255, 0.3);
    }

    /* Form controls */
    .auth-form {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-label {
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 0.4rem;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 1rem;
      color: var(--text-muted);
      font-size: 1.2rem;
      pointer-events: none;
    }

    .form-input {
      width: 100%;
      padding: 0.75rem 1rem 0.75rem 2.75rem;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: var(--radius-sm);
      color: var(--text-primary);
      font-size: 0.9rem;
      transition: all 0.2s ease;
    }

    .form-input:focus {
      outline: none;
      border-color: #6C63FF;
      box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
    }

    .btn-toggle-eye {
      position: absolute;
      right: 0.75rem;
      background: transparent;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      display: flex;
      align-items: center;
      padding: 0.25rem;
    }

    .btn-toggle-eye:hover {
      color: var(--text-primary);
    }

    /* Role Cards */
    .role-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.5rem;
    }

    .role-card {
      padding: 0.75rem 0.5rem;
      background: rgba(15, 23, 42, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: var(--radius-sm);
      text-align: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .role-card:hover {
      border-color: rgba(255, 255, 255, 0.2);
    }

    .role-card.selected {
      background: rgba(108, 99, 255, 0.15);
      border-color: #6C63FF;
    }

    .role-icon {
      font-size: 1.5rem;
      margin-bottom: 0.25rem;
    }

    .role-name {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--text-primary);
    }

    .role-desc {
      font-size: 0.65rem;
      color: var(--text-muted);
    }

    /* Demo Buttons */
    .demo-divider {
      margin: 1.75rem 0 1rem;
      text-align: center;
      position: relative;
    }

    .demo-divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background: rgba(255, 255, 255, 0.1);
    }

    .demo-divider span {
      position: relative;
      background: #1e293b;
      padding: 0 0.75rem;
      font-size: 0.75rem;
      color: var(--text-muted);
    }

    .demo-buttons {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.5rem;
    }

    .btn-demo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.375rem;
      padding: 0.5rem;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: var(--radius-sm);
      color: var(--text-secondary);
      font-size: 0.75rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-demo:hover {
      background: rgba(255, 255, 255, 0.08);
      color: var(--text-primary);
      border-color: rgba(255, 255, 255, 0.2);
    }

    .spin {
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  `]
})
export class LoginComponent {
  activeTab: 'login' | 'register' = 'login';
  showPassword = false;
  loading = false;
  errorMessage = '';
  successMessage = '';

  // Form Fields
  email = '';
  password = '';
  name = '';
  selectedRole: 'user' | 'atelier' | 'association' = 'user';

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  onLogin() {
    if (!this.email || !this.password) {
      this.errorMessage = 'Veuillez remplir tous les champs.';
      return;
    }

    this.loading = true;
    this.errorMessage = '';

    this.authService.login({ email: this.email, password: this.password }).subscribe({
      next: (res) => {
        this.loading = false;
        if (res.success) {
          this.successMessage = 'Connexion réussie ! Redirection...';
          setTimeout(() => {
            this.router.navigate(['/dashboard']);
          }, 600);
        } else {
          this.errorMessage = res.message || 'Identifiants invalides.';
        }
      },
      error: () => {
        this.loading = false;
        // Fallback demo auto log
        this.authService.loginDemo('user');
        this.router.navigate(['/dashboard']);
      }
    });
  }

  onRegister() {
    if (!this.name || !this.email || !this.password) {
      this.errorMessage = 'Veuillez remplir tous les champs d\'inscription.';
      return;
    }

    this.loading = true;
    this.errorMessage = '';

    this.authService.register({
      name: this.name,
      email: this.email,
      password: this.password,
      role: this.selectedRole
    }).subscribe({
      next: (res) => {
        this.loading = false;
        this.successMessage = 'Compte créé avec succès ! Redirection...';
        setTimeout(() => {
          this.router.navigate(['/dashboard']);
        }, 600);
      },
      error: () => {
        this.loading = false;
        this.authService.loginDemo(this.selectedRole);
        this.router.navigate(['/dashboard']);
      }
    });
  }

  quickDemo(role: 'user' | 'atelier' | 'association') {
    this.authService.loginDemo(role);
    this.successMessage = `Connecté en tant que ${role.toUpperCase()} (Mode Démo)`;
    setTimeout(() => {
      this.router.navigate(['/dashboard']);
    }, 400);
  }
}
