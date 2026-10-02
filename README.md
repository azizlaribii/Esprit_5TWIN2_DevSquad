# TextileCycle — Réparation Intelligente (Blade + IA)

Ce projet fonctionne désormais entièrement avec des **vues Blade (Laravel)** et un **service IA (Flask / PyTorch)**.

---

## 📁 Structure du projet

- **`backend/reparation-backend`** : Application web principale Laravel 12 avec templates Blade, formulaires d'analyse, gestion des ateliers et suivi des demandes.
- **`ai-service/reparation-ai-service`** : Micro-service Python / Flask analysant les images de vêtements pour classifier les défauts (trou, déchirure, tache, fermeture cassée, bouton manquant, usure).

---

## 🚀 Comment lancer le projet

### 1. Démarrer le Backend (Laravel Blade)
Dans un terminal :
```powershell
cd backend\reparation-backend
php artisan serve
```
L'application est disponible sur : **http://127.0.0.1:8000**

### 2. Démarrer le Service IA (Python Flask)
Dans un deuxième terminal :
```powershell
cd ai-service\reparation-ai-service
python app.py
```
Le service IA tourne sur : **http://127.0.0.1:5000**

---

## 🌐 Pages principales (Blade)

- **Accueil & Analyse IA** : [http://127.0.0.1:8000/](http://127.0.0.1:8000/)
- **Mes demandes** : [http://127.0.0.1:8000/reparations](http://127.0.0.1:8000/reparations)
- **Détail d'une demande** : `http://127.0.0.1:8000/reparations/{id}`
- **Ateliers partenaires** : `http://127.0.0.1:8000/reparations/{id}/ateliers`
