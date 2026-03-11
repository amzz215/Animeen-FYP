<?php
require_once __DIR__ . "/AnimeenDbConn.php";

$title = isset($_GET["title"]) ? trim($_GET["title"]) : "";
$results = [];
$error = "";
$suggestions = [];

if ($title !== "") {
    $python = "python";
    $script = __DIR__ . DIRECTORY_SEPARATOR . "CosineSim.py";

    $command = escapeshellcmd($python) . " "
             . escapeshellarg($script)
             . " --title " . escapeshellarg($title)
             . " --k 12";

    $output = shell_exec($command);

    if ($output === null || trim($output) === "") {
        $error = "Couldn't return recommendation, try again later please.";
    } else {
        $data = json_decode($output, true);

        if (!is_array($data)) {
            $error = "Invalid recommendation, couldn't show.";
        } elseif (!empty($data["ok"])) {
            $results = $data["results"] ?? [];
        } else {
            $error = $data["error"] ?? "Recommendation search failed.";
            $suggestions = $data["suggestions"] ?? [];
        }
    }
} else {
    $error = "Please search for an anime title.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommendations</title>

    <link rel="stylesheet" href="RankingPage.css">
</head>

<script src="interactions.js"></script>

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
    <h1 class="page-title">
        Top Recommendations<?php echo $title !== "" ? " for " . htmlspecialchars($title) : ""; ?>
    </h1>

    <?php if ($error !== ""): ?>
        <p class="sub" style="margin-bottom: 18px; color: rgba(255,255,255,0.9);">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($suggestions)): ?>
        <div style="margin-bottom: 20px;">
            <p class="sub" style="color: rgba(255,255,255,0.85); margin-bottom: 8px;">Did you mean:</p>
            <?php foreach ($suggestions as $suggestion): ?>
                <a
                    href="Recommendations.php?title=<?php echo urlencode($suggestion); ?>"
                    class="btn"
                    style="display:inline-block; margin-right:8px; margin-bottom:8px; text-decoration:none;"
                >
                    <?php echo htmlspecialchars($suggestion); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <?php foreach ($results as $anime): ?>
            <article class="anime-card" data-id="<?php echo (int)($anime["id"] ?? 0); ?>">
                <a href="AnimeInfo.php?anime=<?php echo (int)($anime["id"] ?? 0); ?>" class="poster-link">
                    <div class="poster">
                        <img
                            src="<?php echo htmlspecialchars($anime["main_picture_url"] ?: "images/placeholder1.jpg"); ?>"
                            alt="<?php echo htmlspecialchars($anime["title"] ?? "Anime"); ?>"
                        >
                    </div>
                </a>

                <div class="meta">
                    <h3 class="title"><?php echo htmlspecialchars($anime["title"] ?? ""); ?></h3>
                    <p class="sub">
                        Mean: <?php echo htmlspecialchars((string)($anime["mean"] ?? "")); ?>
                        • Rank: <?php echo htmlspecialchars((string)($anime["rank"] ?? "")); ?>
                    </p>
                    <p class="sub">
                        <?php echo htmlspecialchars($anime["studios"] ?? ""); ?>
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
</main>

<script src="RankingPage.js"></script>
</body>
</html>
