<?php
// Initialise session and database connection
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

// Retrieve search title and selected genres from the query string
$title = isset($_GET["title"]) ? trim($_GET["title"]) : "";
$genres = isset($_GET["genres"]) && is_array($_GET["genres"]) ? $_GET["genres"] : [];

// Initialise variables used for recommendations, suggestions, errors, and fallback behaviour
$results = [];
$error = "";
$suggestions = [];
$fallbackMessage = "";

$measuredTime = null;
$fallbackMeasuredTime = null;

$cleanGenres = [];

// Clean the selected genres array by removing empty values
foreach ($genres as $genre) {
    $genre = trim($genre);
    if ($genre !== "") {
        $cleanGenres[] = $genre;
    }
}

// Executes the Python recommender script and returns structured response data
function runRecommendations($title, $genres = [])
{
    $python = "python";
    $script = __DIR__ . DIRECTORY_SEPARATOR . "CosineSim.py";

    $command = escapeshellcmd($python) . " "
             . escapeshellarg($script)
             . " --title " . escapeshellarg($title)
             . " --k 250";

    // Pass selected genres into the Python script if filters were applied
    if (!empty($genres)) {
        $command .= " --genres " . escapeshellarg(implode("@@", $genres));
    }

    // Measure how long the recommender script takes to run
    $startTime = microtime(true);
    $output = shell_exec($command);
    $endTime = microtime(true);

    $responseTimeMs = ($endTime - $startTime) * 1000;

    // Handle case where script produces no output
    if ($output === null || trim($output) === "") {
        return [
            "valid" => false,
            "ok" => false,
            "results" => [],
            "suggestions" => [],
            "error" => "Couldn't return recommendation, try again later please.",
            "response_time_ms" => $responseTimeMs
        ];
    }

    $data = json_decode(trim($output), true);

    // Handle case where script output is not valid JSON
    if (!is_array($data)) {
        return [
            "valid" => false,
            "ok" => false,
            "results" => [],
            "suggestions" => [],
            "error" => "Invalid recommendation, couldn't show.",
            "response_time_ms" => $responseTimeMs
        ];
    }

    // Return a normalised response structure for the PHP page to use
    return [
        "valid" => true,
        "ok" => !empty($data["ok"]),
        "results" => $data["results"] ?? [],
        "suggestions" => $data["suggestions"] ?? [],
        "error" => $data["error"] ?? "Recommendation search failed.",
        "response_time_ms" => $responseTimeMs
    ];
}

// Run the recommender if the user has entered a title
if ($title !== "") {
    $response = runRecommendations($title, $cleanGenres);
    $measuredTime = $response["response_time_ms"] ?? null;

    // Display results if the recommendation request succeeds
    if ($response["valid"] && $response["ok"] && !empty($response["results"])) {
        $results = $response["results"];
    } else {
        $usedGenreFilter = !empty($cleanGenres);

        // Retry without genre filters if no results were found with filters applied
        if ($usedGenreFilter) {
            $fallback = runRecommendations($title, []);
            $fallbackMeasuredTime = $fallback["response_time_ms"] ?? null;

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
    // Show message if user loads page without searching for a title
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

<!-- Background video -->
<section>
    <video src="video/naruto.mp4" loop muted autoplay></video>
</section>

<!-- Fixed navigation bar for moving between main pages -->
<div class="static-control-bar">
    <div class="logo">Animeen</div>
    <div class="nav-links">
        <a href="Home.php">Home</a>
        <a href="account.php">Account</a>
        <a href="RankingPage.php">Top Anime</a>
        <a href="GenrePage.php">Genres</a>
    </div>
</div>

<!-- Hidden container used for interaction messages shown by JavaScript -->
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

<!-- Main content area displaying recommendation results -->
<main class="page">
    <h1 class="page-title">
        Top Recommendations<?php echo $title !== "" ? " for " . htmlspecialchars($title) : ""; ?>
    </h1>

    <!-- Message shown if the genre-filtered search had to fall back to general recommendations -->
    <?php if ($fallbackMessage !== ""): ?>
        <p class="sub" style="margin-bottom: 18px; color: rgba(255,255,255,0.9);">
            <?php echo htmlspecialchars($fallbackMessage); ?>
        </p>
    <?php endif; ?>

    <!-- Error message shown if recommendations could not be generated -->
    <?php if ($error !== ""): ?>
        <p class="sub" style="margin-bottom: 18px; color: rgba(255,255,255,0.9);">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <!-- Debugging output for response times, currently left commented out -->
    <!-- <?php if ($measuredTime !== null): ?>
        <p class="sub" style="margin-bottom: 10px; color: rgba(255,255,255,0.9);">
            Response time: <?php echo round($measuredTime, 2); ?> ms
        </p>
    <?php endif; ?>

    <?php if ($fallbackMeasuredTime !== null): ?>
        <p class="sub" style="margin-bottom: 10px; color: rgba(255,255,255,0.9);">
            Fallback response time: <?php echo round($fallbackMeasuredTime, 2); ?> ms
        </p>
    <?php endif; ?> -->

    <!-- Suggestion buttons shown if the entered title was not found exactly -->
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

    <!-- Grid layout showing the recommended anime cards -->
    <div class="grid">
        <?php foreach ($results as $anime): ?>
            <article class="anime-card" data-id="<?php echo (int)($anime["id"] ?? 0); ?>">

                <!-- Poster links through to the individual anime information page -->
                <a href="AnimeInfo.php?anime=<?php echo (int)($anime["id"] ?? 0); ?>" class="poster-link">
                    <div class="poster">
                        <img
                            src="<?php echo htmlspecialchars($anime["main_picture_url"] ?: "images/placeholder1.jpg"); ?>"
                            alt="<?php echo htmlspecialchars($anime["title"] ?? "Anime"); ?>"
                        >
                    </div>
                </a>

                <!-- Displays recommendation metadata such as score, rank, and studio -->
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

                <!-- Interaction buttons allowing the user to save or react to recommendations -->
                <div class="actions">
                    <button class="btn btn-watchlist" type="button">+ Watchlist</button>
                    <button class="btn btn-like" type="button">👍</button>
                    <button class="btn btn-dislike" type="button">👎</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<!-- External scripts handling interactions -->
<script src="interactions.js?v=<?php echo time(); ?>"></script>
<script src="RankingPage.js?v=<?php echo time(); ?>"></script>
</body>
</html>