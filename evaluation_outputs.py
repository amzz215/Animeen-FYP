import pandas as pd
from CosineSim import build_model, recommend
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity


# Demonstrates how TF-IDF similarity changes depending on how close genre strings are
def tfidf_similarity_outputs():
    print("TF-IDF SIMILARITY OUTPUTS")
    data = [
        "Action, Adventure",
        "Action, Drama",
        "Romance, Slice of Life"
    ]

    vectorizer = TfidfVectorizer(token_pattern=r"[^,]+")
    X = vectorizer.fit_transform(data)

    sim_identical = cosine_similarity(X[0], X[0])[0][0]
    sim_related = cosine_similarity(X[0], X[1])[0][0]
    sim_unrelated = cosine_similarity(X[0], X[2])[0][0]

    print(f"Identical genres similarity: {sim_identical:.4f}")
    print(f"Related genres similarity:   {sim_related:.4f}")
    print(f"Unrelated genres similarity: {sim_unrelated:.4f}")
    print()


# Shows how ranking can be influenced by mean score and number of scoring users
def ranking_influence_outputs():
    print("RANKING INFLUENCE OUTPUTS")

    df = pd.DataFrame([
        {"title": "High Users", "mean": 8.0, "num_scoring_users": 1000000},
        {"title": "Low Users", "mean": 8.0, "num_scoring_users": 10000},
        {"title": "Moderate Users", "mean": 7.5, "num_scoring_users": 400000},
        {"title": "Low-Moderate Users", "mean": 9.5, "num_scoring_users": 10000}
    ])

    ordered = df.sort_values(
        by=["mean", "num_scoring_users"],
        ascending=[False, False]
    ).reset_index(drop=True)

    for i, row in ordered.iterrows():
        print(
            f"Position {i + 1}: {row['title']} | "
            f"mean={row['mean']} | "
            f"users={int(row['num_scoring_users'])}"
        )
    print()


# Runs the recommender on a chosen anime and prints the similarity scores of the top results
def recommendation_similarity_outputs():
    print("=== RECOMMENDATION SIMILARITY SCORES ===")

    df, X = build_model()

    title = "Naruto"  # changeable

    result = recommend(df, X, title, k=5)

    if not result["ok"]:
        print("Error:", result["error"])
        return

    for i, r in enumerate(result["results"], 1):
        print(
            f"{i}. {r['title']} | "
            f"similarity={r['similarity']:.4f}"
        )

    print()


# Checks whether running the recommender twice produces the same ordered results
def consistency_outputs():
    print("=== CONSISTENCY TEST ===")

    df, X = build_model()

    title = "Naruto"

    result1 = recommend(df, X, title, k=5)
    result2 = recommend(df, X, title, k=5)

    titles1 = [r["title"] for r in result1["results"]]
    titles2 = [r["title"] for r in result2["results"]]

    print("Run 1:", titles1)
    print("Run 2:", titles2)

    if titles1 == titles2:
        print("Result: CONSISTENT (identical outputs)")
    else:
        print("Result: INCONSISTENT (outputs differ)")

    print()


# Runs all test outputs in sequence so the behaviour of the recommender can be evaluated
if __name__ == "__main__":
    tfidf_similarity_outputs()
    ranking_influence_outputs()
    recommendation_similarity_outputs()
    consistency_outputs()