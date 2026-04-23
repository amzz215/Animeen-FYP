<?php
// Start session
session_start();

// Handle registration form submission
if (isset($_POST["register"])) {

    // Check that all required registration fields have been submitted
    if (!isset($_POST["email"], $_POST["username"], $_POST["first_name"], $_POST["last_name"], $_POST["password"])) {
        $_SESSION["failedregistration"] = "Please fill in all registration fields.";
        header("Location: RegistrationPage.php");
        exit;
    }

    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $password = $_POST["password"];

    // Validate email format before attempting to register the user
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["failedregistration"] = "Invalid Email address.";
        header("Location: RegistrationPage.php");
        exit;
    }

    // Database connection 
    require_once __DIR__ . "/AnimeenDbConn.php";

    try {

        // Check whether the email or username is already being used
        $check = $pdo->prepare("SELECT uid FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);

        if ($check->rowCount() > 0) {
            $_SESSION["failedregistration2"] = "Email or Username already exists.";
            header("Location: RegistrationPage.php");
            exit;
        }

        // Hash the password before saving the new user to the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the users table
        $stat = $pdo->prepare("
            INSERT INTO users (username, first_name, last_name, password, email, created_at)
            VALUES (:username, :first_name, :last_name, :password, :email, NOW())
        ");

        $stat->bindParam(':username', $username);
        $stat->bindParam(':first_name', $first_name);
        $stat->bindParam(':last_name', $last_name);
        $stat->bindParam(':password', $hashed_password);
        $stat->bindParam(':email', $email);

        $stat->execute();

        // Store newly created user details in session and log them in immediately
        $uid = $pdo->lastInsertId();

        $_SESSION["uid"] = $uid;
        $_SESSION["username"] = $username;
        $_SESSION["email"] = $email;
        $_SESSION["loggedin"] = "Welcome $username! Your account has been created.";

        header("Location: HomeUser.php");
        exit;

    } catch (PDOException $ex) {
        // Display a system error if registration cannot be completed
        $_SESSION["systemfailure"] = "Database error occurred.";
        header("Location: RegistrationPage.php");
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
    <title>Register</title>
    <link rel="stylesheet" href="RegistrationPage.css">
</head>

<body>

<!-- Background video -->
<section>
    <video src="video/naruto.mp4" loop muted autoplay></video>
</section>

<!-- Top nav -->
<div class="static-control-bar">
    <div class="logo">Animeen</div>
    <div class="nav-links">
        <a href="Home.php">Home</a>
        <a href="RankingPage.php">Top Anime</a>
        <a href="GenrePage.php">Genres</a>
        <a href="login.php">Login</a>
    </div>
</div>

<!-- Error message shown if registration fields are incomplete or email format is invalid -->
<?php if (isset($_SESSION["failedregistration"])): ?>
    <div style="display:flex; justify-content:center; align-items:center; margin-top:90px; position:relative; z-index:20;">
        <div style="background-color:red; padding:15px 30px; color:white; border:1px solid red; font-weight:bold; border-radius:5px; text-align:center;">
            <?php
            echo htmlspecialchars($_SESSION["failedregistration"]);
            unset($_SESSION["failedregistration"]);
            ?>
        </div>
    </div>
<?php endif; ?>

<!-- Error message shown if the username or email already exists -->
<?php if (isset($_SESSION["failedregistration2"])): ?>
    <div style="display:flex; justify-content:center; align-items:center; margin-top:90px; position:relative; z-index:20;">
        <div style="background-color:red; padding:15px 30px; color:white; border:1px solid red; font-weight:bold; border-radius:5px; text-align:center;">
            <?php
            echo htmlspecialchars($_SESSION["failedregistration2"]);
            unset($_SESSION["failedregistration2"]);
            ?>
        </div>
    </div>
<?php endif; ?>

<!-- Error message shown if a system or database issue occurs -->
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

<!-- Main registration form section -->
<main class="auth-page">
    <div class="auth-card">

        <h2>Create Account</h2>

        <!-- Form allowing new users to create an account -->
        <form method="post" action="RegistrationPage.php" class="auth-form">
            <div class="input-field">
                <input type="email" name="email" required>
                <label>Email</label>
            </div>

            <div class="input-field">
                <input type="text" name="username" required>
                <label>Username</label>
            </div>

            <div class="input-field">
                <input type="text" name="first_name" required>
                <label>First Name</label>
            </div>

            <div class="input-field">
                <input type="text" name="last_name" required>
                <label>Last Name</label>
            </div>

            <div class="input-field">
                <input type="password" name="password" required>
                <label>Password</label>
            </div>

            <button class="input-button" type="submit" name="register">
                Register
            </button>
        </form>

        <!-- Link for users who already have an account -->
        <p class="switch-link">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>

    </div>
</main>

</body>
</html>
