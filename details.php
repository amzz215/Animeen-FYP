<?php
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

if (!isset($_SESSION["uid"])) {
    header("Location: Home.php");
    exit;
}

$uid = (int) $_SESSION["uid"];
$error = "";
$success = "";
$user = null;

try {
    $stmt = $pdo->prepare("
        SELECT username, first_name, last_name, email
        FROM users
        WHERE uid = ?
        LIMIT 1
    ");
    $stmt->execute([$uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: Home.php");
        exit;
    }
} catch (PDOException $ex) {
    $error = "Unable to load your details.";
}

if (isset($_POST["save_details"])) {
    $username = trim($_POST["username"] ?? "");
    $firstName = trim($_POST["first_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($username === "" || $firstName === "" || $lastName === "" || $email === "") {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT uid
                FROM users
                WHERE (username = ? OR email = ?) AND uid != ?
                LIMIT 1
            ");
            $stmt->execute([$username, $email, $uid]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $error = "That username or email is already in use.";
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET username = ?, first_name = ?, last_name = ?, email = ?
                    WHERE uid = ?
                    LIMIT 1
                ");
                $stmt->execute([$username, $firstName, $lastName, $email, $uid]);

                $_SESSION["success_d"] = "Details updated successfully.";
                header("Location: account.php");
                exit;

                $stmt = $pdo->prepare("
                    SELECT username, first_name, last_name, email
                    FROM users
                    WHERE uid = ?
                    LIMIT 1
                ");
                $stmt->execute([$uid]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $ex) {
            $error = "Failed to update details. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Details</title>
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
        <section class="account-panel" style="max-width: 800px; margin: 0 auto;">
            <h1 class="panel-title">Update Details</h1>

            <?php if ($error !== ""): ?>
                <div style="display:flex; justify-content:center; align-items:center; margin-bottom:18px;">
                    <div style="background-color:#c0392b; padding:15px 30px; color:white; border:1px solid #c0392b; font-weight:bold; border-radius:5px; text-align:center;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="post" style="display:grid; gap:16px;">
                <div class="detail-box">
                    <span class="detail-label">Username</span>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user["username"] ?? ""); ?>" style="padding:12px; border-radius:12px; border:none; outline:none;">
                </div>

                <div class="detail-box">
                    <span class="detail-label">First Name</span>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user["first_name"] ?? ""); ?>" style="padding:12px; border-radius:12px; border:none; outline:none;">
                </div>

                <div class="detail-box">
                    <span class="detail-label">Last Name</span>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user["last_name"] ?? ""); ?>" style="padding:12px; border-radius:12px; border:none; outline:none;">
                </div>

                <div class="detail-box">
                    <span class="detail-label">Email</span>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user["email"] ?? ""); ?>" style="padding:12px; border-radius:12px; border:none; outline:none;">
                </div>

                <div class="account-actions">
                    <button class="action-btn" type="submit" name="save_details">Save Changes</button>
                    <a class="action-btn" href="account.php">Cancel</a>
                </div>
            </form>
        </section>
    </main>

</body>
</html>