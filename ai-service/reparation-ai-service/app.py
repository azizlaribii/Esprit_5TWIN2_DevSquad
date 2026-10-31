"""
Service IA Python / Flask — Réparation Intelligente
=====================================================

Détection et classification des altérations textiles par modèle CLIP Zero-Shot
et localisation automatique de la zone endommagée (Bounding Box & Zone anatomique).
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
    print("Utilisation de CLIP Zero-Shot optimisé pour les altérations textiles.")
    from transformers import CLIPProcessor, CLIPModel

    CLIP_MODEL_NAME = "openai/clip-vit-base-patch32"
    clip_model = CLIPModel.from_pretrained(CLIP_MODEL_NAME)
    clip_processor = CLIPProcessor.from_pretrained(CLIP_MODEL_NAME)
    clip_model.eval()

    # Prompts hautement précis pour éviter les confusions (ex: boutons/fermetures sur jeans)
    CANDIDATE_LABELS = {
        "non_vetement": "a photo of a face, animal, room or landscape without clothing",
        "conforme": "a photo of flawless clothing in good condition without any damage, stain or hole",
        "Trou": "a visible hole in jeans, pants or fabric, punctured hole in clothes",
        "Déchirure": "a photo of a shirt with a ripped open seam, torn fabric, rip or tear",
        "Tache": "a photo of a clothing item with a liquid stain, spilled coffee stain, food stain, colored dirty spot",
        "Fermeture cassée": "a broken zipper with missing teeth, separated zipper track, detached zipper slider",
        "Bouton manquant": "a missing shirt button with empty buttonhole or loose thread",
        "Usure": "heavily frayed, worn-out threadbare faded fabric",
    }
    LABEL_KEYS = list(CANDIDATE_LABELS.keys())
    LABEL_TEXTS = list(CANDIDATE_LABELS.values())

    MODE = "clip"

print("Service de Réparation IA prêt.")


def find_defect_bbox_and_location(image, defect_type):
    """
    Localise spatialement la zone du défaut en détectant le contraste et l'écart
    par rapport à la couleur dominante du vêtement.
    """
    if defect_type in ['non_vetement', 'conforme']:
        return None, None

    img_small = image.resize((100, 100)).convert('RGB')
    arr = np.array(img_small, dtype=float)
    median_color = np.median(arr, axis=(0, 1))
    diff = np.linalg.norm(arr - median_color, axis=2)

    win = 22
    max_val = -1
    best_r, best_c = 35, 35
    for r in range(5, 100 - win - 5, 4):
        for c in range(5, 100 - win - 5, 4):
            val = np.mean(diff[r:r+win, c:c+win])
            if val > max_val:
                max_val = val
                best_r, best_c = r, c

    bbox = {
        'x': round(float(best_c), 1),
        'y': round(float(best_r), 1),
        'width': round(win * 1.25, 1),
        'height': round(win * 1.25, 1),
    }

    y_center = best_r + win / 2
    x_center = best_c + win / 2

    if y_center < 30:
        location = "Col & Encolure" if abs(x_center - 50) < 20 else "Épaule"
    elif y_center < 58:
        if abs(x_center - 50) > 28:
            location = "Manche"
        else:
            location = "Poitrine & Buste"
    elif y_center < 78:
        if defect_type == 'Trou':
            location = "Genou / Cuisse"
        else:
            location = "Poche & Hanche"
    else:
        location = "Ourlet & Finition"

    return bbox, location


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
        bbox, location = find_defect_bbox_and_location(image, best_label)

        return jsonify({
            "label": best_label,
            "confidence": round(best_score, 4),
            "all_scores": all_scores,
            "bbox": bbox,
            "location": location,
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

    bbox, location = find_defect_bbox_and_location(image, best_label)

    return jsonify({
        "label": best_label,
        "confidence": round(best_score, 4),
        "all_scores": {k: round(v, 4) for k, v in scores.items()},
        "bbox": bbox,
        "location": location,
        "mode": "clip",
    })


@app.route("/health", methods=["GET"])
def health():
    info = {"status": "ok", "mode": MODE}
    return jsonify(info)


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=False)
