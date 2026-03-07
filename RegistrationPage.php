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

<!-- Page -->
<main class="auth-page">
    <div class="auth-card">

        <h2>Create Account</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post" class="auth-form">
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

        <p class="switch-link">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>

    </div>
</main>

</body>
</html>
