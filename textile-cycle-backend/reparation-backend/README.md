# reparation-backend — Application Laravel 12 (Blade + API)

Application web complète avec vues **Blade** et API pour le module **Réparation Intelligente**.

## 🚀 Démarrage

```bash
composer install
cp .env.example .env
php artisan key:generate

php artisan storage:link
php artisan migrate --seed
php artisan serve
```

L'application web est accessible directement sur **http://127.0.0.1:8000** :
- **Nouvelle analyse** : `http://127.0.0.1:8000/`
- **Mes demandes** : `http://127.0.0.1:8000/reparations`

## 📡 Endpoints

| Méthode | URL | Auth | Description |
|---|---|---|---|
| POST | `/api/register` | non | `{ name, email, password, password_confirmation }` → `{ user, token }` |
| POST | `/api/login` | non | `{ email, password }` → `{ user, token }` |
| POST | `/api/logout` | oui | Révoque le token courant |
| GET | `/api/me` | oui | Utilisateur connecté |
| GET | `/api/reparations` | oui | Liste des demandes de l'utilisateur |
| POST | `/api/reparations` | oui | `multipart/form-data`, champ `photo` → analyse IA + création |
| GET | `/api/reparations/{id}` | oui | Détail d'une demande (photo, analyse, atelier) |
| DELETE | `/api/reparations/{id}` | oui | Supprime une demande |
| GET | `/api/reparations/{id}/ateliers?lat=&lng=` | oui | Ateliers compatibles, triés par proximité si coordonnées fournies |
| POST | `/api/reparations/{id}/ateliers/choisir` | oui | `{ workshop_id }` → rattache l'atelier |

**Authentification** : après `/login` ou `/register`, envoyez le `token` reçu
dans l'en-tête de chaque requête protégée :
```
Authorization: Bearer <token>
```

## 🌍 CORS

`config/cors.php` autorise par défaut `http://localhost:4200` et
`http://127.0.0.1:4200` (ports par défaut d'Angular en développement — `ng
serve`). Si votre frontend tourne sur un autre port, ajoutez-le dans ce
fichier.

## 🤖 Mode IA (identique au module Blade)

Dans `.env` :
```env
DEFECT_AI_DRIVER=mock     # simulation, aucune clé requise (par défaut)
# ou
DEFECT_AI_DRIVER=openai
OPENAI_API_KEY=sk-votre-cle
```

## 🗄️ Base de données

SQLite par défaut (`database/database.sqlite`, créez-le avec
`New-Item database\database.sqlite -ItemType File` sous PowerShell ou
`touch database/database.sqlite` sous Mac/Linux, avant `php artisan migrate`).

Pour passer à MySQL (XAMPP), dans `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reparation_intelligente
DB_USERNAME=root
DB_PASSWORD=
```

## 📂 Fichiers ajoutés au squelette Laravel

```
app/Http/Controllers/Api/AuthController.php
app/Http/Controllers/Api/RepairController.php
app/Http/Controllers/Api/WorkshopController.php
app/Models/RepairRequest.php
app/Models/Workshop.php
app/Services/DefectDetectionService.php
config/cors.php
database/migrations/2025_01_01_000000_create_repair_requests_table.php
database/migrations/2025_01_01_000001_create_workshops_table.php
database/migrations/2025_01_01_000002_create_personal_access_tokens_table.php
database/seeders/WorkshopSeeder.php
routes/api.php
```
