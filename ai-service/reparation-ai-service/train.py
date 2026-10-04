"""
train.py — Entraîne un classifieur de défauts textiles à partir de VOS photos.

Principe (transfer learning) :
    1. On utilise MobileNetV2, un réseau de neurones déjà entraîné sur des
       millions de photos (ImageNet), juste pour transformer chaque image en
       un vecteur de nombres qui résume ses formes/textures/couleurs.
    2. On entraîne ensuite un petit classifieur (régression logistique) sur
       ces vecteurs, avec VOS photos et VOS étiquettes.

Contrairement à un entraînement "from scratch" (qui demanderait des dizaines
de milliers de photos et plusieurs heures de calcul), cette méthode
fonctionne raisonnablement avec seulement 15-30 photos par catégorie et
s'entraîne en quelques secondes sur un simple PC (pas besoin de carte
graphique).

------------------------------------------------------------------------
COMMENT PRÉPARER VOS PHOTOS
------------------------------------------------------------------------
Dans le dossier "dataset/", créez un sous-dossier par catégorie et mettez-y
vos photos (au moins 10-15 par catégorie, idéalement plus) :

    dataset/
      normal/              <- photos de vêtements en bon état
      trou/                <- photos avec un trou
      dechirure/            <- photos avec une déchirure
      tache/                <- photos avec une tache
      fermeture_cassee/     <- photos avec une fermeture éclair cassée
      bouton_manquant/      <- photos avec un bouton manquant
      usure/                <- photos usées/élimées
      non_vetement/         <- photos qui ne montrent PAS de vêtement

Vous n'êtes pas obligé de remplir toutes les catégories : entraînez avec
celles pour lesquelles vous avez des photos (minimum 2 catégories).

------------------------------------------------------------------------
LANCEMENT
------------------------------------------------------------------------
    python train.py

Cela crée un fichier "model.pkl" que le service Flask (app.py) charge
automatiquement au démarrage pour classer les nouvelles photos.
"""

import os
import sys
import pickle
import numpy as np
from PIL import Image
import torch
from torchvision.models import mobilenet_v2, MobileNet_V2_Weights

DATASET_DIR = "dataset"
MODEL_OUT = "model.pkl"

# Fait correspondre le nom de dossier (simple, sans accents) au libellé
# final utilisé par l'application (doit correspondre à DefectDetectionService.php).
FOLDER_TO_LABEL = {
    "normal": "conforme",
    "trou": "Trou",
    "dechirure": "Déchirure",
    "tache": "Tache",
    "fermeture_cassee": "Fermeture cassée",
    "bouton_manquant": "Bouton manquant",
    "usure": "Usure",
    "non_vetement": "non_vetement",
}


def load_feature_extractor():
    weights = MobileNet_V2_Weights.DEFAULT
    model = mobilenet_v2(weights=weights)
    model.classifier = torch.nn.Identity()  # on retire la dernière couche : on garde juste les "features"
    model.eval()
    preprocess = weights.transforms()
    return model, preprocess


def extract_features(model, preprocess, image_path):
    img = Image.open(image_path).convert("RGB")
    x = preprocess(img).unsqueeze(0)
    with torch.no_grad():
        feat = model(x)
    return feat.squeeze().numpy()


def main():
    if not os.path.isdir(DATASET_DIR):
        print(f"[Erreur] Dossier '{DATASET_DIR}/' introuvable.")
        print("Créez-le avec un sous-dossier par catégorie (voir l'en-tête de ce fichier).")
        sys.exit(1)

    folders = sorted([d for d in os.listdir(DATASET_DIR) if os.path.isdir(os.path.join(DATASET_DIR, d))])
    folders = [f for f in folders if any(
        name.lower().endswith(('.jpg', '.jpeg', '.png', '.webp'))
        for name in os.listdir(os.path.join(DATASET_DIR, f))
    )]

    if len(folders) < 2:
        print("[Erreur] Il faut au moins 2 catégories (dossiers non vides) dans 'dataset/'.")
        sys.exit(1)

    print(f"Catégories trouvées : {folders}\n")

    print("Chargement de MobileNetV2 (extracteur de features, ~14 Mo)...")
    model, preprocess = load_feature_extractor()
    print("OK.\n")

    X, y = [], []

    for folder in folders:
        label = FOLDER_TO_LABEL.get(folder, folder)
        path = os.path.join(DATASET_DIR, folder)
        images = [f for f in os.listdir(path) if f.lower().endswith(('.jpg', '.jpeg', '.png', '.webp'))]
        print(f"  {folder} -> \"{label}\" : {len(images)} photo(s)")

        for img_name in images:
            img_path = os.path.join(path, img_name)
            try:
                feat = extract_features(model, preprocess, img_path)
                X.append(feat)
                y.append(label)
            except Exception as e:
                print(f"    [Attention] Impossible de lire {img_name} : {e}")

    if len(X) < 4:
        print("\n[Erreur] Pas assez de photos valides au total pour entraîner "
              "(minimum recommandé : 10-15 par catégorie).")
        sys.exit(1)

    X = np.array(X)
    y = np.array(y)

    print(f"\nEntraînement du classifieur sur {len(X)} photos au total...")

    from sklearn.linear_model import LogisticRegression
    clf = LogisticRegression(max_iter=3000)
    clf.fit(X, y)

    train_accuracy = clf.score(X, y)
    print(f"Précision sur les photos d'entraînement : {train_accuracy * 100:.1f}%")
    print("(Une précision proche de 100% ici est normale mais ne garantit pas")
    print(" la même précision sur de nouvelles photos jamais vues — plus vous")
    print(" avez de photos variées, plus le modèle généralisera bien.)\n")

    with open(MODEL_OUT, "wb") as f:
        pickle.dump({"classifier": clf, "categories": sorted(set(y.tolist()))}, f)

    print(f"✅ Modèle entraîné et sauvegardé dans '{MODEL_OUT}'.")
    print("Relancez (ou lancez) 'python app.py' : il utilisera automatiquement ce modèle.")


if __name__ == "__main__":
    main()
