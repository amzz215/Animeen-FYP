<?php
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

if (!isset($_SESSION["uid"])) {
    header("Location: Home.php");
    exit;
}

$uid = $_SESSION["uid"];
$user = null;

if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();

    session_start();
    $_SESSION["success_l"] = "You have been logged out successfully.";

    header("Location: Home.php");
    exit;
}

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

$watchlistedAnime = [];
$likedAnime = [];
$dislikedAnime = [];

try {
        /* WATCHLISTED */
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.main_picture_url, a.mean, i.watched
        FROM interactions i
        JOIN anime a ON i.anime_id = a.id
        WHERE i.uid = ? AND i.watchlisted = 1
        ORDER BY a.title
    ");
    $stmt->execute([$uid]);
    $watchlistedAnime = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /* LIKED */
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.main_picture_url, a.mean
        FROM interactions i
        JOIN anime a ON i.anime_id = a.id
        WHERE i.uid = ? AND i.liked = 1
        ORDER BY a.title
    ");
    $stmt->execute([$uid]);
    $likedAnime = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /* DISLIKED */
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
    $watchlistedAnime = [];
    $likedAnime = [];
    $dislikedAnime = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="account.css?v=<?php echo time(); ?>">
</head>
<body>

<section class="video-background">
    <video src="video/kame-house.mp4" loop muted autoplay></video>
</section>

<div class="static-control-bar">
    <div class="logo">Animeen</div>
    <div class="nav-links">
        <a href="HomeUser.php">Home</a>
        <a href="RankingPage.php">Top Anime</a>
        <a href="GenrePage.php">Genres</a>
    </div>
</div>

<main class="account-page">

    <?php if (isset($_SESSION["success_d"])): ?>
    <div style="display:flex; justify-content:center; align-items:center; margin-bottom:18px;">
        <div style="background-color:green; padding:15px 30px; color:white; border:1px solid green; font-weight:bold; border-radius:5px; text-align:center;">
            <?php
            echo htmlspecialchars($_SESSION["success_d"]);
            unset($_SESSION["success_d"]);
            ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION["success_p"])): ?>
    <div style="display:flex; justify-content:center; align-items:center; margin-bottom:18px;">
        <div style="background-color:green; padding:15px 30px; color:white; border:1px solid green; font-weight:bold; border-radius:5px; text-align:center;">
            <?php
            echo htmlspecialchars($_SESSION["success_p"]);
            unset($_SESSION["success_p"]);
            ?>
        </div>
    </div>
    <?php endif; ?>

    <div id="interactionMessageWrap"
         style="display:none; position:fixed; top:90px; left:50%; transform:translateX(-50%); z-index:9999; justify-content:center; align-items:center;">
        <div id="interactionMessageBox"
             style="padding:15px 30px; color:white; font-weight:bold; border-radius:5px; text-align:center; min-width:250px;">
        </div>
    </div>

    <section class="account-panel">
        <h1 class="panel-title">My Account</h1>

        <?php if ($user): ?>
            <div class="details-grid">
                <div class="detail-box">
                    <span class="detail-label">Username</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user["username"]); ?></span>
                </div>

                <div class="detail-box">
                    <span class="detail-label">First Name</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user["first_name"]); ?></span>
                </div>

                <div class="detail-box">
                    <span class="detail-label">Last Name</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user["last_name"]); ?></span>
                </div>

                <div class="detail-box">
                    <span class="detail-label">Email</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user["email"]); ?></span>
                </div>

                <div class="detail-box">
                    <span class="detail-label">Joined</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user["created_at"]); ?></span>
                </div>
            </div>
        <?php else: ?>
            <p class="empty-text">Unable to load account information.</p>
        <?php endif; ?>

        <div class="account-actions">
            <a class="action-btn" href="details.php">Update Details</a>
            <a class="action-btn" href="password.php">Update Password</a>

            <form method="post">
                <button class="action-btn" type="submit" name="logout">Log Out</button>
            </form>

            <form method="post" action="deletion.php">
                <button class="action-btn danger-btn" type="submit" name="go_to_delete">Delete Account</button>
            </form>
        </div>
    </section>

    <div class="anime-sections-grid">

        <section class="account-panel anime-section-panel">
            <h2 class="panel-title">Watchlisted Anime</h2>

            <?php if (!empty($watchlistedAnime)): ?>
                <div class="anime-list">
                    <?php foreach ($watchlistedAnime as $anime): ?>
                        <div class="anime-row" data-id="<?php echo (int)$anime["id"]; ?>">
                            <div class="anime-meta">
                                <img
                                    class="anime-thumb"
                                    src="<?php echo htmlspecialchars(!empty($anime["main_picture_url"]) ? $anime["main_picture_url"] : "images/placeholder1.jpg"); ?>"
                                    alt="<?php echo htmlspecialchars($anime["title"]); ?>"
                                >

                                <div class="anime-text">
                                    <a class="anime-link" href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>">
                                        <?php echo htmlspecialchars($anime["title"]); ?>
                                    </a>
                                    <span class="anime-sub">Mean: <?php echo htmlspecialchars((string)($anime["mean"] ?? "N/A")); ?></span>
                                </div>
                            </div>

                            <div class="anime-row-actions">
                                <button class="mini-btn watched-btn <?php echo !empty($anime["watched"]) ? "watched-active" : ""; ?>" type="button">
                                    Watched
                                </button>

                                <button class="mini-btn danger-mini-btn remove-btn" type="button" data-action="remove_watchlist">
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-text">No watchlisted anime yet.</p>
            <?php endif; ?>
        </section>

        <section class="account-panel anime-section-panel">
            <h2 class="panel-title">Liked Anime</h2>

            <?php if (!empty($likedAnime)): ?>
                <div class="anime-list">
                    <?php foreach ($likedAnime as $anime): ?>
                        <div class="anime-row" data-id="<?php echo (int)$anime["id"]; ?>">
                            <div class="anime-meta">
                                <img
                                    class="anime-thumb"
                                    src="<?php echo htmlspecialchars(!empty($anime["main_picture_url"]) ? $anime["main_picture_url"] : "images/placeholder1.jpg"); ?>"
                                    alt="<?php echo htmlspecialchars($anime["title"]); ?>"
                                >

                                <div class="anime-text">
                                    <a class="anime-link" href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>">
                                        <?php echo htmlspecialchars($anime["title"]); ?>
                                    </a>
                                    <span class="anime-sub">Mean: <?php echo htmlspecialchars((string)($anime["mean"] ?? "N/A")); ?></span>
                                </div>
                            </div>

                            <div class="anime-row-actions single-action">
                                <button class="mini-btn danger-mini-btn remove-btn" type="button" data-action="remove_like">
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-text">No liked anime yet.</p>
            <?php endif; ?>
        </section>

        <section class="account-panel anime-section-panel">
            <h2 class="panel-title">Disliked Anime</h2>

            <?php if (!empty($dislikedAnime)): ?>
                <div class="anime-list">
                    <?php foreach ($dislikedAnime as $anime): ?>
                        <div class="anime-row" data-id="<?php echo (int)$anime["id"]; ?>">
                            <div class="anime-meta">
                                <img
                                    class="anime-thumb"
                                    src="<?php echo htmlspecialchars(!empty($anime["main_picture_url"]) ? $anime["main_picture_url"] : "images/placeholder1.jpg"); ?>"
                                    alt="<?php echo htmlspecialchars($anime["title"]); ?>"
                                >

                                <div class="anime-text">
                                    <a class="anime-link" href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>">
                                        <?php echo htmlspecialchars($anime["title"]); ?>
                                    </a>
                                    <span class="anime-sub">Mean: <?php echo htmlspecialchars((string)($anime["mean"] ?? "N/A")); ?></span>
                                </div>
                            </div>

                            <div class="anime-row-actions single-action">
                                <button class="mini-btn danger-mini-btn remove-btn" type="button" data-action="remove_dislike">
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-text">No disliked anime yet.</p>
            <?php endif; ?>
        </section>

    </div>
</main>

<script src="interactions.js?v=<?php echo time(); ?>"></script>
</body>
</html>