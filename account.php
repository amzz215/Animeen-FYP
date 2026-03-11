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

$watchlistedAnime = [
    ["id" => 1, "title" => "Violet Evergarden", "studio" => "Kyoto Animation"],
    ["id" => 2, "title" => "Your Name", "studio" => "CoMix Wave Films"],
    ["id" => 3, "title" => "Spirited Away", "studio" => "Studio Ghibli"]
];

$likedAnime = [
    ["id" => 4, "title" => "Attack on Titan", "studio" => "Wit Studio"],
    ["id" => 5, "title" => "Steins;Gate", "studio" => "White Fox"],
    ["id" => 6, "title" => "Haikyuu!!", "studio" => "Production I.G"]
];

$dislikedAnime = [
    ["id" => 7, "title" => "DB", "studio" => "Studio A"],
    ["id" => 8, "title" => "Naruto", "studio" => "Studio B"],
    ["id" => 9, "title" => "One Piece", "studio" => "Studio C"]
];
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
            <a href="account.php">Account</a>
            <a href="#">About</a>
        </div>
    </div>

<main class="account-page">

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
            <a class="action-btn" href="UpdateDetails.php">Update Details</a>
            <a class="action-btn" href="UpdatePassword.php">Update Password</a>

            <form method="post">
                <button class="action-btn" type="submit" name="logout">Log Out</button>
            </form>

            <a class="action-btn danger-btn" href="DeleteAccount.php">Delete Account</a>
        </div>
    </section>

    <div class="anime-sections-grid">

        <section class="account-panel anime-section-panel">
            <h2 class="panel-title">Watchlisted Anime</h2>
            <div class="anime-list">
                <?php foreach ($watchlistedAnime as $anime): ?>
                    <div class="anime-row">
                        <div class="anime-meta">
                            <a class="anime-link" href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>">
                                <?php echo htmlspecialchars($anime["title"]); ?>
                            </a>
                            <span class="anime-sub"><?php echo htmlspecialchars($anime["studio"]); ?></span>
                        </div>

                        <div class="anime-row-actions">
                            <button class="mini-btn" type="button">Watched</button>
                            <button class="mini-btn danger-mini-btn" type="button">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="account-panel anime-section-panel">
            <h2 class="panel-title">Liked Anime</h2>
            <div class="anime-list">
                <?php foreach ($likedAnime as $anime): ?>
                    <div class="anime-row">
                        <div class="anime-meta">
                            <a class="anime-link" href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>">
                                <?php echo htmlspecialchars($anime["title"]); ?>
                            </a>
                            <span class="anime-sub"><?php echo htmlspecialchars($anime["studio"]); ?></span>
                        </div>

                        <div class="anime-row-actions">
                            <button class="mini-btn" type="button">Watched</button>
                            <button class="mini-btn danger-mini-btn" type="button">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="account-panel anime-section-panel">
            <h2 class="panel-title">Disliked Anime</h2>
            <div class="anime-list">
                <?php foreach ($dislikedAnime as $anime): ?>
                    <div class="anime-row">
                        <div class="anime-meta">
                            <a class="anime-link" href="AnimeInfo.php?anime=<?php echo (int)$anime["id"]; ?>">
                                <?php echo htmlspecialchars($anime["title"]); ?>
                            </a>
                            <span class="anime-sub"><?php echo htmlspecialchars($anime["studio"]); ?></span>
                        </div>

                        <div class="anime-row-actions">
                            <button class="mini-btn" type="button">Watched</button>
                            <button class="mini-btn danger-mini-btn" type="button">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>

</main>

</body>
</html>