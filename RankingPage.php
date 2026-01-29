<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Anime</title>

    <link rel="stylesheet" href="RankingPage.css">
</head>

<body>

    <!-- Background video -->
    <section>
        <video src="video/naruto.mp4" loop muted autoplay></video>
    </section>

    <!-- Navigation bar -->
    <div class="static-control-bar">
        <div class="logo">Animeen</div>
        <div class="nav-links">
            <a href="Home.php">Home</a>
            <a href="login.php">Login</a>
            <a href="GenrePage.php">Genres</a>
            <a href="#">About</a>
        </div>
    </div>
<main class="page">
    <h1 class="page-title">Browse Highest Rated Anime</h1>

    <div class="grid">
      <!-- Card -->
      <article class="anime-card" data-id="1">
        <div class="poster">
          <img src="images/wolf-children.jpg" alt="Ookami Kodomo no Ame to Yuki">
        </div>

        <div class="meta">
          <h3 class="title">Ookami Kodomo no Ame to Yuki</h3>
          <p class="sub">Madhouse • 2012</p>
        </div>

        <div class="actions">
          <button class="btn btn-watchlist" type="button">+ Watchlist</button>
          <button class="btn btn-like" type="button" aria-label="Like">👍</button>
          <button class="btn btn-dislike" type="button" aria-label="Dislike">👎</button>
        </div>
      </article>

      <!-- Duplicate cards (example) -->
      <article class="anime-card" data-id="2">
        <div class="poster">
          <img src="video/spiderman.jpg" alt="spiderman">
        </div>
        <div class="meta">
          <h3 class="title">Your Name</h3>
          <p class="sub">CoMix Wave • 2016</p>
        </div>
        <div class="actions">
          <button class="btn btn-watchlist" type="button">+ Watchlist</button>
          <button class="btn btn-like" type="button" aria-label="Like">👍</button>
          <button class="btn btn-dislike" type="button" aria-label="Dislike">👎</button>
        </div>
      </article>

      <article class="anime-card" data-id="3">
        <div class="poster">
          <img src="images/spirited-away.jpg" alt="Spirited Away">
        </div>
        <div class="meta">
          <h3 class="title">Spirited Away</h3>
          <p class="sub">Ghibli • 2001</p>
        </div>
        <div class="actions">
          <button class="btn btn-watchlist" type="button">+ Watchlist</button>
          <button class="btn btn-like" type="button" aria-label="Like">👍</button>
          <button class="btn btn-dislike" type="button" aria-label="Dislike">👎</button>
        </div>
      </article>
    </div>
  </main>
    <script src="RankingPage.js"></script>
</body>
</html>
