import requests
import time
import csv
import mysql.connector

# Stores the MyAnimeList client ID and API request setup
CLIENT_ID = "ef82e2ae76985d0b3c8a7c4eaef76324"
BASE_URL = "https://api.myanimelist.net/v2/anime/ranking"
HEADERS = {"X-MAL-CLIENT-ID": CLIENT_ID}

# Stores the local database connection details
DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "",
    "database": "animeen_db"
}


# Retrieves anime data from the MAL ranking endpoint and filters entries by year
def fetch_all_anime(since_year=1980):
    all_anime = []
    limit = 100
    offset = 0
    fields = (
        "id,title,main_picture,alternative_titles,"
        "synopsis,mean,rank,num_scoring_users,nsfw,"
        "media_type,status,genres,num_episodes,start_season,"
        "average_episode_duration,rating,studios"
    )
    total_fetched = 0
    print("Beginning the search...")

    # Loops through the API results page by page until no more data is returned
    while True:
        params = {
            "ranking_type": "all",
            "limit": limit,
            "offset": offset,
            "fields": fields
        }

        # Handles request errors by retrying after a short delay
        try:
            response = requests.get(BASE_URL, headers=HEADERS, params=params, timeout=10)
        except requests.exceptions.RequestException as e:
            print(f"Error: {e}. Retrying in 5 seconds...")
            time.sleep(5)
            continue

        if response.status_code != 200:
            print(f"Error {response.status_code}")
            break

        data = response.json()
        anime_list = data.get("data", [])

        # Stops the loop once the API no longer returns any anime entries
        if not anime_list:
            print("Stopped — no more data.")
            break

        # Extracts each anime entry and only keeps titles released from the chosen year onwards
        for anime in anime_list:
            node = anime.get("node", {})

            start_season = node.get("start_season", {})
            year = start_season.get("year")

            if year is not None and year >= since_year:
                node["year"] = year
                all_anime.append(node)

        total_fetched += len(anime_list)
        print(f"Page offset={offset}: fetched {len(anime_list)} (total scanned: {total_fetched}, kept: {len(all_anime)})")

        next_page = data.get("paging", {}).get("next")
        if not next_page:
            print("Final page has been reached.")
            break

        offset += limit
        time.sleep(0.5)

    print(f"Total anime collected from {since_year}+ : {len(all_anime)}")
    return all_anime


# Converts the collected anime data into a CSV file
def anime_to_csv(anime_list, filename):
    if not anime_list:
        print("No data to save.")
        return

    fields = [
        "id", "title", "main_picture_url", "alternative_titles",
        "synopsis", "mean", "rank", "num_scoring_users", "nsfw",
        "media_type", "status", "genres", "num_episodes",
        "rating", "studios", "year"
    ]

    with open(filename, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=fields)
        writer.writeheader()

        # Reformats nested MAL fields into a flatter structure suitable for CSV output
        for anime in anime_list:
            main_picture = anime.get("main_picture", {})
            main_pic_url = main_picture.get("large") or main_picture.get("medium") or ""

            alt_titles = anime.get("alternative_titles", {})
            en_title = alt_titles.get("en", "")
            ja_title = alt_titles.get("ja", "")
            synonyms = alt_titles.get("synonyms", [])
            synonyms_str = ", ".join(synonyms) if synonyms else ""
            alt_titles_str = ", ".join(filter(None, [en_title, ja_title, synonyms_str]))

            genres = ",".join([g.get("name", "") for g in anime.get("genres", [])]) if anime.get("genres") else ""
            studios = ",".join([s.get("name", "") for s in anime.get("studios", [])]) if anime.get("studios") else ""

            row = {
                "id": anime.get("id", ""),
                "title": anime.get("title", ""),
                "main_picture_url": main_pic_url,
                "alternative_titles": alt_titles_str,
                "synopsis": (anime.get("synopsis") or "").replace("\n", " "),
                "mean": anime.get("mean", ""),
                "rank": anime.get("rank", ""),
                "num_scoring_users": anime.get("num_scoring_users", ""),
                "nsfw": anime.get("nsfw", ""),
                "media_type": anime.get("media_type", ""),
                "status": anime.get("status", ""),
                "genres": genres,
                "num_episodes": anime.get("num_episodes", ""),
                "rating": anime.get("rating", ""),
                "studios": studios,
                "year": anime.get("year", "")
            }

            writer.writerow(row)

    print(f"Saved {len(anime_list)} anime entries to {filename}")


# Inserts the collected anime data into the MySQL anime table and updates existing rows if needed
def insert_into_mysql(anime_list):
    if not anime_list:
        print("No data to insert into database.")
        return

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    # Uses an upsert query so repeated runs update existing anime instead of duplicating them
    query = """
        INSERT INTO anime (
            id, title, main_picture_url, alternative_titles,
            synopsis, mean, rank, num_scoring_users, nsfw,
            media_type, status, genres, num_episodes,
            rating, studios, year
        )
        VALUES (
            %s, %s, %s, %s,
            %s, %s, %s, %s, %s,
            %s, %s, %s, %s,
            %s, %s, %s
        )
        ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            main_picture_url = VALUES(main_picture_url),
            alternative_titles = VALUES(alternative_titles),
            synopsis = VALUES(synopsis),
            mean = VALUES(mean),
            rank = VALUES(rank),
            num_scoring_users = VALUES(num_scoring_users),
            nsfw = VALUES(nsfw),
            media_type = VALUES(media_type),
            status = VALUES(status),
            genres = VALUES(genres),
            num_episodes = VALUES(num_episodes),
            rating = VALUES(rating),
            studios = VALUES(studios),
            year = VALUES(year)
    """

    inserted = 0

    # Reformats MAL data into values suitable for the anime table
    for anime in anime_list:
        main_picture = anime.get("main_picture", {})
        main_pic_url = main_picture.get("large") or main_picture.get("medium") or ""

        alt_titles = anime.get("alternative_titles", {})
        en_title = alt_titles.get("en", "")
        ja_title = alt_titles.get("ja", "")
        synonyms = alt_titles.get("synonyms", [])
        synonyms_str = ", ".join(synonyms) if synonyms else ""
        alt_titles_str = ", ".join(filter(None, [en_title, ja_title, synonyms_str]))

        genres = ",".join([g.get("name", "") for g in anime.get("genres", [])]) if anime.get("genres") else ""
        studios = ",".join([s.get("name", "") for s in anime.get("studios", [])]) if anime.get("studios") else ""

        values = (
            anime.get("id", ""),
            anime.get("title", ""),
            main_pic_url,
            alt_titles_str,
            (anime.get("synopsis") or "").replace("\n", " "),
            anime.get("mean", None),
            anime.get("rank", None),
            anime.get("num_scoring_users", None),
            anime.get("nsfw", ""),
            anime.get("media_type", ""),
            anime.get("status", ""),
            genres,
            anime.get("num_episodes", None),
            anime.get("rating", ""),
            studios,
            anime.get("year", None)
        )

        cursor.execute(query, values)
        inserted += 1

    conn.commit()
    cursor.close()
    conn.close()

    print(f"Inserted and updated {inserted} anime entries into the anime table.")


# Runs the full script, saving it to CSV, and inserting it into MySQL/database
if __name__ == "__main__":
    output_file = "anime_data.csv"
    anime_data = fetch_all_anime(since_year=1980)
    anime_to_csv(anime_data, output_file)
    insert_into_mysql(anime_data)