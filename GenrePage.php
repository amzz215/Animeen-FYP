<?php
require_once __DIR__ . "/AnimeenDbConn.php";

$genres = [];

try {

$stat = $pdo->query("
SELECT DISTINCT TRIM(
    SUBSTRING_INDEX(SUBSTRING_INDEX(genres, ',', numbers.n), ',', -1)
) AS genre
FROM anime
JOIN (
    SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
    UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
) numbers
ON CHAR_LENGTH(genres) - CHAR_LENGTH(REPLACE(genres, ',', '')) >= numbers.n - 1
ORDER BY genre
");

$genres = $stat->fetchAll(PDO::FETCH_COLUMN);

} catch(PDOException $ex){
$genres = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Genres</title>
<link rel="stylesheet" href="GenrePage.css">
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
</div>
</div>

<main class="page">

<h1 class="page-title">All Genres</h1>

<div class="grid genre-grid">

<?php foreach($genres as $genre): 

$slug = strtolower(str_replace(" ", "-", $genre));
?>

<a class="genre-card"
href="TopGenreAnime.php?genre=<?php echo urlencode($slug); ?>">

<?php echo htmlspecialchars($genre); ?>

</a>

<?php endforeach; ?>

</div>

</main>

</body>
</html>