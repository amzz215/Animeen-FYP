import re
import json
import argparse
import pandas as pd
import mysql.connector
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import MinMaxScaler
from sklearn.metrics.pairwise import cosine_similarity
from scipy.sparse import hstack

# -------------------------
# 1) DB config
# -------------------------
DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "",
    "database": "animeen_db"
}

TITLE_COL = "title"
GENRE_COL = "genres"
STUDIO_COL = "studios"
ID_COL = "id"
IMAGE_COL = "main_picture_url"

# -------------------------
# 2) Franchise key
# -------------------------
def franchise_key(title: str) -> str:
    STOP = {"the", "a", "an", "and", "of", "to", "in", "on", "for", "with", "no"}
    t = str(title).lower()
    t = re.sub(r"\(.*?\)|\[.*?\]|\{.*?\}", " ", t)
    t = re.sub(r"[^a-z0-9]+", " ", t)
    t = re.sub(r"\b(season|part|cour|movie|film|ova|ona|special|recap|tv)\b", " ", t)
    t = re.sub(r"\b\d+\b", " ", t)
    t = re.sub(r"\s+", " ", t).strip()
    toks = [w for w in t.split() if w and w not in STOP]
    return f"{toks[0]} {toks[1]}" if len(toks) >= 2 else (toks[0] if toks else "")

# -------------------------
# 3) Load from MySQL
# -------------------------
def load_anime_from_db() -> pd.DataFrame:
    conn = mysql.connector.connect(**DB_CONFIG)
    try:
        query = """
            SELECT
                id,
                title,
                main_picture_url,
                mean,
                rank,
                num_scoring_users,
                genres,
                studios
            FROM anime
        """
        df = pd.read_sql(query, conn)
        return df
    finally:
        conn.close()

# -------------------------
# 4) Build model
# -------------------------
def build_model():
    df = load_anime_from_db()

    # keep rows that have the fields needed by the recommender
    df = df.dropna(subset=["mean", "rank", "num_scoring_users", GENRE_COL, STUDIO_COL]).copy()
    df = df.reset_index(drop=True)

    # fill text cols just in case nothign in the csv file i extracted and that was put into my database
    df[TITLE_COL] = df[TITLE_COL].fillna("").astype(str)
    df[GENRE_COL] = df[GENRE_COL].fillna("").astype(str)
    df[STUDIO_COL] = df[STUDIO_COL].fillna("").astype(str)
    df[IMAGE_COL] = df[IMAGE_COL].fillna("").astype(str)

    df["franchise_key"] = df[TITLE_COL].apply(franchise_key)

    GENRE_WEIGHT = 0.7
    STUDIO_WEIGHT = 0.3

    tfidf_genre = TfidfVectorizer(token_pattern=r"[^,]+")
    tfidf_studio = TfidfVectorizer(token_pattern=r"[^,]+")

    G_genre = tfidf_genre.fit_transform(df[GENRE_COL]) * GENRE_WEIGHT
    G_studio = tfidf_studio.fit_transform(df[STUDIO_COL]) * STUDIO_WEIGHT

    num = df[["mean", "rank", "num_scoring_users"]].apply(pd.to_numeric, errors="coerce").fillna(0)
    num_scaled = MinMaxScaler().fit_transform(num)

    X = hstack([G_genre, G_studio, num_scaled]).tocsr()
    return df, X

# -------------------------
# 5) Genre filter helper
# -------------------------
def has_all_selected_genres(anime_genres: str, selected_genres):
    anime_parts = [g.strip().lower() for g in str(anime_genres).split(",") if g.strip()]
    anime_set = set(anime_parts)

    for genre in selected_genres:
        if genre.strip().lower() not in anime_set:
            return False

    return True

# -------------------------
# 6) Recommend
# -------------------------
def recommend(df, X, title: str, k: int = 200, selected_genres=None):
    if selected_genres is None:
        selected_genres = []

    # exact match first
    idxs = df.index[df[TITLE_COL].str.lower() == title.lower()]

    if len(idxs) == 0:
        # fallback suggestions
        partial = df[df[TITLE_COL].str.contains(title, case=False, na=False)].head(5)[TITLE_COL].tolist()
        return {
            "ok": False,
            "error": "Title not found",
            "suggestions": partial,
            "results": []
        }

    i = int(idxs[0])
    query_fk = df.at[i, "franchise_key"]

    sims = cosine_similarity(X[i], X).ravel()
    order = sims.argsort()[::-1]

    cand = df.iloc[order].copy()
    cand["similarity"] = sims[order]

    # remove same franchise
    cand = cand[cand["franchise_key"] != query_fk]
    cand = cand.drop_duplicates(subset="franchise_key", keep="first")

    if selected_genres:
        cand = cand[cand[GENRE_COL].apply(lambda g: has_all_selected_genres(g, selected_genres))]

    cols = [
        ID_COL, TITLE_COL, IMAGE_COL,
        "mean", "rank", "num_scoring_users",
        GENRE_COL, STUDIO_COL, "similarity"
    ]
    top = cand.head(k)[cols].copy().fillna("")

    return {
        "ok": True,
        "query": title,
        "results": top.to_dict(orient="records")
    }

# -------------------------
# 7) Main
# -------------------------
def main():
    try:
        parser = argparse.ArgumentParser()
        parser.add_argument("--title", required=True)
        parser.add_argument("--k", type=int, default=50)
        parser.add_argument("--genres", default="")
        args = parser.parse_args()

        selected_genres = []

        if args.genres:
            selected_genres = [g.strip() for g in args.genres.split("@@") if g.strip()]

        df, X = build_model()
        payload = recommend(df, X, args.title, args.k, selected_genres)
        print(json.dumps(payload))

    except Exception as e:
        print(json.dumps({
            "ok": False,
            "error": str(e),
            "results": []
        }))

if __name__ == "__main__":
    main()