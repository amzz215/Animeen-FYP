// Initialise session and database connection
<?php
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

// Initialise array that will store genre names and their related images
$genres = [];

// Retrieve all distinct genres from the anime table and pair each one with the top-ranked anime image
try {
    $stat = $pdo->query("
        SELECT DISTINCT TRIM(
            SUBSTRING_INDEX(SUBSTRING_INDEX(genres, ',', numbers.n), ',', -1)
        ) AS genre
        FROM anime
        JOIN (
            SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
            UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
        ) numbers
        ON CHAR_LENGTH(genres) - CHAR_LENGTH(REPLACE(genres, ',', '')) >= numbers.n - 1
        WHERE genres IS NOT NULL AND genres <> ''
        ORDER BY genre
    ");

    $genreNames = $stat->fetchAll(PDO::FETCH_COLUMN);

    // Retrieve the top-ranked anime image for each genre
    $imageStat = $pdo->prepare("
        SELECT main_picture_url
        FROM anime
        WHERE FIND_IN_SET(?, REPLACE(genres, ', ', ',')) > 0
          AND main_picture_url IS NOT NULL
          AND main_picture_url <> ''
          AND rank IS NOT NULL
        ORDER BY rank ASC
        LIMIT 1
    ");

    foreach ($genreNames as $genre) {
        $imageStat->execute([$genre]);
        $image = $imageStat->fetchColumn();

        $genres[] = [
            "name" => $genre,
            "image" => $image ?: ""
        ];
    }

// Fallback to an empty list if genre data cannot be retrieved
} catch (PDOException $ex) {
    $genres = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Genres</title>
    <link rel="stylesheet" href="GenrePage.css?v=<?php echo time(); ?>">
</head>

<body>

<!-- Background video -->
<section>
    <video src="video/naruto.mp4" loop muted autoplay></video>
</section>

<!-- Fixed navigation bar for moving between main pages -->
<div class="static-control-bar">
    <div class="logo">Animeen</div>

    <div class="nav-links">
        <a href="HomeUser.php">Home</a>
        <a href="login.php">Login</a>
        <a href="RankingPage.php">Top Anime</a>
    </div>
</div>

<!-- Main content section displaying all available anime genres -->
<main class="page">
    <h1 class="page-title">All Genres</h1>

    <!-- Grid layout used to display genre cards -->
    <div class="grid genre-grid">
        <?php foreach ($genres as $genre): ?>
            <?php
            // Create variables for genre name, URL slug, and image
            $name = $genre["name"];
            $slug = strtolower(str_replace(" ", "-", $name));
            $image = $genre["image"];
            ?>

            <!-- Each card links to a page showing top anime from the selected genre -->
            <a
                class="genre-card"
                href="TopGenreAnime.php?genre=<?php echo urlencode($slug); ?>"
                <?php if ($image !== ""): ?>
                    style="--card-image: url('<?php echo htmlspecialchars($image); ?>');"
                <?php endif; ?>
            >
                <span><?php echo htmlspecialchars($name); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>