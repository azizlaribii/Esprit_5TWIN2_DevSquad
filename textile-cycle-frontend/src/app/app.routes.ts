import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: '',
    redirectTo: '/dashboard',
    pathMatch: 'full'
  },
  {
    path: 'login',
    loadComponent: () => import('./features/auth/login.component').then(m => m.LoginComponent),
    title: 'Connexion | TexTileCycle'
  },
  {
    path: 'dashboard',
    loadComponent: () => import('./features/dashboard/dashboard.component').then(m => m.DashboardComponent),
    title: 'Tableau de bord | TexTileCycle'
  },
  {
    path: 'statistiques',
    loadComponent: () => import('./features/statistiques/statistiques.component').then(m => m.StatistiquesComponent),
    title: 'Statistiques | TexTileCycle'
  },
  {
    path: 'predictions',
    loadComponent: () => import('./features/predictions/predictions.component').then(m => m.PredictionsComponent),
    title: 'Prédictions IA | TexTileCycle'
  },
  {
    path: 'depots',
    loadComponent: () => import('./features/stubs').then(m => m.DepotsComponent),
    title: 'Dépôts | TexTileCycle'
  },
  {
    path: 'reparations',
    loadComponent: () => import('./features/stubs').then(m => m.ReparationsComponent),
    title: 'Réparations | TexTileCycle'
  },
  {
    path: 'dons',
    loadComponent: () => import('./features/stubs').then(m => m.DonsComponent),
    title: 'Dons | TexTileCycle'
  },
  {
    path: 'transformations',
    loadComponent: () => import('./features/stubs').then(m => m.TransformationsComponent),
    title: 'Transformations | TexTileCycle'
  },
  {
    path: 'ateliers',
    loadComponent: () => import('./features/stubs').then(m => m.AteliersComponent),
    title: 'Ateliers | TexTileCycle'
  },
  {
    path: 'associations',
    loadComponent: () => import('./features/stubs').then(m => m.AssociationsComponent),
    title: 'Associations | TexTileCycle'
  },
  {
    path: 'impact',
    loadComponent: () => import('./features/stubs').then(m => m.ImpactComponent),
    title: 'Impact Écologique | TexTileCycle'
  },
  {
    path: '**',
    redirectTo: '/dashboard'
  }
];
