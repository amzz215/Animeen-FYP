<?php
// Initialise session and database connection
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

$error = "";

// Handle forgotten password update form submission
if (isset($_POST["save_password"])) {
    $email = trim($_POST["email"] ?? "");
    $newPassword = trim($_POST["new_password"] ?? "");
    $confirmPassword = trim($_POST["confirm_password"] ?? "");

    // Validate fields before updating the database
    if ($email === "" || $newPassword === "" || $confirmPassword === "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirm password do not match.";
    } elseif (strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        try {
            // Check that an account exists with the entered email
            $stmt = $pdo->prepare("
                SELECT uid
                FROM users
                WHERE email = ?
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = "No account was found with that email address.";
            } else {
                // Hash the new password and update it for the matching account
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET password = ?
                    WHERE uid = ?
                    LIMIT 1
                ");
                $stmt->execute([$hashedPassword, (int)$user["uid"]]);

                // Send user back to login page once password has been reset
                $_SESSION["registration_success"] = "Password updated successfully. You can now log in.";
                header("Location: login.php");
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
    <title>Update Forgotten Password</title>
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
        <a href="Home.php">Home</a>
        <a href="RankingPage.php">Top Anime</a>
        <a href="GenrePage.php">Genres</a>
        <a href="login.php">Login</a>
        <a href="RegistrationPage.php">Register</a>
    </div>
</div>

<main class="account-page">
    <!-- Main panel containing the forgotten password form -->
    <section class="account-panel" style="max-width: 800px; margin: 0 auto;">
        <h1 class="panel-title">Update Forgotten Password</h1>

        <!-- Displays an error message if validation or update fails -->
        <?php if ($error !== ""): ?>
            <div style="display:flex; justify-content:center; align-items:center; margin-bottom:18px;">
                <div style="background-color:#c0392b; padding:15px 30px; color:white; border:1px solid #c0392b; font-weight:bold; border-radius:5px; text-align:center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form allowing the user to reset their password using their email -->
        <form method="post" style="display:grid; gap:16px;">
            <div class="detail-box">
                <span class="detail-label">Email</span>
                <input type="email" name="email" style="padding:12px; border-radius:12px; border:none; outline:none;">
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
                <a class="action-btn" href="login.php">Cancel</a>
            </div>
        </form>
    </section>
</main>

</body>
</html>