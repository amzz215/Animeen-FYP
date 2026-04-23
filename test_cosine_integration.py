import pandas as pd
import pytest

from CosineSim import build_model, recommend, has_all_selected_genres
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity


# Provides a reusable sample dataset for testing the recommender
@pytest.fixture
def sample_df():
    return pd.DataFrame([
        {
            "id": 1,
            "title": "Naruto",
            "main_picture_url": "url1",
            "mean": 8.0,
            "rank": 500,
            "num_scoring_users": 900000,
            "genres": "Action, Adventure",
            "studios": "Pierrot"
        },
        {
            "id": 2,
            "title": "Naruto Season 2",
            "main_picture_url": "url2",
            "mean": 7.9,
            "rank": 550,
            "num_scoring_users": 850000,
            "genres": "Action, Adventure",
            "studios": "Pierrot"
        },
        {
            "id": 3,
            "title": "Bleach",
            "main_picture_url": "url3",
            "mean": 7.8,
            "rank": 700,
            "num_scoring_users": 700000,
            "genres": "Action, Supernatural",
            "studios": "Pierrot"
        },
        {
            "id": 4,
            "title": "Attack on Titan",
            "main_picture_url": "url4",
            "mean": 8.8,
            "rank": 80,
            "num_scoring_users": 1200000,
            "genres": "Action, Drama",
            "studios": "Wit Studio"
        },
        {
            "id": 5,
            "title": "Your Lie in April",
            "main_picture_url": "url5",
            "mean": 8.6,
            "rank": 120,
            "num_scoring_users": 550000,
            "genres": "Drama, Romance",
            "studios": "A-1 Pictures"
        },
        {
            "id": 6,
            "title": "Haikyuu",
            "main_picture_url": "url6",
            "mean": 8.4,
            "rank": 200,
            "num_scoring_users": 500000,
            "genres": "Sports, Comedy",
            "studios": "Production I.G"
        },
    ])


# Replaces the real database loader with test data so the model can be tested in isolation
@pytest.fixture
def built_model_from_sample(monkeypatch, sample_df):
    def fake_load_anime_from_db():
        return sample_df.copy()

    monkeypatch.setattr("CosineSim.load_anime_from_db", fake_load_anime_from_db)
    df, X = build_model()
    return df, X


# Checks that the model returns both a dataframe and a usable feature matrix
def test_build_model_returns_dataframe_and_matrix(built_model_from_sample):
    df, X = built_model_from_sample

    assert isinstance(df, pd.DataFrame)
    assert len(df) == 6
    assert X.shape[0] == len(df)
    assert X.shape[1] > 0


# Checks that franchise keys are added correctly for later franchise filtering
def test_build_model_adds_franchise_key_column(built_model_from_sample):
    df, _ = built_model_from_sample

    assert "franchise_key" in df.columns
    assert df.loc[df["title"] == "Naruto", "franchise_key"].iloc[0] == "naruto"
    assert df.loc[df["title"] == "Naruto Season 2", "franchise_key"].iloc[0] == "naruto"


# Checks that an exact title match returns a successful recommendation payload
def test_recommend_returns_success_payload_for_exact_title(built_model_from_sample):
    df, X = built_model_from_sample
    payload = recommend(df, X, "Naruto", k=3)

    assert payload["ok"] is True
    assert payload["query"] == "Naruto"
    assert isinstance(payload["results"], list)


# Ensures that the original query title and same-franchise titles are excluded from recommendations
def test_recommend_excludes_query_title_and_same_franchise(built_model_from_sample):
    df, X = built_model_from_sample
    payload = recommend(df, X, "Naruto", k=10)

    titles = [row["title"] for row in payload["results"]]

    assert "Naruto" not in titles
    assert "Naruto Season 2" not in titles


# Checks that the recommender respects the requested number of returned results
def test_recommend_respects_k_limit(built_model_from_sample):
    df, X = built_model_from_sample
    payload = recommend(df, X, "Naruto", k=2)

    assert len(payload["results"]) <= 2


# Verifies that the recommender applies genre filtering correctly
def test_recommend_applies_genre_filter(built_model_from_sample):
    df, X = built_model_from_sample
    payload = recommend(df, X, "Naruto", k=10, selected_genres=["Romance"])

    assert payload["ok"] is True
    assert len(payload["results"]) > 0
    assert all(
        has_all_selected_genres(row["genres"], ["Romance"])
        for row in payload["results"]
    )


# Checks that no results are returned when no anime match the selected genre filter
def test_recommend_returns_empty_when_no_genre_matches(built_model_from_sample):
    df, X = built_model_from_sample
    payload = recommend(df, X, "Naruto", k=10, selected_genres=["Horror"])

    assert payload["ok"] is True
    assert payload["results"] == []


