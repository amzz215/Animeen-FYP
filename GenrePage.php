<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Genres</title>

  <link rel="stylesheet" href="GenrePage.css">
</head>

<body>

  <!-- Background video -->
  <section>
    <video src="video/naruto.mp4" loop muted autoplay></video>
  </section>

  <!-- Navbar -->
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

  <!-- Page content -->
  <main class="page">
    <h1 class="page-title">All Genres</h1>

    <div class="grid genre-grid">
      <a class="genre-card" href="TopGenreAnime.php?genre=action">Action</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=adventure">Adventure</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=comedy">Comedy</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=drama">Drama</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=fantasy">Fantasy</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=horror">Horror</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=mecha">Mecha</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=music">Music</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=mystery">Mystery</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=romance">Romance</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=sci-fi">Sci-Fi</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=slice-of-life">Slice of Life</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=sports">Sports</a>
      <a class="genre-card" href="TopGenreAnime.php?genre=thriller">Thriller</a>
    </div>
  </main>

</body>
</html>

