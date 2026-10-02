# reparation-ai-service — Service IA Python / Flask

Micro-service Python qui analyse les photos de vêtements. Deux modes,
choisis **automatiquement** selon ce qui est disponible :

## Mode 1 — Modèle entraîné sur VOS photos (recommandé, votre demande)

C'est l'approche classique d'apprentissage supervisé que vous décriviez :
vous donnez des exemples étiquetés, le modèle apprend les différences.

### Étape 1 — Rassembler des photos

Dans le dossier `dataset/`, chaque sous-dossier est déjà créé pour vous.
Remplacez le fichier `PLACEZ_VOS_PHOTOS_ICI.txt` par vos vraies photos
(`.jpg`, `.jpeg`, `.png`, `.webp`) :

```
dataset/
  normal/              <- vêtements en bon état (10-15+ photos idéalement)
  trou/                <- vêtements avec un trou
  dechirure/            <- vêtements déchirés
  tache/                <- vêtements tachés
  fermeture_cassee/     <- fermetures éclair cassées
  bouton_manquant/      <- boutons manquants
  usure/                <- vêtements usés/élimés
  non_vetement/         <- photos qui ne montrent PAS de vêtement
```

Vous n'êtes pas obligé de remplir toutes les catégories — entraînez avec
celles pour lesquelles vous avez des photos (minimum 2 catégories, 4 photos
au total, mais visez plutôt 10-30 photos par catégorie pour un résultat
correct).

💡 Astuce : prenez plusieurs photos de la même pièce sous des angles/
éclairages différents pour varier les exemples sans avoir besoin de 30
vêtements différents.

### Étape 2 — Entraîner

```powershell
python train.py
```

Ça crée un fichier `model.pkl`. Ça prend quelques secondes à quelques
minutes selon le nombre de photos (pas besoin de carte graphique).

### Étape 3 — Lancer le service

```powershell
python app.py
```

Il détecte automatiquement `model.pkl` et l'utilise.

## Mode 2 — CLIP zero-shot (repli automatique, sans entraînement)

Si vous lancez `python app.py` **sans avoir créé `model.pkl`** (donc sans
avoir lancé `train.py`), le service utilise à la place CLIP, un modèle
pré-entraîné par OpenAI, gratuit, qui compare la photo à des descriptions
textuelles. Moins précis pour cette tâche spécifique, mais fonctionne tout
de suite sans aucune photo de votre part.

## Installation (commune aux deux modes)

**Prérequis : Python 3.9+.**
```powershell
pip install -r requirements.txt
```
⚠️ Télécharge PyTorch (~700 Mo), patientez sans interrompre.

## Connecter Laravel à ce service

Dans `reparation-backend/.env` :
```env
DEFECT_AI_DRIVER=flask
FLASK_AI_URL=http://127.0.0.1:5000
```
Puis `php artisan config:clear` et redémarrez `php artisan serve`.

## Vérifier quel mode est actif

```powershell
curl http://127.0.0.1:5000/health
```
Répond `{"status": "ok", "mode": "trained", "categories": [...]}` ou
`{"status": "ok", "mode": "clip"}`.

## Ré-entraîner après avoir ajouté des photos

Ajoutez simplement de nouvelles photos dans `dataset/`, relancez
`python train.py` (ça écrase `model.pkl`), puis redémarrez `python app.py`.

## Limites à connaître

- Avec peu de photos (ex: 10 par catégorie), le modèle peut se tromper sur
  des cas très différents de vos exemples. Plus vous ajoutez de photos
  variées (angles, éclairages, couleurs de vêtements), plus il devient
  fiable — c'est le principe même de l'apprentissage supervisé.
- Ce n'est toujours pas de la localisation précise du défaut au pixel près
  (le cadre affiché dans l'app reste une estimation basée sur l'analyse de
  contraste, pas sur ce que le classifieur a "vu").
