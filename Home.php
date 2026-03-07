<?php
require_once "AnimeenDbConn.php";

/**
 * Pagination defaults (override per page)
 */
$limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 100;
if ($limit <= 0) $limit = 100;
if ($limit > 200) $limit = 200; // hard cap to protect performance

$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

$sql = "
  SELECT id, title, main_picture_url, mean, rank
  FROM anime
  WHERE mean IS NOT NULL
  ORDER BY mean DESC
  LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();

$animeList = $stmt->fetchAll();

/**
 * Total rows for pagination UI
 * 
 */

$countStmt = $pdo->query("SELECT COUNT(*) AS c FROM anime WHERE mean IS NOT NULL");
$totalRows = (int)$countStmt->fetch()["c"];
$totalPages = (int)ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <link rel="stylesheet" href="Home.css">
</head>

<body>

    <section>
        <video src="video/kame-house.mp4" loop muted autoplay></video>
    </section>

    <div class="static-control-bar">
        <div class="logo">Animeen</div>
            <div class="nav-links">
                <!-- <a href="#">Home</a> -->
                <a href="RankingPage.php">Top Anime</a>
                <a href="GenrePage.php">Genres</a>
                <a href="login.php">Login</a>
                <a href="#">About</a>
            </div>
    </div>

    <div class="search-container">
  <form class="search-bar" id="searchForm" method="get" action="Recommendations.php">
    <input
      class="search-input"
      type="text"
      name="title"
      placeholder="Search anime..."
      required
    >

    <button class="search-btn" type="submit">Search</button>
    <button class="filter-btn" type="button" id="openFilters">Filter</button>
  </form>
</div>

<div class="drawer-overlay" id="drawerOverlay"></div>

<aside class="filter-drawer" id="filterDrawer" aria-hidden="true">
  <div class="drawer-header">
    <h2>Filters</h2>
    <button class="drawer-close" type="button" id="closeFilters">✕</button>
  </div>

  <div class="drawer-content">

    <div class="filter-item">
      <span>Genre</span>
      <div class="genre-grid">
        <label class="genre-pill"><input type="checkbox" value="action"><span>Action</span></label>
        <label class="genre-pill"><input type="checkbox" value="adventure"><span>Adventure</span></label>
        <label class="genre-pill"><input type="checkbox" value="comedy"><span>Comedy</span></label>
        <label class="genre-pill"><input type="checkbox" value="drama"><span>Drama</span></label>
        <label class="genre-pill"><input type="checkbox" value="fantasy"><span>Fantasy</span></label>
        <label class="genre-pill"><input type="checkbox" value="romance"><span>Romance</span></label>
        <label class="genre-pill"><input type="checkbox" value="sci-fi"><span>Sci-Fi</span></label>
        <label class="genre-pill"><input type="checkbox" value="slice-of-life"><span>Slice of Life</span></label>
        <label class="genre-pill"><input type="checkbox" value="sports"><span>Sports</span></label>
        <label class="genre-pill"><input type="checkbox" value="thriller"><span>Thriller</span></label>
      </div>
    </div>

    <div class="filter-item">
      <span>Type</span>
      <div class="radio-row">
        <label class="radio-pill">
          <input type="radio" name="filterType" value="" checked>
          <span>Any</span>
        </label>
        <label class="radio-pill">
          <input type="radio" name="filterType" value="tv">
          <span>TV</span>
        </label>
        <label class="radio-pill">
          <input type="radio" name="filterType" value="movie">
          <span>Movie</span>
        </label>
      </div>
    </div>

    <div class="filter-item">
      <span>Year range</span>
      <div class="year-row">
        <select id="startYear"></select>
        <select id="endYear"></select>
      </div>
    </div>

    <div class="drawer-actions">
      <button class="clear-filters" type="button" id="clearFilters">Clear</button>
      <button class="apply-filters" type="button" id="applyFilters">Apply</button>
    </div>

  </div>
</aside>

    <!-- <footer class="footer">
        <p>&copy; Animeen 2026. All rights reserved.</p>
    </footer> -->
    <script src="Home.js"></script>
</body>
</html>