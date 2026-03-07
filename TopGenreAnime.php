<?php
require_once __DIR__ . "/AnimeenDbConn.php";

$limit = 100;
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

$genreSlug = $_GET["genre"] ?? "";
$genreTitle = ucwords(str_replace("-", " ", $genreSlug));

$animeList = [];
$totalPages = 1;

if ($genreSlug !== "") {

    try {

        /* count anime in this genre */
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM anime
            WHERE rank IS NOT NULL
            AND FIND_IN_SET(:genre, REPLACE(genres, ', ', ','))
        ");
        $countStmt->execute(["genre" => $genreTitle]);
        $totalRows = (int)$countStmt->fetch()["total"];

        $totalPages = max(1, ceil($totalRows / $limit));

        /* fetch anime for this page */
        $stmt = $pdo->prepare("
            SELECT id, title, main_picture_url, studios, rank
            FROM anime
            WHERE rank IS NOT NULL
            AND FIND_IN_SET(:genre, REPLACE(genres, ', ', ','))
            ORDER BY rank ASC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(":genre", $genreTitle);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

        $stmt->execute();
        $animeList = $stmt->fetchAll();

    } catch (PDOException $ex) {
        $error = "Failed to load anime.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($genreTitle); ?> — Top Anime</title>
<link rel="stylesheet" href="TopGenreAnime.css">
</head>

<body>

<section>
<video src="video/naruto.mp4" loop muted autoplay></video>
</section>

<div class="static-control-bar">
<div class="logo">Animeen</div>
<div class="nav-links">
<a href="Home.php">Home</a>
<a href="login.php">Login</a>
<a href="RankingPage.php">Top Anime</a>
<a href="GenrePage.php">Genres</a>
</div>
</div>

<main class="page">

<h1 class="page-title">Top Anime — <?php echo htmlspecialchars($genreTitle); ?></h1>

<div class="grid">

<?php foreach ($animeList as $anime): ?>

<article class="anime-card">

<a href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>" class="poster-link">

<div class="poster">
<img
src="<?php echo htmlspecialchars($anime["main_picture_url"] ?: "images/placeholder1.jpg"); ?>"
alt="<?php echo htmlspecialchars($anime["title"]); ?>"
>
</div>

</a>

<div class="meta">
<h3 class="title"><?php echo htmlspecialchars($anime["title"]); ?></h3>

<p class="sub">
<?php echo htmlspecialchars($anime["studios"] ?? "Unknown Studio"); ?>
• Rank: <?php echo htmlspecialchars($anime["rank"]); ?>
</p>
</div>

<div class="actions">
<button class="btn btn-watchlist">+ Watchlist</button>
<button class="btn btn-like">👍</button>
<button class="btn btn-dislike">👎</button>
</div>

</article>

<?php endforeach; ?>

</div>


<div style="display:flex; justify-content:center; gap:10px; margin-top:30px;">

<?php if ($page > 1): ?>
<a class="btn" href="?genre=<?php echo urlencode($genreSlug); ?>&page=<?php echo $page-1; ?>">← Previous</a>
<?php endif; ?>

<span style="color:white;">
Page <?php echo $page; ?> / <?php echo $totalPages; ?>
</span>

<?php if ($page < $totalPages): ?>
<a class="btn" href="?genre=<?php echo urlencode($genreSlug); ?>&page=<?php echo $page+1; ?>">Next →</a>
<?php endif; ?>

</div>

<div class="back-row">
<a class="back-btn" href="GenrePage.php">← Back to Genres</a>
</div>

</main>

</body>
</html>