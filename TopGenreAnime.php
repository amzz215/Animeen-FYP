<?php
$genre = isset($_GET["genre"]) ? $_GET["genre"] : "unknown";
$genreSafe = htmlspecialchars($genre);
$genreTitle = ucwords(str_replace("-", " ", $genreSafe));

/* Placeholder data — replace with DB when ive completed db */
$animeList = [
  ["id" => 1, "title" => "Example Anime 1", "studio" => "Studio A", "year" => "2014", "img" => "images/placeholder1.jpg"],
  ["id" => 2, "title" => "Example Anime 2", "studio" => "Studio B", "year" => "2018", "img" => "images/placeholder2.jpg"],
  ["id" => 3, "title" => "Example Anime 3", "studio" => "Studio C", "year" => "2021", "img" => "images/placeholder3.jpg"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $genreTitle; ?> — Top Anime</title>

  <link rel="stylesheet" href="TopGenreAnime.css">
</head>

<body>

  <section>
    <video src="video/naruto.mp4" loop muted autoplay></video>
  </section>

  <div class="static-control-bar">
    <div class="logo">Animeen</div>
    <div class="nav-links">
      <a href="Home.php">Home</a>
      <a href="login.php">Login</a>
      <a href="RankingPage.php">Top Anime</a>
      <a href="GenrePage.php">Genres</a>
      <a href="#">About</a>
    </div>
  </div>

  <main class="page">
    <h1 class="page-title">Top Anime — <?php echo $genreTitle; ?></h1>

    <div class="grid">
  <?php foreach ($animeList as $anime): ?>
    <article class="anime-card" data-id="<?php echo $anime["id"]; ?>">

      <a href="AnimeInfo.php?anime=<?php echo $anime["id"]; ?>" class="poster-link">
        <div class="poster">
          <img src="<?php echo htmlspecialchars($anime["img"]); ?>" alt="<?php echo htmlspecialchars($anime["title"]); ?>">
        </div>
      </a>

      <div class="meta">
        <h3 class="title"><?php echo htmlspecialchars($anime["title"]); ?></h3>
        <p class="sub"><?php echo htmlspecialchars($anime["studio"]); ?> • <?php echo htmlspecialchars($anime["year"]); ?></p>
      </div>

      <div class="actions">
        <button class="btn btn-watchlist" type="button">+ Watchlist</button>
        <button class="btn btn-like" type="button">👍</button>
        <button class="btn btn-dislike" type="button">👎</button>
      </div>

    </article>
  <?php endforeach; ?>
</div>


    <div class="back-row">
      <a class="back-btn" href="GenrePage.php">← Back to Genres</a>
    </div>
  </main>

  <script src="AnimeGrid.js"></script>
</body>
</html>
