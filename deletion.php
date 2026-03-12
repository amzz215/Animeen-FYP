<?php
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

if (!isset($_SESSION["uid"])) {
    header("Location: Home.php");
    exit;
}

$uid = (int) $_SESSION["uid"];

if (isset($_POST["confirm_delete"])) {
    try {

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            DELETE FROM interactions
            WHERE uid = ?
        ");
        $stmt->execute([$uid]);

        $stmt = $pdo->prepare("
            DELETE FROM users
            WHERE uid = ?
            LIMIT 1
        ");
        $stmt->execute([$uid]);

        $pdo->commit();

        $_SESSION["success"] = "Your account has been permanently deleted.";

        session_unset();
        session_destroy();

        session_start();
        $_SESSION["success"] = "Your account has been permanently deleted.";

        header("Location: Home.php");
        exit;

    } catch (PDOException $ex) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION["error"] = "Failed to delete account. Please try again.";
        header("Location: deletion.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>
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
        </div>
    </div>

    <main class="account-page">
        <section class="account-panel" style="max-width: 700px; margin: 0 auto;">
            <h1 class="panel-title">Delete Account</h1>

            <p class="empty-text" style="margin-bottom: 18px;">
                This will permanently delete your account and all of your anime interactions.
            </p>

            <?php if (isset($_SESSION["error"])): ?>
            <div style="display:flex; justify-content:center; align-items:center; margin-bottom:18px;">
                <div style="background-color:#c0392b; padding:15px 30px; color:white; border:1px solid #c0392b; font-weight:bold; border-radius:5px; text-align:center;">
                    <?php
                    echo htmlspecialchars($_SESSION["error"]);
                    unset($_SESSION["error"]);
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="account-actions">
                <form method="post">
                    <button class="action-btn danger-btn" type="submit" name="confirm_delete">
                        Yes, Delete My Account
                    </button>
                </form>

                <a class="action-btn" href="account.php">Cancel</a>
            </div>
        </section>
    </main>

</body>
</html>