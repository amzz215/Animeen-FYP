<?php
// Initialise session and database connection
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

// Restrict access to logged-in users only and if not logged in sent to the login page
if (!isset($_SESSION["uid"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["uid"];
$user = null;

// Handle logout functionality and redirect with success feedback
if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();

    session_start();
    $_SESSION["success_l"] = "You have been logged out successfully.";

    header("Location: Home.php");
    exit;
}

// Retrieve user account details from the database
try {
    $stmt = $pdo->prepare("
        SELECT uid, username, first_name, last_name, email, created_at
        FROM users
        WHERE uid = ?
        LIMIT 1
    ");
    $stmt->execute([$uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    $user = null;
}

// Initialise arrays for storing user interaction data
$watchlistedAnime = [];
$likedAnime = [];
$dislikedAnime = [];

// Fetch all user interaction data (watchlist, liked, disliked)
try {

    // Watchlisted anime including watched state for UI toggle
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.main_picture_url, a.mean, i.watched
        FROM interactions i
        JOIN anime a ON i.anime_id = a.id
        WHERE i.uid = ? AND i.watchlisted = 1
        ORDER BY a.title
    ");
    $stmt->execute([$uid]);
    $watchlistedAnime = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Liked anime entries
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.main_picture_url, a.mean
        FROM interactions i
        JOIN anime a ON i.anime_id = a.id
        WHERE i.uid = ? AND i.liked = 1
        ORDER BY a.title
    ");
    $stmt->execute([$uid]);
    $likedAnime = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Disliked anime entries
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.main_picture_url, a.mean
        FROM interactions i
        JOIN anime a ON i.anime_id = a.id
        WHERE i.uid = ? AND i.disliked = 1
        ORDER BY a.title
    ");
    $stmt->execute([$uid]);
    $dislikedAnime = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Fallback to empty datasets if query fails
    $watchlistedAnime = [];
    $likedAnime = [];
    $dislikedAnime = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="account.css?v=<?php echo time(); ?>">
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
        <a href="HomeUser.php">Home</a>
        <a href="RankingPage.php">Top Anime</a>
        <a href="GenrePage.php">Genres</a>
    </div>
</div>

<main class="account-page">

    <!-- Success feedback messages for account updates -->
    <?php if (isset($_SESSION["success_d"])): ?>
        <div style="display:flex; justify-content:center; margin-bottom:18px;">
            <div style="background:green; padding:15px 30px; color:white; border-radius:5px;">
                <?php echo htmlspecialchars($_SESSION["success_d"]); unset($_SESSION["success_d"]); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION["success_p"])): ?>
        <div style="display:flex; justify-content:center; margin-bottom:18px;">
            <div style="background:green; padding:15px 30px; color:white; border-radius:5px;">
                <?php echo htmlspecialchars($_SESSION["success_p"]); unset($_SESSION["success_p"]); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hidden container for dynamic interaction messages (handled via JS) -->
    <div id="interactionMessageWrap" style="display:none; position:fixed; top:90px; left:50%; transform:translateX(-50%); z-index:9999;">
        <div id="interactionMessageBox"></div>
    </div>

    <!-- Account details panel -->
    <section class="account-panel">
        <h1 class="panel-title">My Account</h1>

        <?php if ($user): ?>
            <div class="details-grid">
                <div class="detail-box"><span>Username</span><span><?php echo htmlspecialchars($user["username"]); ?></span></div>
                <div class="detail-box"><span>First Name</span><span><?php echo htmlspecialchars($user["first_name"]); ?></span></div>
                <div class="detail-box"><span>Last Name</span><span><?php echo htmlspecialchars($user["last_name"]); ?></span></div>
                <div class="detail-box"><span>Email</span><span><?php echo htmlspecialchars($user["email"]); ?></span></div>
                <div class="detail-box"><span>Joined</span><span><?php echo htmlspecialchars($user["created_at"]); ?></span></div>
            </div>
        <?php else: ?>
            <p class="empty-text">Unable to load account information.</p>
        <?php endif; ?>

        <!-- Account management actions -->
        <div class="account-actions">
            <a class="action-btn" href="details.php">Update Details</a>
            <a class="action-btn" href="password.php">Update Password</a>

            <form method="post">
                <button class="action-btn" name="logout">Log Out</button>
            </form>

            <form method="post" action="deletion.php">
                <button class="action-btn danger-btn">Delete Account</button>
            </form>
        </div>
    </section>

    <!-- Main grid displaying interaction categories -->
    <div class="anime-sections-grid">

        <!-- Watchlist section with watched toggle -->
        <section class="account-panel anime-section-panel">
            <h2 class="panel-title">Watchlisted Anime</h2>

            <?php if (!empty($watchlistedAnime)): ?>
                <div class="anime-list">
                    <?php foreach ($watchlistedAnime as $anime): ?>
                        <div class="anime-row" data-id="<?php echo (int)$anime["id"]; ?>">

                            <div class="anime-meta">
                                <img class="anime-thumb" src="<?php echo htmlspecialchars($anime["main_picture_url"]); ?>">
                                <div class="anime-text">
                                    <a class="anime-link" href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>">
                                        <?php echo htmlspecialchars($anime["title"]); ?>
                                    </a>
                                    <span>Mean: <?php echo htmlspecialchars($anime["mean"]); ?></span>
                                </div>
                            </div>

                            <div class="anime-row-actions">
                                <button class="mini-btn watched-btn <?php echo !empty($anime["watched"]) ? "watched-active" : ""; ?>">
                                    Watched
                                </button>

                                <button class="mini-btn danger-mini-btn remove-btn" data-action="remove_watchlist">
                                    Remove
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No watchlisted anime yet.</p>
            <?php endif; ?>
        </section>

        <!-- Liked anime section -->
        <section class="account-panel anime-section-panel">
            <h2 class="panel-title">Liked Anime</h2>

            <?php if (!empty($likedAnime)): ?>
                <div class="anime-list">
                    <?php foreach ($likedAnime as $anime): ?>
                        <div class="anime-row" data-id="<?php echo (int)$anime["id"]; ?>">
                            <div class="anime-meta">
                                <img class="anime-thumb" src="<?php echo htmlspecialchars($anime["main_picture_url"]); ?>">
                                <div class="anime-text">
                                    <a href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>">
                                        <?php echo htmlspecialchars($anime["title"]); ?>
                                    </a>
                                    <span>Mean: <?php echo htmlspecialchars($anime["mean"]); ?></span>
                                </div>
                            </div>

                            <div class="anime-row-actions single-action">
                                <button class="mini-btn danger-mini-btn remove-btn" data-action="remove_like">
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No liked anime yet.</p>
            <?php endif; ?>
        </section>

        <!-- Disliked anime section -->
        <section class="account-panel anime-section-panel">
            <h2 class="panel-title">Disliked Anime</h2>

            <?php if (!empty($dislikedAnime)): ?>
                <div class="anime-list">
                    <?php foreach ($dislikedAnime as $anime): ?>
                        <div class="anime-row" data-id="<?php echo (int)$anime["id"]; ?>">
                            <div class="anime-meta">
                                <img class="anime-thumb" src="<?php echo htmlspecialchars($anime["main_picture_url"]); ?>">
                                <div class="anime-text">
                                    <a href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>">
                                        <?php echo htmlspecialchars($anime["title"]); ?>
                                    </a>
                                    <span>Mean: <?php echo htmlspecialchars($anime["mean"]); ?></span>
                                </div>
                            </div>

                            <div class="anime-row-actions single-action">
                                <button class="mini-btn danger-mini-btn remove-btn" data-action="remove_dislike">
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No disliked anime yet.</p>
            <?php endif; ?>
        </section>

    </div>
</main>

<!-- External JS handles interaction updates -->
<script src="interactions.js?v=<?php echo time(); ?>"></script>

</body>
</html>