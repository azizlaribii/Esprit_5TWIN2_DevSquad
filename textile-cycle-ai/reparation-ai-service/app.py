"""
Service IA Python / Flask — Réparation Intelligente TextileCycle
================================================================
Version 2 : Feature engineering amélioré pour les taches sur vêtements clairs.
"""

import os
import pickle
import numpy as np
from PIL import Image
from flask import Flask, request, jsonify
from flask_cors import CORS
from scipy.ndimage import sobel, laplace, gaussian_filter
from sklearn.ensemble import ExtraTreesClassifier, RandomForestClassifier
from sklearn.preprocessing import StandardScaler
from sklearn.pipeline import Pipeline

app = Flask(__name__)
CORS(app)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "model.pkl")
DATASET_DIR = os.path.join(BASE_DIR, "dataset")

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


def extract_image_features(image_or_stream):
    if isinstance(image_or_stream, str):
        img = Image.open(image_or_stream).convert("RGB")
    elif hasattr(image_or_stream, "read"):
        img = Image.open(image_or_stream).convert("RGB")
    else:
        img = image_or_stream.convert("RGB")

    img_std = img.resize((224, 224), Image.Resampling.BILINEAR)
    arr = np.asarray(img_std, dtype=np.float32)
    r, g, b = arr[:, :, 0], arr[:, :, 1], arr[:, :, 2]
    gray = 0.299 * r + 0.587 * g + 0.114 * b

    # ── 1. Basic luminance stats ──────────────────────────────────────────────
    mean_lum = float(np.mean(gray))
    std_lum  = float(np.std(gray))
    lum_range = float(np.percentile(gray, 90) - np.percentile(gray, 10))

    # ── 2. SATURATION ANALYSIS (key for stain detection on light garments) ───
    max_c = np.maximum(np.maximum(r, g), b)
    min_c = np.minimum(np.minimum(r, g), b)
    sat   = np.where(max_c > 0, (max_c - min_c) / (max_c + 1e-5), 0)

    mean_sat     = float(np.mean(sat))
    std_sat      = float(np.std(sat))
    # ★ These four features are crucial for stain-on-white detection
    max_sat      = float(np.max(sat))          # maximum saturation found anywhere
    p95_sat      = float(np.percentile(sat, 95))  # 95th percentile – ignores noise
    high_sat_pct = float(np.mean(sat > 0.25))  # % of pixels with noticeable color
    very_high_sat= float(np.mean(sat > 0.45))  # % of pixels with strong color

    # ── 3. Color outlier map – pixels far from median fabric color ───────────
    median_r = float(np.median(r))
    median_g = float(np.median(g))
    median_b = float(np.median(b))
    color_dist = np.sqrt(
        (r - median_r) ** 2 +
        (g - median_g) ** 2 +
        (b - median_b) ** 2
    )
    outlier_mask = color_dist > 30          # pixels clearly different from fabric
    outlier_pct  = float(np.mean(outlier_mask))   # fraction of outlier pixels
    max_color_dist = float(np.max(color_dist))
    p95_color_dist = float(np.percentile(color_dist, 95))

    # ── 4. Hue-based signature (R-dominant = warm colors = stains/rust/mud) ──
    r_dominance = float(np.mean(np.maximum(0, r - np.maximum(g, b))))   # R above others
    g_dominance = float(np.mean(np.maximum(0, g - np.maximum(r, b))))   # G above others
    b_dominance = float(np.mean(np.maximum(0, b - np.maximum(r, g))))   # B above others
    warm_color_pct = float(np.mean((r > g + 20) & (r > b + 20)))        # warm pixel %

    # ── 5. Color histograms (8 bins per channel) ─────────────────────────────
    hist_r, _ = np.histogram(r, bins=8, range=(0, 256), density=True)
    hist_g, _ = np.histogram(g, bins=8, range=(0, 256), density=True)
    hist_b, _ = np.histogram(b, bins=8, range=(0, 256), density=True)
    # Saturation histogram (4 bins)
    hist_s, _ = np.histogram(sat, bins=4, range=(0, 1), density=True)

    # ── 6. Gradient / Edge analysis ──────────────────────────────────────────
    gx = sobel(gray, axis=1)
    gy = sobel(gray, axis=0)
    grad_mag   = np.hypot(gx, gy)
    mean_grad  = float(np.mean(grad_mag))
    std_grad   = float(np.std(grad_mag))
    max_grad   = float(np.percentile(grad_mag, 99))
    edge_density = float(np.mean(grad_mag > (mean_grad + 1.5 * std_grad)))
    lap_var    = float(np.var(laplace(gray)))

    # ── 7. Grid anomaly analysis (14×14) ─────────────────────────────────────
    grid_size = 14
    cell_h, cell_w = 224 // grid_size, 224 // grid_size
    grid_means   = np.zeros((grid_size, grid_size))
    grid_stds    = np.zeros((grid_size, grid_size))
    grid_grads   = np.zeros((grid_size, grid_size))
    grid_sat_max = np.zeros((grid_size, grid_size))   # ★ NEW: peak saturation per cell
    grid_outlier = np.zeros((grid_size, grid_size))   # ★ NEW: color outlier per cell
    grid_darks   = np.zeros((grid_size, grid_size))

    for i in range(grid_size):
        for j in range(grid_size):
            patch      = gray [i*cell_h:(i+1)*cell_h, j*cell_w:(j+1)*cell_w]
            patch_g    = grad_mag[i*cell_h:(i+1)*cell_h, j*cell_w:(j+1)*cell_w]
            patch_sat  = sat  [i*cell_h:(i+1)*cell_h, j*cell_w:(j+1)*cell_w]
            patch_dist = color_dist[i*cell_h:(i+1)*cell_h, j*cell_w:(j+1)*cell_w]

            grid_means  [i, j] = np.mean(patch)
            grid_stds   [i, j] = np.std(patch)
            grid_grads  [i, j] = np.mean(patch_g)
            grid_sat_max[i, j] = np.percentile(patch_sat, 90)   # ★
            grid_outlier[i, j] = np.mean(patch_dist)            # ★
            grid_darks  [i, j] = np.mean(patch < (mean_lum - 1.2 * std_lum))

    med_lum  = np.median(grid_means)
    lum_diff = np.abs(grid_means - med_lum)

    max_lum_diff   = float(np.max(lum_diff))
    mean_lum_diff  = float(np.mean(lum_diff))
    max_patch_grad = float(np.max(grid_grads))
    max_patch_dark = float(np.max(grid_darks))
    max_patch_std  = float(np.max(grid_stds))
    max_patch_sat  = float(np.max(grid_sat_max))    # ★ peak local saturation
    max_patch_out  = float(np.max(grid_outlier))    # ★ peak local color outlier

    # ── 8. Combined anomaly map for bbox ─────────────────────────────────────
    anomaly_map = (
        lum_diff     / (std_lum  + 1e-5) * 1.2 +
        grid_grads   / (mean_grad + 1e-5) * 0.8 +
        grid_darks   * 2.5 +
        grid_sat_max * 3.0 +            # ★ saturation heavily weighted
        grid_outlier / (max_color_dist + 1e-5) * 2.5   # ★ color outlier weighted
    )
    anomaly_map[0, :]  *= 0.6
    anomaly_map[-1, :] *= 0.6
    anomaly_map[:, 0]  *= 0.6
    anomaly_map[:, -1] *= 0.6

    peak_idx   = np.unravel_index(np.argmax(anomaly_map), anomaly_map.shape)
    peak_y, peak_x = int(peak_idx[0]), int(peak_idx[1])
    peak_score = float(anomaly_map[peak_y, peak_x])

    high_anom  = anomaly_map > (peak_score * 0.6)
    anom_rows, anom_cols = np.where(high_anom)
    if len(anom_rows) > 0:
        anom_h  = max(1, int(np.max(anom_rows)) - int(np.min(anom_rows)) + 1)
        anom_w  = max(1, int(np.max(anom_cols)) - int(np.min(anom_cols)) + 1)
        anom_aspect = float(max(anom_h, anom_w) / min(anom_h, anom_w))
        anom_area   = float(len(anom_rows) / (grid_size * grid_size))
    else:
        anom_h = anom_w = 2
        anom_aspect = 1.0
        anom_area   = 0.01

    # ── 9. Assemble feature vector ────────────────────────────────────────────
    features = [
        # Luminance
        mean_lum, std_lum, lum_range,
        # Saturation (7 features – key for stains)
        mean_sat, std_sat, max_sat, p95_sat, high_sat_pct, very_high_sat,
        # Color outliers (4 features)
        outlier_pct, max_color_dist, p95_color_dist,
        # Hue dominance (4 features)
        r_dominance, g_dominance, b_dominance, warm_color_pct,
        # Gradients (5)
        mean_grad, std_grad, max_grad, edge_density, lap_var,
        # Grid anomalies (9)
        max_lum_diff, mean_lum_diff, max_patch_grad, max_patch_dark,
        max_patch_std, peak_score, max_patch_sat, max_patch_out,
        anom_aspect, anom_area,
    ]
    features.extend(hist_r.tolist())   # 8
    features.extend(hist_g.tolist())   # 8
    features.extend(hist_b.tolist())   # 8
    features.extend(hist_s.tolist())   # 4
    # Total: 30 + 28 = 58 features

    # ── 10. Bounding box computation ──────────────────────────────────────────
    norm_x = round((peak_x / grid_size) * 100, 1)
    norm_y = round((peak_y / grid_size) * 100, 1)
    bbox_w = min(40.0, max(12.0, round((anom_w / grid_size) * 100 * 1.3, 1)))
    bbox_h = min(40.0, max(12.0, round((anom_h / grid_size) * 100 * 1.3, 1)))
    norm_x = max(2.0, min(95.0 - bbox_w, norm_x - bbox_w / 4))
    norm_y = max(2.0, min(95.0 - bbox_h, norm_y - bbox_h / 4))

    cx = norm_x + bbox_w / 2
    cy = norm_y + bbox_h / 2
    if cy < 22:
        location = "Col"
    elif cx < 22 or cx > 78:
        location = "Manche"
    elif cy < 35:
        location = "Épaule"
    elif cy < 58:
        location = "Poitrine" if abs(cx - 50) < 20 else "Poche"
    elif cy < 75:
        location = "Ventre"
    else:
        location = "Genou" if abs(cx - 50) > 15 else "Ourlet"

    bbox = {"x": round(norm_x, 1), "y": round(norm_y, 1),
            "width": round(bbox_w, 1), "height": round(bbox_h, 1)}

    return np.array(features, dtype=np.float32), bbox, location


