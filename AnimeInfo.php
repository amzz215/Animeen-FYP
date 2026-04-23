<?php
// Initialise session and database connection
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

// Retrieve anime ID from query string and validate input
$animeId = isset($_GET["anime"]) ? (int)$_GET["anime"] : 0;

if ($animeId <= 0) {
    die("Invalid anime ID.");
}

// Query database to retrieve full anime details
$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        main_picture_url,
        synopsis,
        mean,
        rank,
        num_scoring_users,
        genres,
        studios,
        media_type,
        status,
        num_episodes,
        rating
    FROM anime
    WHERE id = :id
    LIMIT 1
");

$stmt->bindValue(":id", $animeId, PDO::PARAM_INT);
$stmt->execute();

$anime = $stmt->fetch();

// Handle case where anime does not exist
if (!$anime) {
    die("Anime not found.");
}

// Map database fields to local variables with fallback values
$title = $anime["title"] ?? "Anime";
$image = !empty($anime["main_picture_url"]) ? $anime["main_picture_url"] : "images/placeholder1.jpg";
$studio = $anime["studios"] ?? "Unknown Studio";
$genres = $anime["genres"] ?? "Unknown Genre";
$desc = $anime["synopsis"] ?? "No synopsis available.";
$mean = $anime["mean"] ?? "N/A";
$rank = $anime["rank"] ?? "N/A";
$mediaType = $anime["media_type"] ?? "Unknown";
$status = $anime["status"] ?? "Unknown";
$numEpisodes = $anime["num_episodes"] ?? "N/A";
$rating = $anime["rating"] ?? "N/A";
$numUsers = $anime["num_scoring_users"] ?? "N/A";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Dynamically sets page title based on selected anime -->
<title><?php echo htmlspecialchars($title); ?></title>
<link rel="stylesheet" href="RankingPage.css">
</head>

<!-- JavaScript handling user interactions (watchlist, like, dislike) -->
<script src="interactions.js"></script>

<body>

<!-- Background video -->
<section>
    <video src="video/naruto.mp4" loop muted autoplay></video>
</section>

<!-- Fixed navigation bar -->
<div class="static-control-bar">
    <div class="logo">Animeen</div>
    <div class="nav-links">
        <a href="Home.php">Home</a>
        <a href="RankingPage.php">Top Anime</a>
        <a href="GenrePage.php">Genres</a>
        <a href="login.php">Login</a>
    </div>
</div>

<!-- Success message displayed after user interactions -->
<?php if (isset($_SESSION["interaction_success"])): ?>
<div style="display:flex; justify-content:center; margin-top:90px; z-index:20;">
    <div style="background-color:green; padding:15px 30px; color:white; border-radius:5px;">
        <?php
        echo htmlspecialchars($_SESSION["interaction_success"]);
        unset($_SESSION["interaction_success"]);
        ?>
    </div>
</div>
<?php endif; ?>

<!-- Error message displayed if interaction fails -->
<?php if (isset($_SESSION["interaction_error"])): ?>
<div style="display:flex; justify-content:center; margin-top:90px; z-index:20;">
    <div style="background-color:#c0392b; padding:15px 30px; color:white; border-radius:5px;">
        <?php
        echo htmlspecialchars($_SESSION["interaction_error"]);
        unset($_SESSION["interaction_error"]);
        ?>
    </div>
</div>
<?php endif; ?>

<!-- Main layout displaying anime details -->
<main class="page">
    <div class="grid" style="grid-template-columns: 320px 1fr;">

        <!-- Poster image section -->
        <div class="poster">
            <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>">
        </div>

        <!-- Metadata and descriptive information -->
        <div class="meta">
            <h1 class="title"><?php echo htmlspecialchars($title); ?></h1>

            <p class="sub"><?php echo htmlspecialchars($studio); ?></p>

            <p class="sub" style="margin-top: 8px;">
                Genre: <?php echo htmlspecialchars($genres); ?>
            </p>

            <p class="sub" style="margin-top: 8px;">
                Mean: <?php echo htmlspecialchars((string)$mean); ?>
                • Rank: <?php echo htmlspecialchars((string)$rank); ?>
            </p>

            <p class="sub" style="margin-top: 8px;">
                Type: <?php echo htmlspecialchars((string)$mediaType); ?>
                • Status: <?php echo htmlspecialchars((string)$status); ?>
            </p>

            <p class="sub" style="margin-top: 8px;">
                Episodes: <?php echo htmlspecialchars((string)$numEpisodes); ?>
                • Rating: <?php echo htmlspecialchars((string)$rating); ?>
            </p>

            <p class="sub" style="margin-top: 8px;">
                Scoring Users: <?php echo htmlspecialchars((string)$numUsers); ?>
            </p>

            <!-- Synopsis section -->
            <p style="margin-top:12px; color: rgba(255,255,255,0.9); line-height: 1.6;">
                <?php echo nl2br(htmlspecialchars($desc)); ?>
            </p>

            <!-- Interaction buttons allowing user engagement -->
            <div class="actions" data-id="<?php echo (int)$animeId; ?>" style="margin-top:16px;">
                <button class="btn btn-watchlist" type="button">+ Watchlist</button>
                <button class="btn btn-like" type="button">👍 Like</button>
                <button class="btn btn-dislike" type="button">👎 Dislike</button>
            </div>

        </div>

    </div>
</main>

</body>
</html>

