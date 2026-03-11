<?php
require_once __DIR__ . "/AnimeenDbConn.php";

$limit = 100;
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

$animeList = [];
$error = "";
$totalPages = 1;

try {
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM anime
        WHERE rank IS NOT NULL
    ");
    $countStmt->execute();
    $totalRows = (int)$countStmt->fetch()["total"];

    $totalPages = max(1, (int)ceil($totalRows / $limit));

    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $limit;
    }

    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            main_picture_url,
            studios,
            rank
        FROM anime
        WHERE rank IS NOT NULL
        ORDER BY rank ASC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    $animeList = $stmt->fetchAll();

} catch (PDOException $ex) {
    $error = "Failed to load anime from the database.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Anime</title>

    <link rel="stylesheet" href="RankingPage.css">
</head>

<script src="interactions.js"></script>
<script src="RankingPage.js"></script>

<body>

<section>
    <video src="video/naruto.mp4" loop muted autoplay></video>
</section>

<div class="static-control-bar">
    <div class="logo">Animeen</div>
    <div class="nav-links">
        <a href="Home.php">Home</a>
        <a href="login.php">Login</a>
        <a href="GenrePage.php">Genres</a>
        <a href="#">About</a>
    </div>
</div>

<?php if (isset($_SESSION["interaction_success"])): ?>
<div style="display:flex; justify-content:center; align-items:center; margin-top:90px; position:relative; z-index:20;">
    <div style="background-color:green; padding:15px 30px; color:white; border:1px solid green; font-weight:bold; border-radius:5px; text-align:center;">
        <?php
        echo htmlspecialchars($_SESSION["interaction_success"]);
        unset($_SESSION["interaction_success"]);
        ?>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION["interaction_error"])): ?>
<div style="display:flex; justify-content:center; align-items:center; margin-top:90px; position:relative; z-index:20;">
    <div style="background-color:#c0392b; padding:15px 30px; color:white; border:1px solid #c0392b; font-weight:bold; border-radius:5px; text-align:center;">
        <?php
        echo htmlspecialchars($_SESSION["interaction_error"]);
        unset($_SESSION["interaction_error"]);
        ?>
    </div>
</div>
<?php endif; ?>

<main class="page">
    <h1 class="page-title">Browse Highest Rated Anime</h1>

    <?php if (!empty($error)): ?>
        <p class="sub" style="color: white; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <div class="grid">
        <?php foreach ($animeList as $anime): ?>
            <article class="anime-card" data-id="<?php echo (int)$anime["id"]; ?>">

                <a href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>" class="poster-link">
                    <div class="poster">
                        <img
                            src="<?php echo htmlspecialchars(!empty($anime["main_picture_url"]) ? $anime["main_picture_url"] : "images/placeholder1.jpg"); ?>"
                            alt="<?php echo htmlspecialchars($anime["title"]); ?>"
                        >
                    </div>
                </a>

                <div class="meta">
                    <h3 class="title"><?php echo htmlspecialchars($anime["title"]); ?></h3>
                    <p class="sub">
                        <?php echo htmlspecialchars($anime["studios"] ?? "Unknown Studio"); ?>
                        • Rank: <?php echo htmlspecialchars((string)$anime["rank"]); ?>
                    </p>
                </div>

                <div class="actions">
                    <button class="btn btn-watchlist" type="button">+ Watchlist</button>
                    <button class="btn btn-like" type="button">👍</button>
                    <button class="btn btn-dislike" type="button">👎</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div style="display:flex; justify-content:center; align-items:center; gap:12px; margin-top:30px; flex-wrap:wrap;">

        <?php if ($page > 1): ?>
            <a class="btn" href="RankingPage.php?page=<?php echo $page - 1; ?>" style="text-decoration:none;">← Previous</a>
        <?php endif; ?>

        <span class="sub" style="color:white;">
            Page <?php echo $page; ?> of <?php echo $totalPages; ?>
        </span>

        <?php if ($page < $totalPages): ?>
            <a class="btn" href="RankingPage.php?page=<?php echo $page + 1; ?>" style="text-decoration:none;">Next →</a>
        <?php endif; ?>

    </div>
</main>

</body>
</html>