def train_model():
    print("[AI Service] Entraînement du modèle depuis le dataset...")
    X, y = [], []
    for folder, label in FOLDER_TO_LABEL.items():
        folder_path = os.path.join(DATASET_DIR, folder)
        if not os.path.isdir(folder_path):
            continue
        files = [
            os.path.join(folder_path, f)
            for f in os.listdir(folder_path)
            if f.lower().endswith(('.jpg', '.jpeg', '.png', '.webp'))
        ]
        for f in files:
            try:
                feat, _, _ = extract_image_features(f)
                X.append(feat)
                y.append(label)
            except Exception as e:
                print(f"  Skip {f}: {e}")

    if len(X) < 5:
        raise ValueError(f"Pas assez d'images dans {DATASET_DIR}.")

    X = np.array(X)
    y = np.array(y)
    print(f"  {len(X)} samples, {X.shape[1]} features, {len(np.unique(y))} classes")

    clf = Pipeline([
        ('scaler', StandardScaler()),
        ('rf', ExtraTreesClassifier(
            n_estimators=200,
            max_depth=None,
            min_samples_leaf=1,
            random_state=42,
            class_weight='balanced',     # handle imbalanced classes
        ))
    ])
    clf.fit(X, y)

    model_data = {"pipeline": clf, "classes": clf.classes_.tolist()}
    with open(MODEL_PATH, "wb") as f:
        pickle.dump(model_data, f)

    print(f"[AI Service] model.pkl enregistré ({len(clf.classes_)} classes).")
    return clf


