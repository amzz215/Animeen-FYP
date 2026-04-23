<?php
// Initialise session and database connection
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

// Initialise array that will store all available genres
$genres = [];

// Retrieve distinct genre names from the anime table
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
    // Fallback to an empty array if genre retrieval fails
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
    <link rel="stylesheet" href="Home.css?v=<?php echo time(); ?>">
</head>
<body>

<!-- Background video -->
<section class="video-background">
    <video src="video/kame-house.mp4" loop muted autoplay></video>
</section>

<!-- Fixed navigation bar -->
<div class="static-control-bar">
    <div class="logo">Animeen</div>

    <div class="nav-links">
        <a href="RankingPage.php">Top Anime</a>
        <a href="GenrePage.php">Genres</a>
        <a href="login.php">Login</a>
        <a href="#about">About</a>
    </div>
</div>

<!-- Success message displayed after a user logs in -->
<?php if (isset($_SESSION["loggedin"])): ?>
    <div class="flash-wrap">
        <div class="flash-success">
            <?php
            echo htmlspecialchars($_SESSION["loggedin"]);
            unset($_SESSION["loggedin"]);
            ?>
        </div>
    </div>
<?php endif; ?>

<!-- Main hero section containing the anime search bar -->
<main class="hero-content">
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
</main>

<!-- Overlay shown when the filter drawer is opened -->
<div class="drawer-overlay" id="drawerOverlay"></div>

<!-- Side drawer allowing users to filter anime by genre -->
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
                    <label class="genre-pill">
                        <input
                            type="checkbox"
                            name="genres[]"
                            value="<?php echo htmlspecialchars($genre); ?>"
                            form="searchForm"
                        >
                        <span><?php echo htmlspecialchars($genre); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Drawer action buttons for clearing and applying selected filters -->
    <div class="drawer-actions">
        <button class="clear-filters" type="button" id="clearFilters">Clear</button>
        <button class="apply-filters" type="submit" form="searchForm">Apply</button>
    </div>
</aside>

<!-- About Us section describing the purpose and features -->
<section class="about-section" id="about">
    <div class="about-container">
        <div class="about-left">
            <h2 class="about-title">About Animeen</h2>

            <p class="about-text">
                Animeen is an anime discovery platform built to help users search, explore, and organise anime in one place.
                Whether you want to browse top-ranked titles, explore by genre, or search for personalised recommendations,
                Animeen is designed to make anime discovery feel streamlined and engaging.
            </p>

            <p class="about-text">
                The project combines anime browsing, filtering, recommendation functionality, and account-based interaction
                systems such as watchlisting, liking, and disliking. The aim is to create a modern, user-focused anime platform
                with both strong functionality and a polished visual experience.
            </p>
        </div>

        <!-- Additional navigation links -->
        <div class="about-right">
            <div class="about-links-group">
                <h3>Project Overview</h3>
                <a href="#about">About</a>
                <a href="Recommendations.php?title=Naruto">Recommendations</a>
                <a href="RankingPage.php">Top Anime</a>
                <a href="GenrePage.php">Genres</a>
            </div>

            <div class="about-links-group">
                <h3>Let’s Chat</h3>
                <a href="#">Feedback</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </div>
</section>

<!-- Footer section -->
<footer class="site-footer">
    <div class="footer-inner">
        <p>© 2026 Animeen</p>

        <div class="footer-links">
            <a href="#">Terms</a>
            <a href="#">Privacy</a>
        </div>
    </div>
</footer>

<!-- External JavaScript used for homepage interactions -->
<script src="Home.js?v=<?php echo time(); ?>"></script>
<!-- <script src="Home.js"></script> -->
</body>
</html>