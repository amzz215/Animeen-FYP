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
            <a href="#">Genres</a>
            <a href="#">About</a>
        </div>
    </div>

    <!-- Login content -->
    <main class="login-page">
        <div class="login-card">

            <form class="login-form" method="post" action="ffLoginPage.php">
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

                <div class="forget">
                    <a href="password.php">Forgotten password?</a>
                </div>

                <button class="input-button" type="submit" name="login">
                    Login
                </button>

                <div class="register">
                    <p>Not already registered?
                        <a href="ffRegistrationForm.php">Register here</a>
                    </p>
                    <p>
                        <a href="ffLoginPageAdministrator.php">Administrator?</a>
                    </p>
                </div>
            </form>

        </div>
    </main>

    <script src="Home.js"></script>
</body>
</html>