# Checks that the recommender returns suggestions when the title cannot be found
def test_recommend_title_not_found_returns_suggestions(built_model_from_sample):
    df, X = built_model_from_sample
    payload = recommend(df, X, "NarutoXYZ", k=10)

    assert payload["ok"] is False
    assert payload["error"] == "Title not found"
    assert isinstance(payload["suggestions"], list)
    assert isinstance(payload["results"], list)
    assert payload["results"] == []


# Verifies that each recommendation result contains the expected output fields
def test_recommend_result_structure(built_model_from_sample):
    df, X = built_model_from_sample
    payload = recommend(df, X, "Naruto", k=3)

    assert payload["ok"] is True
    assert len(payload["results"]) > 0

    row = payload["results"][0]
    expected_keys = {
        "id",
        "title",
        "main_picture_url",
        "mean",
        "rank",
        "num_scoring_users",
        "genres",
        "studios",
        "similarity",
    }

    assert expected_keys.issubset(row.keys())


# Ensures rows missing required recommendation fields are removed during model building
def test_build_model_drops_rows_with_missing_required_fields(monkeypatch):
    df_with_missing = pd.DataFrame([
        {
            "id": 1,
            "title": "Naruto",
            "main_picture_url": "url1",
            "mean": 8.0,
            "rank": 500,
            "num_scoring_users": 900000,
            "genres": "Action, Adventure",
            "studios": "Pierrot"
        },
        {
            "id": 2,
            "title": "Broken Anime",
            "main_picture_url": "url2",
            "mean": None,
            "rank": 300,
            "num_scoring_users": 500000,
            "genres": "Action",
            "studios": "Some Studio"
        },
    ])

    def fake_load_anime_from_db():
        return df_with_missing.copy()

    monkeypatch.setattr("CosineSim.load_anime_from_db", fake_load_anime_from_db)

    df, X = build_model()

    assert len(df) == 1
    assert df.iloc[0]["title"] == "Naruto"
    assert X.shape[0] == 1


# Checks that higher user count can influence recommendation ordering when other factors are similar
def test_higher_user_count_influences_ranking(monkeypatch):
    df = pd.DataFrame([
        {
            "id": 1,
            "title": "Query Anime",
            "main_picture_url": "url",
            "mean": 8.0,
            "rank": 100,
            "num_scoring_users": 500000,
            "genres": "Action",
            "studios": "StudioA"
        },
        {
            "id": 2,
            "title": "Low Users",
            "main_picture_url": "url",
            "mean": 8.0,
            "rank": 200,
            "num_scoring_users": 10000,
            "genres": "Action",
            "studios": "StudioA"
        },
        {
            "id": 3,
            "title": "High Users",
            "main_picture_url": "url",
            "mean": 8.0,
            "rank": 200,
            "num_scoring_users": 1000000,
            "genres": "Action",
            "studios": "StudioA"
        },
        {
            "id": 4,
            "title": "Moderate Users",
            "main_picture_url": "url",
            "mean": 7.5,
            "rank": 200,
            "num_scoring_users": 400000,
            "genres": "Action",
            "studios": "StudioA"
        },
        {
            "id": 5,
            "title": "low-moderate Users",
            "main_picture_url": "url",
            "mean": 9.5,
            "rank": 400,
            "num_scoring_users": 1,
            "genres": "Action",
            "studios": "StudioA"
        },
    ])

    def fake_load_anime_from_db():
        return df.copy()

    monkeypatch.setattr("CosineSim.load_anime_from_db", fake_load_anime_from_db)

    df_model, X = build_model()
    result = recommend(df_model, X, "Query Anime", k=3)

    titles = [r["title"] for r in result["results"]]

    assert titles.index("High Users") < titles.index("Low Users")


# Checks that running the recommender multiple times on the same data gives consistent results
def test_recommendation_consistency(monkeypatch):
    df = pd.DataFrame([
        {
            "id": 1,
            "title": "Naruto",
            "main_picture_url": "url",
            "mean": 8.0,
            "rank": 500,
            "num_scoring_users": 900000,
            "genres": "Action, Adventure",
            "studios": "Pierrot"
        },
        {
            "id": 2,
            "title": "Bleach",
            "main_picture_url": "url",
            "mean": 7.8,
            "rank": 700,
            "num_scoring_users": 700000,
            "genres": "Action, Supernatural",
            "studios": "Pierrot"
        },
        {
            "id": 3,
            "title": "Attack on Titan",
            "main_picture_url": "url",
            "mean": 8.8,
            "rank": 80,
            "num_scoring_users": 1200000,
            "genres": "Action, Drama",
            "studios": "Wit Studio"
        },
    ])

    def fake_load_anime_from_db():
        return df.copy()

    monkeypatch.setattr("CosineSim.load_anime_from_db", fake_load_anime_from_db)

    df1, X1 = build_model()
    result1 = recommend(df1, X1, "Naruto", k=2)

    df2, X2 = build_model()
    result2 = recommend(df2, X2, "Naruto", k=2)

    assert result1 == result2