# ── Chargement / Entraînement du modèle ──────────────────────────────────────
_need_retrain = True
if os.path.exists(MODEL_PATH):
    try:
        with open(MODEL_PATH, "rb") as f:
            saved = pickle.load(f)
        # Quick sanity-check: ensure saved model was trained with current feature count
        sample_feat, _, _ = extract_image_features(
            next(iter([
                os.path.join(DATASET_DIR, d, f)
                for d in os.listdir(DATASET_DIR)
                if os.path.isdir(os.path.join(DATASET_DIR, d))
                for f in os.listdir(os.path.join(DATASET_DIR, d))
                if f.lower().endswith(('.jpg', '.jpeg', '.png'))
            ]))
        )
        n_features_expected = saved["pipeline"].n_features_in_
        if len(sample_feat) == n_features_expected:
            pipeline = saved["pipeline"]
            classes  = saved["classes"]
            _need_retrain = False
            print(f"[AI Service] Modèle chargé ({len(classes)} classes, {n_features_expected} features).")
    except Exception as e:
        print(f"[AI Service] model.pkl incompatible ({e}), ré-entraînement...")

if _need_retrain:
    pipeline = train_model()
    classes  = pipeline.classes_.tolist()


# ── Routes Flask ──────────────────────────────────────────────────────────────
@app.route("/analyze", methods=["POST"])
def analyze():
    if "photo" not in request.files:
        return jsonify({"error": "Aucun fichier 'photo' reçu."}), 400

    file = request.files["photo"]
    try:
        feat, bbox, location = extract_image_features(file.stream)
    except Exception as e:
        return jsonify({"error": f"Image illisible : {e}"}), 400

    x     = feat.reshape(1, -1)
    probs = pipeline.predict_proba(x)[0]
    best_idx   = int(np.argmax(probs))
    best_label = str(pipeline.classes_[best_idx])
    best_score = float(probs[best_idx])

    all_scores = {
        str(pipeline.classes_[i]): round(float(probs[i]), 4)
        for i in range(len(probs))
    }

    if best_label in ["conforme", "non_vetement"]:
        bbox     = None
        location = None

    return jsonify({
        "label":      best_label,
        "confidence": round(best_score, 4),
        "location":   location,
        "bbox":       bbox,
        "all_scores": all_scores,
        "mode":       "trained_cv_v2",
    })


@app.route("/health", methods=["GET"])
def health():
    return jsonify({
        "status":  "ok",
        "service": "reparation-ai-service",
        "version": "2.0",
        "classes": classes,
    })


if __name__ == "__main__":
    print("Démarrage du service IA sur http://127.0.0.1:5000 ...")
    app.run(host="127.0.0.1", port=5000, debug=False)
