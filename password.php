<?php
// Initialise session and database connection
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

// Restrict access to logged-in users only if not logged in they are sent to the home page
if (!isset($_SESSION["uid"])) {
    header("Location: Home.php");
    exit;
}

$uid = (int) $_SESSION["uid"];
$error = "";
$success = "";

// Handle password update form submission
if (isset($_POST["save_password"])) {
    $currentPassword = trim($_POST["current_password"] ?? "");
    $newPassword = trim($_POST["new_password"] ?? "");
    $confirmPassword = trim($_POST["confirm_password"] ?? "");

    // Validate password fields before checking against the database
    if ($currentPassword === "" || $newPassword === "" || $confirmPassword === "") {
        $error = "Please fill in all password fields.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirm password do not match.";
    } elseif (strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        try {
            // Retrieve the current stored password for the logged-in user
            $stmt = $pdo->prepare("
                SELECT password
                FROM users
                WHERE uid = ?
                LIMIT 1
            ");
            $stmt->execute([$uid]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check that the user exists and the current password is correct
            if (!$user) {
                $error = "User not found.";
            } elseif (!password_verify($currentPassword, $user["password"])) {
                $error = "Current password is incorrect.";
            } else {
                // Hash the new password and update it in the database
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET password = ?
                    WHERE uid = ?
                    LIMIT 1
                ");
                $stmt->execute([$hashedPassword, $uid]);

                // Store success message and redirect back to account page
                $_SESSION["success_p"] = "Password updated successfully.";
                header("Location: account.php");
                exit;
            }
        } catch (PDOException $ex) {
            $error = "Failed to update password. Please try again.";
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
    <title>Update Password</title>
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
        <a href="account.php">Account</a>
    </div>
</div>

<main class="account-page">
    <!-- Main panel containing the password update form -->
    <section class="account-panel" style="max-width: 800px; margin: 0 auto;">
        <h1 class="panel-title">Update Password</h1>

        <!-- Displays an error message if validation or update fails -->
        <?php if ($error !== ""): ?>
            <div style="display:flex; justify-content:center; align-items:center; margin-bottom:18px;">
                <div style="background-color:#c0392b; padding:15px 30px; color:white; border:1px solid #c0392b; font-weight:bold; border-radius:5px; text-align:center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form allowing the user to change their password -->
        <form method="post" style="display:grid; gap:16px;">
            <div class="detail-box">
                <span class="detail-label">Current Password</span>
                <input type="password" name="current_password" style="padding:12px; border-radius:12px; border:none; outline:none;">
            </div>

            <div class="detail-box">
                <span class="detail-label">New Password</span>
                <input type="password" name="new_password" style="padding:12px; border-radius:12px; border:none; outline:none;">
            </div>

            <div class="detail-box">
                <span class="detail-label">Confirm New Password</span>
                <input type="password" name="confirm_password" style="padding:12px; border-radius:12px; border:none; outline:none;">
            </div>

            <!-- Form actions allowing the user to save or cancel -->
            <div class="account-actions">
                <button class="action-btn" type="submit" name="save_password">Save Password</button>
                <a class="action-btn" href="account.php">Cancel</a>
            </div>
        </form>
    </section>
</main>

</body>
</html>