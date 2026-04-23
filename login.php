<?php
// Start session
session_start();

// Handle login form submission
if (isset($_POST["login"])) {

    // Check that both email and password fields have been filled in
    if (!isset($_POST["email"], $_POST["password"]) || trim($_POST["email"]) === "" || trim($_POST["password"]) === "") {
        $_SESSION["failedlogin"] = "Please fill in both the email and password fields.";
        header("Location: login.php");
        exit;
    }

    // Include database connection once form input has passed basic validation
    require_once __DIR__ . "/AnimeenDbConn.php";

    try {
        // Retrieve the account matching the entered email address
        $stmt = $pdo->prepare("
            SELECT uid, username, email, password
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([trim($_POST["email"])]);
        $user = $stmt->fetch();

        // Verify that the user exists and the password is correct
        if ($user) {
            if (password_verify($_POST["password"], $user["password"])) {

                // Store user details in session and redirect to the logged-in homepage
                $_SESSION["uid"] = $user["uid"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["loggedin"] = "Welcome," . $user["username"] . "!";

                header("Location: HomeUser.php");
                exit;
            } else {
                // Display error if password does not match the stored hash
                $_SESSION["failedlogin"] = "Incorrect password. Please try again or click forgotten password.";
                header("Location: login.php");
                exit;
            }
        } else {
            // Display error if no account matches the entered email
            $_SESSION["failedlogin2"] = "Incorrect email. Please try again.";
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $ex) {
        // Display system-level error if database connection or query fails
        $_SESSION["systemfailure"] = "Failed to connect to the database.";
        header("Location: login.php");
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
    <title>Login</title>
    <link rel="stylesheet" href="Login.css">
</head>

<body>

    <!-- Background video -->
    <section>
        <video src="video/konoha.mp4" loop muted autoplay></video>
    </section>

    <!-- Navigation bar -->
    <div class="static-control-bar">
        <div class="logo">Animeen</div>
        <div class="nav-links">
            <a href="Home.php">Home</a>
            <a href="RankingPage.php">Top Anime</a>
            <a href="GenrePage.php">Genres</a>
        </div>
    </div>

    <!-- Error message shown when required login fields are left empty -->
    <?php if (isset($_SESSION["failedlogin"])): ?>
        <div style="display:flex; justify-content:center; align-items:center; margin-top:90px; position:relative; z-index:20;">
            <div style="background-color:red; padding:15px 30px; color:white; border:1px solid red; font-weight:bold; border-radius:5px; text-align:center;">
                <?php
                echo htmlspecialchars($_SESSION["failedlogin"]);
                unset($_SESSION["failedlogin"]);
                ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Error message shown when the email is not recognised -->
    <?php if (isset($_SESSION["failedlogin2"])): ?>
        <div style="display:flex; justify-content:center; align-items:center; margin-top:90px; position:relative; z-index:20;">
            <div style="background-color:red; padding:15px 30px; color:white; border:1px solid red; font-weight:bold; border-radius:5px; text-align:center;">
                <?php
                echo htmlspecialchars($_SESSION["failedlogin2"]);
                unset($_SESSION["failedlogin2"]);
                ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Error message shown if the system cannot access the database -->
    <?php if (isset($_SESSION["systemfailure"])): ?>
        <div style="display:flex; justify-content:center; align-items:center; margin-top:90px; position:relative; z-index:20;">
            <div style="background-color:red; padding:15px 30px; color:white; border:1px solid red; font-weight:bold; border-radius:5px; text-align:center;">
                <?php
                echo htmlspecialchars($_SESSION["systemfailure"]);
                unset($_SESSION["systemfailure"]);
                ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Success message shown after successful registration -->
    <?php if (isset($_SESSION["registration_success"])): ?>
        <div style="display:flex; justify-content:center; align-items:center; margin-top:90px; position:relative; z-index:20;">
            <div style="background-color:green; padding:15px 30px; color:white; border:1px solid green; font-weight:bold; border-radius:5px; text-align:center;">
                <?php
                echo htmlspecialchars($_SESSION["registration_success"]);
                unset($_SESSION["registration_success"]);
                ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main login form section -->
    <main class="login-page">
        <div class="login-card">

            <!-- Form used to authenticate the user -->
            <form class="login-form" method="post" action="login.php">
                <h2>Login</h2>

                <div class="input-field">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="text" id="email" name="email" required>
                    <label for="email">Email</label>
                </div>

                <div class="input-field">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" id="password" name="password" required>
                    <label for="password">Password</label>
                </div>

                <!-- Link provided for users who need to recover or change password -->
                <div class="forget">
                    <a href="password.php">Forgotten password?</a>
                </div>

                <button class="input-button" type="submit" name="login">
                    Login
                </button>

                <!-- Redirects unregistered users to the registration page -->
                <div class="register">
                    <p>Not already registered?
                        <a href="RegistrationPage.php">Register here</a>
                    </p>
                </div>
            </form>

        </div>
    </main>
    <script src="Home.js"></script>
</body>
</html>