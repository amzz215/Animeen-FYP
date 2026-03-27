<?php
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

$title = isset($_GET["title"]) ? trim($_GET["title"]) : "";
$genres = isset($_GET["genres"]) && is_array($_GET["genres"]) ? $_GET["genres"] : [];

$results = [];
$error = "";
$suggestions = [];
$fallbackMessage = "";

$cleanGenres = [];

foreach ($genres as $genre) {
    $genre = trim($genre);
    if ($genre !== "") {
        $cleanGenres[] = $genre;
    }
}

function runRecommendations($title, $genres = [])
{
    $python = "python";
    $script = __DIR__ . DIRECTORY_SEPARATOR . "CosineSim.py";

    $command = escapeshellcmd($python) . " "
             . escapeshellarg($script)
             . " --title " . escapeshellarg($title)
             . " --k 250";

    if (!empty($genres)) {
        $command .= " --genres " . escapeshellarg(implode("@@", $genres));
    }

    $output = shell_exec($command);

    if ($output === null || trim($output) === "") {
        return [
            "valid" => false,
            "ok" => false,
            "results" => [],
            "suggestions" => [],
            "error" => "Couldn't return recommendation, try again later please."
        ];
    }

    $data = json_decode(trim($output), true);

    if (!is_array($data)) {
        return [
            "valid" => false,
            "ok" => false,
            "results" => [],
            "suggestions" => [],
            "error" => "Invalid recommendation, couldn't show."
        ];
    }

    return [
        "valid" => true,
        "ok" => !empty($data["ok"]),
        "results" => $data["results"] ?? [],
        "suggestions" => $data["suggestions"] ?? [],
        "error" => $data["error"] ?? "Recommendation search failed."
    ];
}

if ($title !== "") {
    $response = runRecommendations($title, $cleanGenres);

    if ($response["valid"] && $response["ok"] && !empty($response["results"])) {
        $results = $response["results"];
    } else {
        $usedGenreFilter = !empty($cleanGenres);

        if ($usedGenreFilter) {
            $fallback = runRecommendations($title, []);

            if ($fallback["valid"] && $fallback["ok"]) {
                $results = $fallback["results"];
                $suggestions = $fallback["suggestions"];
                $fallbackMessage = "Sorry, couldn't find any recommendations matching your preference, here’s what we got instead.";
            } else {
                $error = $fallback["error"];
                $suggestions = $fallback["suggestions"];
            }
        } else {
            $error = $response["error"];
            $suggestions = $response["suggestions"];
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
    <link rel="stylesheet" href="RankingPage.css?v=<?php echo time(); ?>">
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

<div id="interactionMessageWrap"
     style="
        display:none;
        position:fixed;
        top:90px;
        left:50%;
        transform:translateX(-50%);
        z-index:9999;
        justify-content:center;
        align-items:center;
     ">

    <div id="interactionMessageBox"
         style="
            padding:15px 30px;
            color:white;
            font-weight:bold;
            border-radius:5px;
            text-align:center;
            min-width:250px;
         ">
    </div>
</div>

<main class="page">
    <h1 class="page-title">
        Top Recommendations<?php echo $title !== "" ? " for " . htmlspecialchars($title) : ""; ?>
    </h1>

    <?php if ($fallbackMessage !== ""): ?>
        <p class="sub" style="margin-bottom: 18px; color: rgba(255,255,255,0.9);">
            <?php echo htmlspecialchars($fallbackMessage); ?>
        </p>
    <?php endif; ?>

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

<script src="interactions.js?v=<?php echo time(); ?>"></script>
<script src="RankingPage.js?v=<?php echo time(); ?>"></script>
</body>
</html>