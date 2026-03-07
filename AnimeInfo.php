<?php
require_once __DIR__ . "/AnimeenDbConn.php";

$animeId = isset($_GET["anime"]) ? (int)$_GET["anime"] : 0;

if ($animeId <= 0) {
    die("Invalid anime ID.");
}

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

if (!$anime) {
    die("Anime not found.");
}

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
<title><?php echo htmlspecialchars($title); ?></title>

<link rel="stylesheet" href="RankingPage.css">
</head>

<body>

<section>
    <video src="video/naruto.mp4" loop muted autoplay></video>
</section>

<div class="static-control-bar">
    <div class="logo">Animeen</div>
    <div class="nav-links">
        <a href="Home.php">Home</a>
        <a href="RankingPage.php">Top Anime</a>
        <a href="GenrePage.php">Genres</a>
        <a href="login.php">Login</a>
    </div>
</div>

<main class="page">
    <div class="grid" style="grid-template-columns: 320px 1fr;">

        <div class="poster">
            <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>">
        </div>

        <div class="meta">
            <h1 class="title"><?php echo htmlspecialchars($title); ?></h1>

            <p class="sub">
                <?php echo htmlspecialchars($studio); ?>
            </p>

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

            <p style="margin-top:12px; color: rgba(255,255,255,0.9); line-height: 1.6;">
                <?php echo nl2br(htmlspecialchars($desc)); ?>
            </p>

            <div class="actions" style="margin-top:16px;">
                <button class="btn btn-watchlist" type="button">+ Watchlist</button>
                <button class="btn btn-like" type="button">👍 Like</button>
                <button class="btn btn-dislike" type="button">👎 Dislike</button>
            </div>

            <a href="Recommendations.php" style="display:block;margin-top:14px;">← Back to Top Anime</a>
        </div>

    </div>
</main>

</body>
</html>

