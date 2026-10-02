"""
Service IA Python / Flask — Réparation Intelligente
=====================================================

Deux modes possibles, choisis automatiquement au démarrage :

1. MODÈLE ENTRAÎNÉ (prioritaire, si "model.pkl" existe) :
   Un classifieur entraîné sur VOS photos via train.py (voir ce fichier).
   C'est de l'apprentissage supervisé classique : vous donnez des exemples
   étiquetés, le modèle apprend à distinguer les catégories.

2. CLIP ZERO-SHOT (repli automatique si aucun modèle entraîné n'existe) :
   Modèle de vision-langage pré-entraîné par OpenAI (gratuit, open-source),
   compare la photo à des descriptions textuelles. Ne nécessite aucun
   exemple de votre part, mais moins précis pour cette tâche spécifique.

Installation (une seule fois) :
    pip install -r requirements.txt

Lancement :
    python app.py
    -> le service tourne sur http://127.0.0.1:5000
"""

import os
import pickle
import numpy as np
from flask import Flask, request, jsonify
from PIL import Image
import torch

app = Flask(__name__)

MODEL_PATH = "model.pkl"
MODE = None  # "trained" ou "clip"

# ---------------------------------------------------------------------------
# Chargement du bon mode au démarrage
# ---------------------------------------------------------------------------

if os.path.exists(MODEL_PATH):
    print(f"'{MODEL_PATH}' trouvé -> utilisation du modèle entraîné sur vos photos.")
    from torchvision.models import mobilenet_v2, MobileNet_V2_Weights

    with open(MODEL_PATH, "rb") as f:
        saved = pickle.load(f)
    classifier = saved["classifier"]
    categories = saved["categories"]

    weights = MobileNet_V2_Weights.DEFAULT
    feature_model = mobilenet_v2(weights=weights)
    feature_model.classifier = torch.nn.Identity()
    feature_model.eval()
    feature_preprocess = weights.transforms()

    MODE = "trained"
    print(f"Catégories apprises : {categories}")

else:
    print("Aucun 'model.pkl' trouvé -> utilisation de CLIP (zero-shot, sans "
          "entraînement). Pour entraîner votre propre modèle sur vos photos, "
          "voir train.py.")
    from transformers import CLIPProcessor, CLIPModel

    CLIP_MODEL_NAME = "openai/clip-vit-base-patch32"
    clip_model = CLIPModel.from_pretrained(CLIP_MODEL_NAME)
    clip_processor = CLIPProcessor.from_pretrained(CLIP_MODEL_NAME)
    clip_model.eval()

    CANDIDATE_LABELS = {
        "non_vetement": "a photo that does not show any clothing item",
        "conforme": "a photo of a clothing item in good condition, with no damage",
        "Trou": "a photo of a clothing item with a visible hole",
        "Déchirure": "a photo of a clothing item with a visible tear or rip",
        "Tache": "a photo of a clothing item with a visible stain",
        "Fermeture cassée": "a photo of a clothing item with a broken zipper",
        "Bouton manquant": "a photo of a clothing item with a missing button",
        "Usure": "a photo of a clothing item that looks worn out or frayed",
    }
    LABEL_KEYS = list(CANDIDATE_LABELS.keys())
    LABEL_TEXTS = list(CANDIDATE_LABELS.values())

    MODE = "clip"

print("Service prêt.")


# ---------------------------------------------------------------------------
# Routes
# ---------------------------------------------------------------------------

@app.route("/analyze", methods=["POST"])
def analyze():
    if "photo" not in request.files:
        return jsonify({"error": "Aucun fichier 'photo' reçu."}), 400

    file = request.files["photo"]

    try:
        image = Image.open(file.stream).convert("RGB")
    except Exception as e:
        return jsonify({"error": f"Image illisible : {e}"}), 400

    if MODE == "trained":
        x = feature_preprocess(image).unsqueeze(0)
        with torch.no_grad():
            feat = feature_model(x).squeeze().numpy().reshape(1, -1)

        probs = classifier.predict_proba(feat)[0]
        classes = classifier.classes_
        best_idx = int(np.argmax(probs))
        best_label = classes[best_idx]
        best_score = float(probs[best_idx])

        all_scores = {classes[i]: round(float(probs[i]), 4) for i in range(len(classes))}

        return jsonify({
            "label": best_label,
            "confidence": round(best_score, 4),
            "all_scores": all_scores,
            "mode": "trained",
        })

    # Mode CLIP (zero-shot)
    inputs = clip_processor(
        text=LABEL_TEXTS,
        images=image,
        return_tensors="pt",
        padding=True,
    )
    with torch.no_grad():
        outputs = clip_model(**inputs)
        probs = outputs.logits_per_image.softmax(dim=1)[0]

    scores = {LABEL_KEYS[i]: float(probs[i]) for i in range(len(LABEL_KEYS))}
    best_label = max(scores, key=scores.get)
    best_score = scores[best_label]

    return jsonify({
        "label": best_label,
        "confidence": round(best_score, 4),
        "all_scores": {k: round(v, 4) for k, v in scores.items()},
        "mode": "clip",
    })


@app.route("/health", methods=["GET"])
def health():
    info = {"status": "ok", "mode": MODE}
    if MODE == "trained":
        info["categories"] = categories
    return jsonify(info)


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=False)
