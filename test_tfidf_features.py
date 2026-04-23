import pandas as pd
import pytest
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity


# Tests that the TF-IDF vectoriser correctly builds a matrix from genre data
def test_tfidf_matrix_builds():
    data = pd.Series([
        "Action, Adventure",
        "Action, Drama",
        "Romance, Comedy"
    ])

    vectorizer = TfidfVectorizer(token_pattern=r"[^,]+")
    matrix = vectorizer.fit_transform(data)

    # Ensure correct number of rows (documents) and non-empty feature space
    assert matrix.shape[0] == 3
    assert matrix.shape[1] > 0


# Checks that similar genre combinations produce a higher cosine similarity score
# compared to completely unrelated genre combinations
def test_tfidf_similar_genres_score_higher_than_unrelated():
    data = pd.Series([
        "Action, Adventure",
        "Action, Drama",
        "Romance, Comedy"
    ])

    vectorizer = TfidfVectorizer(token_pattern=r"[^,]+")
    matrix = vectorizer.fit_transform(data)

    sim_related = cosine_similarity(matrix[0], matrix[1])[0][0]
    sim_unrelated = cosine_similarity(matrix[0], matrix[2])[0][0]

    assert sim_related > sim_unrelated


# Ensures that identical genre inputs result in a similarity score of 1
def test_tfidf_identical_genres_similarity_is_one():
    data = pd.Series([
        "Action, Adventure",
        "Action, Adventure"
    ])

    vectorizer = TfidfVectorizer(token_pattern=r"[^,]+")
    matrix = vectorizer.fit_transform(data)

    sim = cosine_similarity(matrix[0], matrix[1])[0][0]

    assert sim == pytest.approx(1.0, rel=1e-5)