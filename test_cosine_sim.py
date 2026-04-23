from CosineSim import franchise_key, has_all_selected_genres


# Tests that a normal title is reduced correctly into a franchise key
def test_franchise_key_basic():
    assert franchise_key("Attack on Titan") == "attack titan"


# Ensures season numbers and keywords like "Season" are removed properly
def test_franchise_key_with_season():
    assert franchise_key("Naruto Season 2") == "naruto"


# Checks that words like "Movie" are stripped so franchise grouping works correctly
def test_franchise_key_removes_movie():
    assert franchise_key("Demon Slayer Movie") == "demon slayer"


# Ensures standalone numbers are removed from the title
def test_franchise_key_removes_numbers():
    assert franchise_key("Tokyo Ghoul 2") == "tokyo ghoul"


# Handles edge case where an empty title is passed in
def test_franchise_key_empty():
    assert franchise_key("") == ""


# Verifies that bracketed information (seasons or versions) is ignored
def test_franchise_key_removes_brackets():
    assert franchise_key("Attack on Titan (Final Season)") == "attack titan"


# Checks that common stopwords are removed to keep the key concise
def test_franchise_key_removes_stopwords():
    assert franchise_key("The Irregular at Magic High School") == "irregular at"


# Basic test to check a single genre match works correctly
def test_genre_single_match():
    assert has_all_selected_genres("Action, Comedy", ["Action"]) is True


# Ensures multiple selected genres must all be present in the anime genres
def test_genre_multiple_match():
    assert has_all_selected_genres("Action, Comedy, Drama", ["Action", "Comedy"]) is True


# Confirms that the function returns False if required genres are missing
def test_genre_no_match():
    assert has_all_selected_genres("Action, Comedy", ["Romance"]) is False


# Ensures genre matching is case-insensitive
def test_genre_case_insensitive():
    assert has_all_selected_genres("Action, Comedy", ["action"]) is True


# If no genres are selected, the function should allow all results
def test_genre_empty_selected():
    assert has_all_selected_genres("Action, Comedy", []) is True


# Ensures extra whitespace in genre strings does not affect matching
def test_genre_whitespace_handling():
    assert has_all_selected_genres("Action,  Comedy ", ["Comedy"]) is True


# Edge case where the anime has no genres listed
def test_genre_empty_source_string():
    assert has_all_selected_genres("", ["Action"]) is False