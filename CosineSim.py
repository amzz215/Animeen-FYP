import re
import json
import argparse
import pandas as pd
import mysql.connector
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import MinMaxScaler
from sklearn.metrics.pairwise import cosine_similarity
from scipy.sparse import hstack

# Database connection configuration
DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "",
    "database": "animeen_db"
}

# Defines column names
TITLE_COL = "title"
GENRE_COL = "genres"
STUDIO_COL = "studios"
ID_COL = "id"
IMAGE_COL = "main_picture_url"


# Generates a simplified franchise key from a title to group related anime together
# This is used to prevent the recommender returning multiple entries from the same franchise
def franchise_key(title: str) -> str:
    STOP = {"the", "a", "an", "and", "of", "to", "in", "on", "for", "with", "no"}
    t = str(title).lower()

    # Removes brackets, symbols, and common suffixes such as "season" or "movie"
    t = re.sub(r"\(.*?\)|\[.*?\]|\{.*?\}", " ", t)
    t = re.sub(r"[^a-z0-9]+", " ", t)
    t = re.sub(r"\b(season|part|cour|movie|film|ova|ona|special|recap|tv)\b", " ", t)
    t = re.sub(r"\b\d+\b", " ", t)
    t = re.sub(r"\s+", " ", t).strip()

    # Removes stopwords and constructs a short identifier using first 1–2 meaningful words
    toks = [w for w in t.split() if w and w not in STOP]
    return f"{toks[0]} {toks[1]}" if len(toks) >= 2 else (toks[0] if toks else "")


# Loads anime data from the MySQL database into a pandas DataFrame
# This acts as the dataset used by the recommender system
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


# Builds the recommendation model by transforming text and numeric features
# Combines TF-IDF vectors with scaled numerical values into a single feature matrix
def build_model():
    df = load_anime_from_db()

    # Removes rows with missing key fields to ensure valid similarity computation
    df = df.dropna(subset=["mean", "rank", "num_scoring_users", GENRE_COL, STUDIO_COL]).copy()
    df = df.reset_index(drop=True)

    # Ensures all text columns are valid strings to avoid vectorisation errors
    df[TITLE_COL] = df[TITLE_COL].fillna("").astype(str)
    df[GENRE_COL] = df[GENRE_COL].fillna("").astype(str)
    df[STUDIO_COL] = df[STUDIO_COL].fillna("").astype(str)
    df[IMAGE_COL] = df[IMAGE_COL].fillna("").astype(str)

    # Generates a franchise key for each anime to help filter duplicates later
    df["franchise_key"] = df[TITLE_COL].apply(franchise_key)

    # Defines relative importance of genre and studio in similarity calculation
    GENRE_WEIGHT = 0.7
    STUDIO_WEIGHT = 0.3

    # Converts genre and studio text into TF-IDF feature vectors
    tfidf_genre = TfidfVectorizer(token_pattern=r"[^,]+")
    tfidf_studio = TfidfVectorizer(token_pattern=r"[^,]+")

    G_genre = tfidf_genre.fit_transform(df[GENRE_COL]) * GENRE_WEIGHT
    G_studio = tfidf_studio.fit_transform(df[STUDIO_COL]) * STUDIO_WEIGHT

    # Normalises numerical features (mean rating, rank, popularity) into comparable scale
    num = df[["mean", "rank", "num_scoring_users"]].apply(pd.to_numeric, errors="coerce").fillna(0)
    num_scaled = MinMaxScaler().fit_transform(num)

    # Combines all features into a single sparse matrix used for cosine similarity
    X = hstack([G_genre, G_studio, num_scaled]).tocsr()
    return df, X


# Checks whether an anime contains all selected genres from the filter
# Used when user applies genre constraints in the UI
def has_all_selected_genres(anime_genres: str, selected_genres):
    anime_parts = [g.strip().lower() for g in str(anime_genres).split(",") if g.strip()]
    anime_set = set(anime_parts)

    for genre in selected_genres:
        if genre.strip().lower() not in anime_set:
            return False

    return True


# Core recommendation function that returns similar anime based on cosine similarity
# Includes filtering for franchise duplicates and optional genre constraints
def recommend(df, X, title: str, k: int = 200, selected_genres=None):
    if selected_genres is None:
        selected_genres = []

    # Attempts to find an exact title match in the dataset
    idxs = df.index[df[TITLE_COL].str.lower() == title.lower()]

    # If no exact match is found, returns partial suggestions instead of breaking the system
    if len(idxs) == 0:
        partial = df[df[TITLE_COL].str.contains(title, case=False, na=False)].head(5)[TITLE_COL].tolist()
        return {
            "ok": False,
            "error": "Title not found",
            "suggestions": partial,
            "results": []
        }

    i = int(idxs[0])
    query_fk = df.at[i, "franchise_key"]

    # Computes cosine similarity between selected anime and all others
    sims = cosine_similarity(X[i], X).ravel()
    order = sims.argsort()[::-1]

    cand = df.iloc[order].copy()
    cand["similarity"] = sims[order]

    # Removes anime from the same franchise to improve recommendation diversity
    cand = cand[cand["franchise_key"] != query_fk]
    cand = cand.drop_duplicates(subset="franchise_key", keep="first")

    # Applies genre filtering if user selected specific genres
    if selected_genres:
        cand = cand[cand[GENRE_COL].apply(lambda g: has_all_selected_genres(g, selected_genres))]

    # Selects relevant fields to return to frontend
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


# Entry point for script execution, parses CLI arguments and outputs JSON results
def main():
    try:
        parser = argparse.ArgumentParser()
        parser.add_argument("--title", required=True)
        parser.add_argument("--k", type=int, default=50)
        parser.add_argument("--genres", default="")
        args = parser.parse_args()

        selected_genres = []

        # Parses genre filter passed from PHP, split using @@ delimiter
        if args.genres:
            selected_genres = [g.strip() for g in args.genres.split("@@") if g.strip()]

        df, X = build_model()
        payload = recommend(df, X, args.title, args.k, selected_genres)

        # Outputs JSON so it can be consumed by PHP frontend
        print(json.dumps(payload))

    except Exception as e:
        # Handles unexpected errors and returns structured failure response
        print(json.dumps({
            "ok": False,
            "error": str(e),
            "results": []
        }))


# Executes the script when called from command line
if __name__ == "__main__":
    main()