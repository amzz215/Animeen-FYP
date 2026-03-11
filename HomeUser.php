<?php
session_start();

// if (!isset($_SESSION["uid"])) {
//     header("Location: Home.php");
//     exit;
// }

require_once __DIR__ . "/AnimeenDbConn.php";

$genres = [];

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

    $genres = $stat->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $ex) {
    $genres = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <link rel="stylesheet" href="Home.css">
</head>

<body>

<section>
    <video src="video/kame-house.mp4" loop muted autoplay></video>
</section>

<div class="static-control-bar">
    <div class="logo">Animeen</div>

    <div class="nav-links">
        <a href="RankingPage.php">Top Anime</a>
        <a href="GenrePage.php">Genres</a>
        <a href="account.php">Account</a>
        <a href="#">About</a>
    </div>
</div>

    <?php if (isset($_SESSION["loggedin"])): ?>
        <div style="display:flex; justify-content:center; align-items:center; margin-top:90px; position:relative; z-index:20;">
            <div style="background-color:green; padding:15px 30px; color:white; border:1px solid green; font-weight:bold; border-radius:5px; text-align:center;">
                <?php
                echo htmlspecialchars($_SESSION["loggedin"]);
                unset($_SESSION["loggedin"]);
                ?>
            </div>
        </div>
    <?php endif; ?>

<div class="search-container">
    <form class="search-bar" id="searchForm" method="get" action="Recommendations.php">
        <input
            class="search-input"
            type="text"
            name="title"
            placeholder="Search anime..."
            required
        >

        <button class="search-btn" type="submit">Search</button>
        <button class="filter-btn" type="button" id="openFilters">Filter</button>
    </form>
</div>

<div class="drawer-overlay" id="drawerOverlay"></div>

<aside class="filter-drawer" id="filterDrawer" aria-hidden="true">
    <div class="drawer-header">
        <h2>Filters</h2>
        <button class="drawer-close" type="button" id="closeFilters">✕</button>
    </div>

    <div class="drawer-content">

        <div class="filter-item">
            <span>Genre</span>
            <div class="genre-grid">
                <?php foreach ($genres as $genre): ?>
                    <?php $slug = strtolower(str_replace(" ", "-", $genre)); ?>
                    <label class="genre-pill">
                        <input type="checkbox" value="<?php echo htmlspecialchars($slug); ?>">
                        <span><?php echo htmlspecialchars($genre); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-item">
            <span>Type</span>
            <div class="radio-row">
                <label class="radio-pill">
                    <input type="radio" name="filterType" value="" checked>
                    <span>Any</span>
                </label>
                <label class="radio-pill">
                    <input type="radio" name="filterType" value="tv">
                    <span>TV</span>
                </label>
                <label class="radio-pill">
                    <input type="radio" name="filterType" value="movie">
                    <span>Movie</span>
                </label>
            </div>
        </div>

        <div class="filter-item">
            <span>Year range</span>
            <div class="year-row">
                <select id="startYear"></select>
                <select id="endYear"></select>
            </div>
        </div>

        <div class="drawer-actions">
            <button class="clear-filters" type="button" id="clearFilters">Clear</button>
            <button class="apply-filters" type="button" id="applyFilters">Apply</button>
        </div>

    </div>
</aside>

<script src="Home.js"></script>
</body>
</html>